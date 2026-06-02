#!/usr/bin/env bash
# pull.sh — Sync live riversofchile.com to local Docker WordPress
#
# Usage:
#   ./scripts/pull.sh                 # DB + incremental uploads (daily use)
#   ./scripts/pull.sh --full          # DB + bulk uploads + themes (first run / full reset)
#   ./scripts/pull.sh --uploads-only  # Resume uploads without re-importing DB

set -euo pipefail

# ── Config ────────────────────────────────────────────────────────────────────
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"

# Load .env (handles unquoted values with spaces, skips comments and blanks)
if [[ -f "$PROJECT_DIR/.env" ]]; then
  while IFS='=' read -r key value; do
    [[ "$key" =~ ^[[:space:]]*# ]] && continue  # skip comments
    [[ -z "$key" ]] && continue                  # skip blank lines
    key="${key// /}"                              # trim spaces from key
    export "$key"="$value"
  done < "$PROJECT_DIR/.env"
else
  echo "✗ .env not found at $PROJECT_DIR/.env" >&2
  exit 1
fi

SSH_HOST="${ROC_SSH_HOST:-roc}"
LIVE_WP_PATH="${ROC_LIVE_WP_PATH:-/home/dh_jwwaqd/riversofchile.com}"
LIVE_URL="${ROC_LIVE_URL:-http://www.riversofchile.com}"
LOCAL_URL="http://localhost:${WP_HTTP_PORT:-8086}"
LOCAL_WP_PATH="/var/www/html"

FULL_SYNC=false
UPLOADS_ONLY=false
SSH_OPTS=(-o BatchMode=yes -o ConnectTimeout=10 -o ServerAliveInterval=30 -o ServerAliveCountMax=10 -o StrictHostKeyChecking=accept-new)
RSYNC_RSH="ssh -o BatchMode=yes -o ConnectTimeout=10 -o ServerAliveInterval=30 -o ServerAliveCountMax=10 -o StrictHostKeyChecking=accept-new"
RSYNC_EXCLUDE=(
  --exclude='.DS_Store'
)

RSYNC_OPTS=(
  -azh
  --partial
  --inplace
  --progress
  --stats
  "${RSYNC_EXCLUDE[@]}"
)

# Detect modern rsync (3.x+) for nicer progress bar
if rsync --version 2>/dev/null | head -1 | grep -qE 'version (3|[4-9])\.'; then
  RSYNC_OPTS=(
    -azh
    --partial
    --inplace
    --info=progress2,stats2
    --human-readable
    "${RSYNC_EXCLUDE[@]}"
  )
fi
BAR_WIDTH=40

for arg in "$@"; do
  case "$arg" in
    --full)
      FULL_SYNC=true
      ;;
    --uploads-only)
      UPLOADS_ONLY=true
      ;;
    *)
      echo "✗ Unknown option: $arg" >&2
      echo "Usage: ./scripts/pull.sh [--full] [--uploads-only]" >&2
      exit 1
      ;;
  esac
done

if [[ "$FULL_SYNC" == true && "$UPLOADS_ONLY" == true ]]; then
  echo "✗ --full and --uploads-only cannot be used together" >&2
  exit 1
fi

# ── Auto-detect first run ─────────────────────────────────────────────────────
if [[ "$FULL_SYNC" == false && "$UPLOADS_ONLY" == false ]]; then
  UPLOADS_DIR="$PROJECT_DIR/src/wp-content/uploads"
  THEME_DIR="$PROJECT_DIR/src/wp-content/themes/the-box"
  if [[ ! -d "$UPLOADS_DIR" || -z "$(ls -A "$UPLOADS_DIR" 2>/dev/null)" || ! -d "$THEME_DIR" ]]; then
    echo "⚠ Empty uploads or missing theme detected — promoting to --full sync."
    FULL_SYNC=true
  fi
fi

TOTAL_STEPS=5
if [[ "$UPLOADS_ONLY" == true ]]; then
  TOTAL_STEPS=2
elif [[ "$FULL_SYNC" == true ]]; then
  TOTAL_STEPS=6
fi
CURRENT_STEP=0

# ── Helpers ───────────────────────────────────────────────────────────────────
progress_prefix() {
  local filled=$(( CURRENT_STEP * BAR_WIDTH / TOTAL_STEPS ))
  local empty=$(( BAR_WIDTH - filled ))
  local bar
  bar="$(printf '%*s' "$filled" '' | tr ' ' '#')"
  bar+="$(printf '%*s' "$empty" '' | tr ' ' '-')"
  printf "[%s] (%d/%d)" "$bar" "$CURRENT_STEP" "$TOTAL_STEPS"
}

_STEP_START=0
_PULL_START=$SECONDS

# Summary tracking
declare -a _STEP_LABELS=()
declare -a _STEP_DURATIONS=()
_DB_SIZE_MB=0
_UPLOADS_FILES=0
_UPLOADS_MB=0
_UPLOADS_STATUS="skipped"
_PLUGINS_DISABLED=0

step() {
  CURRENT_STEP=$((CURRENT_STEP + 1))
  _STEP_START=$SECONDS
  _CURRENT_STEP_LABEL="$*"
  echo ""
  echo "$(progress_prefix) ▶ $*"
}
ok() {
  local elapsed=$(( SECONDS - _STEP_START ))
  echo "  ✓ $* (${elapsed}s)"
  _STEP_LABELS+=("$_CURRENT_STEP_LABEL")
  _STEP_DURATIONS+=("$elapsed")
}
die()  { echo "✗ ERROR: $*" >&2; exit 1; }

ensure_wp_cli() {
  if ! docker compose -f "$PROJECT_DIR/docker-compose.yml" exec -T web command -v wp >/dev/null 2>&1; then
    echo "  installing wp-cli in container..."
    docker compose -f "$PROJECT_DIR/docker-compose.yml" exec -T web sh -c '
      curl -sS -o /usr/local/bin/wp https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
      chmod +x /usr/local/bin/wp
    ' >/dev/null
  fi
}

tar_pull() {
  local label="$1"
  local remote_parent="$2"
  local remote_dir="$3"
  local local_parent="$4"

  step "$label"
  mkdir -p "$local_parent/$remote_dir"
  echo "  streaming via ssh tar | gzip -1 ..."
  ssh "${SSH_OPTS[@]}" "$SSH_HOST" \
    "tar -C '$remote_parent' -cf - '$remote_dir' | gzip -1" \
    | gunzip | tar -C "$local_parent" -xf -
}

upload_progress() {
  local label="$1"
  local source_path="$2"
  local destination_path="$3"
  local total_bytes
  local total_mb

  step "$label"
  echo "  scanning remote file list (this may take a moment) ..."

  local dry_run_output
  dry_run_output="$(
    rsync -an -i --out-format='%i %l %n' \
      -e "$RSYNC_RSH" \
      "$SSH_HOST:$source_path/" \
      "$destination_path/" 2>/dev/null
  )"

  local file_count total_bytes
  read -r file_count total_bytes <<< "$(
    echo "$dry_run_output" \
    | awk '/^[>c]f/ {count++; bytes += $2} END {printf "%d %d", count+0, bytes+0}'
  )"

  if [[ ! "$file_count" =~ ^[0-9]+$ ]]; then file_count=0; fi
  if [[ ! "$total_bytes" =~ ^[0-9]+$ ]]; then total_bytes=0; fi

  total_mb=$(( total_bytes / 1048576 ))

  if [[ "$file_count" -eq 0 ]]; then
    echo "  already up to date — nothing to transfer"
    _UPLOADS_STATUS="up to date"
    return 0
  fi

  _UPLOADS_FILES=$file_count
  _UPLOADS_MB=$total_mb
  _UPLOADS_STATUS="synced"
  echo "  to transfer: ${file_count} files, ${total_mb} MB"
  echo ""

  if command -v script >/dev/null 2>&1; then
    script -q /dev/null \
      rsync "${RSYNC_OPTS[@]}" \
        -e "$RSYNC_RSH" \
        "$SSH_HOST:$source_path/" \
        "$destination_path/"
  else
    rsync "${RSYNC_OPTS[@]}" \
      -e "$RSYNC_RSH" \
      "$SSH_HOST:$source_path/" \
      "$destination_path/"
  fi
}

# ── 1. DB pull ────────────────────────────────────────────────────────────────
if [[ "$UPLOADS_ONLY" == false ]]; then
step "Pulling database from $LIVE_URL via $SSH_HOST ..."

ensure_wp_cli

local_backup_dir="$PROJECT_DIR/db/backups"
mkdir -p "$local_backup_dir"
local_backup_file="$local_backup_dir/local-before-pull-$(date +%Y%m%d%H%M%S).sql.gz"
echo "  backing up local database to $local_backup_file ..."
if docker compose -f "$PROJECT_DIR/docker-compose.yml" exec -T db \
  mysqldump -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE" 2>/dev/null | gzip -1 > "$local_backup_file"; then
  echo "  ✓ Local database backup saved."
else
  echo "  ⚠ Local database backup failed (perhaps DB is empty or container is stopped)."
fi

# Stream dump over SSH directly into mysql
if ! ssh "${SSH_OPTS[@]}" "$SSH_HOST" \
  "DB_NAME=\$(wp config get DB_NAME --path='$LIVE_WP_PATH') && \
   DB_USER=\$(wp config get DB_USER --path='$LIVE_WP_PATH') && \
   DB_PASSWORD=\$(wp config get DB_PASSWORD --path='$LIVE_WP_PATH') && \
   DB_HOST=\$(wp config get DB_HOST --path='$LIVE_WP_PATH') && \
   mysqldump \
     -h \"\$DB_HOST\" \
     --default-character-set=utf8mb4 \
     --single-transaction \
     --quick \
     --no-tablespaces \
     --skip-lock-tables \
     --skip-add-locks \
     --skip-comments \
     --hex-blob \
     -u \"\$DB_USER\" \
     -p\"\$DB_PASSWORD\" \
     \"\$DB_NAME\" | gzip -1" \
  | gunzip \
  | docker compose -f "$PROJECT_DIR/docker-compose.yml" exec -T db \
      mysql --default-character-set=utf8mb4 -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"; then
  die "Live DB export/import failed. Verify your SSH connection with 'ssh $SSH_HOST'."
fi

# Purge transients
echo "  purging transients..."
docker compose -f "$PROJECT_DIR/docker-compose.yml" exec -T db \
  mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE" \
  -e "DELETE FROM wp_roc_options WHERE option_name LIKE '\_transient\_%' OR option_name LIKE '\_site\_transient\_%';" 2>/dev/null || true

_DB_SIZE_MB=$(docker compose -f "$PROJECT_DIR/docker-compose.yml" exec -T db \
  mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE" \
  -sNe "SELECT ROUND(SUM(data_length+index_length)/1024/1024,1) FROM information_schema.tables WHERE table_schema=DATABASE();" 2>/dev/null || echo "?")

ok "Database imported"

# ── 2. Fix URLs ───────────────────────────────────────────────────────────────
step "Replacing URLs ($LIVE_URL → $LOCAL_URL) ..."

LIVE_HOST="${LIVE_URL#http://}"
LIVE_HOST="${LIVE_HOST#https://}"
LIVE_HOST="${LIVE_HOST%/}"
LIVE_HOST_NOWWW="${LIVE_HOST#www.}"
LIVE_HOST_WWW="www.${LIVE_HOST_NOWWW}"
LOCAL_HOST="${LOCAL_URL#http://}"
LOCAL_HOST="${LOCAL_HOST#https://}"
LOCAL_HOST="${LOCAL_HOST%/}"

URL_REPLACEMENTS=(
  "https://$LIVE_HOST|$LOCAL_URL"
  "http://$LIVE_HOST|$LOCAL_URL"
  "//$LIVE_HOST|//$LOCAL_HOST"
  "https:\\/\\/$LIVE_HOST|http:\\/\\/$LOCAL_HOST"
  "http:\\/\\/$LIVE_HOST|http:\\/\\/$LOCAL_HOST"
)

if [[ "$LIVE_HOST_NOWWW" != "$LIVE_HOST" ]]; then
  URL_REPLACEMENTS+=(
    "https://$LIVE_HOST_NOWWW|$LOCAL_URL"
    "http://$LIVE_HOST_NOWWW|$LOCAL_URL"
    "//$LIVE_HOST_NOWWW|//$LOCAL_HOST"
    "https:\\/\\/$LIVE_HOST_NOWWW|http:\\/\\/$LOCAL_HOST"
    "http:\\/\\/$LIVE_HOST_NOWWW|http:\\/\\/$LOCAL_HOST"
  )
else
  URL_REPLACEMENTS+=(
    "https://$LIVE_HOST_WWW|$LOCAL_URL"
    "http://$LIVE_HOST_WWW|$LOCAL_URL"
    "//$LIVE_HOST_WWW|//$LOCAL_HOST"
    "https:\\/\\/$LIVE_HOST_WWW|http:\\/\\/$LOCAL_HOST"
    "http:\\/\\/$LIVE_HOST_WWW|http:\\/\\/$LOCAL_HOST"
  )
fi

SR_SCRIPT=""
for pair in "${URL_REPLACEMENTS[@]}"; do
  from="${pair%%|*}"
  to="${pair##*|}"
  SR_SCRIPT+="wp search-replace $(printf '%q' "$from") $(printf '%q' "$to")"
  SR_SCRIPT+=" --path=$LOCAL_WP_PATH --all-tables --allow-root --report-changed-only 2>&1 | tail -1"$'\n'
done
docker compose -f "$PROJECT_DIR/docker-compose.yml" exec -T web bash -c "$SR_SCRIPT"

# Force canonical local URLs so redirects never stick to live host variants.
docker compose -f "$PROJECT_DIR/docker-compose.yml" exec -T web \
  wp option update home "$LOCAL_URL" --path="$LOCAL_WP_PATH" --allow-root >/dev/null
docker compose -f "$PROJECT_DIR/docker-compose.yml" exec -T web \
  wp option update siteurl "$LOCAL_URL" --path="$LOCAL_WP_PATH" --allow-root >/dev/null

ok "URLs updated"

# ── 3. Disable local-incompatible plugins ─────────────────────────────────────
step "Disabling local-incompatible plugins (cache, security, image-optim) ..."

LOCAL_DISABLE_PLUGINS=(
  wp-super-cache
  ewww-image-optimizer
  google-site-kit
  updraftplus
  wordfence
  docket-cache
)

docker compose -f "$PROJECT_DIR/docker-compose.yml" exec -T web \
  wp plugin deactivate "${LOCAL_DISABLE_PLUGINS[@]}" \
    --path="$LOCAL_WP_PATH" --allow-root 2>/dev/null || true

_PLUGINS_DISABLED=${#LOCAL_DISABLE_PLUGINS[@]}
ok "Local-incompatible plugins disabled"

# Update plugins.txt with current live versions
echo "  updating plugins.txt from live..."
echo "# WordPress plugins — riversofchile.com" > "$PROJECT_DIR/plugins.txt"
echo "# Generated: $(date +%Y-%m-%d) | WP-CLI: wp plugin list --fields=name,version,status" >> "$PROJECT_DIR/plugins.txt"
echo "#" >> "$PROJECT_DIR/plugins.txt"
echo "# slug,version,status" >> "$PROJECT_DIR/plugins.txt"
echo "" >> "$PROJECT_DIR/plugins.txt"
ssh "${SSH_OPTS[@]}" "$SSH_HOST" "wp plugin list --fields=name,version,status --format=csv --path='$LIVE_WP_PATH'" | tail -n +2 >> "$PROJECT_DIR/plugins.txt"

fi  # end UPLOADS_ONLY==false

# ── 4. Uploads ───────────────────────────────────────────────────────────────
if [[ "$FULL_SYNC" == true ]]; then
  tar_pull "Syncing uploads (tar+gzip — first run bulk) ..." \
    "$LIVE_WP_PATH/wp-content" "uploads" \
    "$PROJECT_DIR/src/wp-content"
else
  upload_progress "Syncing uploads (incremental rsync) ..." \
    "$LIVE_WP_PATH/wp-content/uploads" \
    "$PROJECT_DIR/src/wp-content/uploads"
fi

ok "Uploads synced"

# ── 4b. Themes (First setup / Full sync) ──────────────────────────────────────
if [[ "$FULL_SYNC" == true && "$UPLOADS_ONLY" == false ]]; then
  tar_pull "Syncing themes (tar+gzip — active the-box theme) ..." \
    "$LIVE_WP_PATH/wp-content" "themes" \
    "$PROJECT_DIR/src/wp-content"
  ok "Themes synced"
fi

# ── 5. Cache flush ────────────────────────────────────────────────────────────
step "Flushing cache..."

docker compose -f "$PROJECT_DIR/docker-compose.yml" exec -T web \
  wp cache flush --path="$LOCAL_WP_PATH" --allow-root

ok "Cache flushed"

# ── Summary ───────────────────────────────────────────────────────────────────
TOTAL_ELAPSED=$(( SECONDS - _PULL_START ))
MINS=$(( TOTAL_ELAPSED / 60 ))
SECS=$(( TOTAL_ELAPSED % 60 ))
[[ $MINS -gt 0 ]] && TIME_STR="${MINS}m ${SECS}s" || TIME_STR="${SECS}s"

MODE="incremental"
[[ "$FULL_SYNC"    == true ]] && MODE="full"
[[ "$UPLOADS_ONLY" == true ]] && MODE="uploads-only"

HR="━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

echo ""
echo "$HR"
echo "  ✓ Pull complete  (${MODE})  •  ${TIME_STR}  (${TOTAL_ELAPSED}s)"
echo "$HR"
echo ""

if [[ "$UPLOADS_ONLY" == false ]]; then
  printf "  %-30s %s\n" "Database:" "${_DB_SIZE_MB} MB"
  if [[ "$_PLUGINS_DISABLED" -gt 0 ]]; then
    printf "  %-30s %s\n" "Local-only plugins:" "${_PLUGINS_DISABLED} plugins deactivated"
  fi
fi

if [[ "$_UPLOADS_STATUS" == "synced" ]]; then
  printf "  %-30s %s\n" "Uploads:" "${_UPLOADS_FILES} files, ${_UPLOADS_MB} MB transferred"
else
  printf "  %-30s %s\n" "Uploads:" "$_UPLOADS_STATUS"
fi

if [[ "${#_STEP_LABELS[@]}" -gt 0 ]]; then
  echo ""
  echo "  Step breakdown:"
  for i in "${!_STEP_LABELS[@]}"; do
    printf "    %-40s %s\n" "${_STEP_LABELS[$i]}" "${_STEP_DURATIONS[$i]}s"
  done
fi

echo ""
printf "  %-8s %s\n" "Site:"  "$LOCAL_URL"
printf "  %-8s %s\n" "Admin:" "${LOCAL_URL}/wp-admin  (use live credentials)"
echo ""
