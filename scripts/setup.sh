#!/usr/bin/env bash
# setup.sh — First-run setup for new machines or disaster recovery
#
# Pulls the live database and uploads from riversofchile.com by default.
# Run this once after "docker compose up -d" on a fresh clone.
#
# Usage:
#   ./scripts/setup.sh              # Pull live DB + incrementally sync files; auto-full on first run
#   ./scripts/setup.sh --full       # Force first-run bulk file sync via pull.sh
#   ./scripts/setup.sh --snapshot   # Use db/snapshot.sql.gz instead (offline / no SSH)

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"

# ── Auto-create .env from .env.example if it doesn't exist ───────────────────
if [[ ! -f "$PROJECT_DIR/.env" ]]; then
  if [[ -f "$PROJECT_DIR/.env.encrypted" ]]; then
    "$SCRIPT_DIR/env-sync.sh" decrypt
    echo "▶ Created .env from .env.encrypted"
  elif [[ -f "$PROJECT_DIR/.env.example" ]]; then
    cp "$PROJECT_DIR/.env.example" "$PROJECT_DIR/.env"
    echo "▶ Created .env from .env.example"
  else
    echo "✗ ERROR: missing .env, .env.encrypted, and .env.example" >&2
    exit 1
  fi
fi
# Load .env so docker compose picks up the right values
set -a; source "$PROJECT_DIR/.env"; set +a

WP_PATH=/var/www/html
LOCAL_URL=http://localhost:${WP_HTTP_PORT:-8086}
LIVE_URL="${ROC_LIVE_URL:-http://www.riversofchile.com}"
SNAPSHOT="$PROJECT_DIR/db/snapshot.sql.gz"
PLUGINS_FILE="$PROJECT_DIR/plugins.txt"

USE_LIVE=true
PULL_ARGS=()

usage() {
  cat <<EOF
Usage:
  ./scripts/setup.sh              Pull live DB + incrementally sync files; auto-full on first run
  ./scripts/setup.sh --full       Force first-run bulk file sync via pull.sh
  ./scripts/setup.sh --snapshot   Use db/snapshot.sql.gz instead of live
EOF
}

for arg in "$@"; do
  case "$arg" in
    --full)
      PULL_ARGS+=(--full)
      ;;
    --snapshot)
      USE_LIVE=false
      ;;
    -h|--help)
      usage
      exit 0
      ;;
    *)
      usage >&2
      echo "✗ ERROR: Unknown option: $arg" >&2
      exit 1
      ;;
  esac
done

if [[ "$USE_LIVE" == false && "${#PULL_ARGS[@]}" -gt 0 ]]; then
  echo "✗ ERROR: --snapshot cannot be combined with --full" >&2
  exit 1
fi

# ── Helpers ───────────────────────────────────────────────────────────────────
info()    { echo "▶ $*"; }
success() { echo "  ✓ $*"; }
warn()    { echo "  ⚠ $*"; }
die()     { echo "✗ ERROR: $*" >&2; exit 1; }

# Timing variables
_TIMER_START=0
t_docker_up=0; t_wp_ready=0; t_pull_sh=0; t_plugins_txt=0; t_activate_flush=0
timer_start() { _TIMER_START=$SECONDS; }
timer_end()   { eval "t_$1=$(( SECONDS - _TIMER_START ))"; }

wp() {
  docker compose -f "$PROJECT_DIR/docker-compose.yml" exec -T web \
    wp "$@" --path="$WP_PATH" --allow-root 2>/dev/null
}

ensure_wp_cli() {
  if ! docker compose -f "$PROJECT_DIR/docker-compose.yml" exec -T web command -v wp >/dev/null 2>&1; then
    echo "  installing wp-cli in container..."
    docker compose -f "$PROJECT_DIR/docker-compose.yml" exec -T web sh -c '
      curl -sS -o /usr/local/bin/wp https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
      chmod +x /usr/local/bin/wp
    ' >/dev/null
  fi
}

# ── 1. Prerequisites ──────────────────────────────────────────────────────────
timer_start
info "Checking prerequisites..."
command -v docker >/dev/null 2>&1  || die "Docker not found. Install Docker Desktop first."
command -v docker-compose >/dev/null 2>&1 || command -v docker >/dev/null 2>&1 || die "docker compose not found."

# Ensure services are running
RUNNING=$(docker compose -f "$PROJECT_DIR/docker-compose.yml" ps --services --filter status=running 2>/dev/null | tr '\n' ' ')
if [[ "$RUNNING" != *"web"* || "$RUNNING" != *"db"* ]]; then
  info "Starting Docker services..."
  docker compose -f "$PROJECT_DIR/docker-compose.yml" up -d --build >/dev/null
  RUNNING=$(docker compose -f "$PROJECT_DIR/docker-compose.yml" ps --services --filter status=running 2>/dev/null | tr '\n' ' ')
fi
if [[ "$RUNNING" != *"web"* || "$RUNNING" != *"db"* ]]; then
  die "Could not start required containers. Check: docker compose logs"
fi
success "Docker services running"
timer_end "docker_up"

ensure_wp_cli

# ── 2. Wait for WordPress files + DB connection ──────────────────────────────
timer_start
info "Waiting for WordPress files + DB connection..."
TRIES=0
until wp core version >/dev/null 2>&1 && wp db check >/dev/null 2>&1; do
  TRIES=$((TRIES + 1))
  [[ $TRIES -gt 30 ]] && die "WordPress not ready after 60s. Check: docker compose logs web"
  sleep 2
done
success "WordPress is ready"
timer_end "wp_ready"

# ── 3. Import database FIRST (populates DB so plugin activation works later) ──
timer_start
if [[ "$USE_LIVE" == true ]]; then
  SSH_HOST_ALIAS="${ROC_SSH_HOST:-roc}"
  info "Checking live SSH access (ssh $SSH_HOST_ALIAS)..."
  command -v ssh >/dev/null 2>&1 || die "ssh client not found. Install OpenSSH first."
  if ! ssh -o BatchMode=yes -o ConnectTimeout=8 "$SSH_HOST_ALIAS" "exit 0" >/dev/null 2>&1; then
    die "ssh $SSH_HOST_ALIAS is not configured or not reachable. Configure the alias in ~/.ssh/config, verify 'ssh $SSH_HOST_ALIAS' works, then re-run: ./scripts/setup.sh"
  fi
  success "ssh $SSH_HOST_ALIAS is working"

  info "Pulling database and files from live server..."
  if [[ "${#PULL_ARGS[@]}" -gt 0 ]]; then
    "$SCRIPT_DIR/pull.sh" "${PULL_ARGS[@]}"
  else
    "$SCRIPT_DIR/pull.sh"
  fi
  success "Live DB imported via pull.sh"
  timer_end "pull_sh"
else
  info "Importing snapshot from $SNAPSHOT..."
  [[ -f "$SNAPSHOT" ]] || die "Snapshot not found at $SNAPSHOT. Run without --snapshot to pull from the live server."

  # Reset the database, then import the snapshot
  wp db reset --yes >/dev/null 2>&1 || true
  gunzip -c "$SNAPSHOT" | docker compose -f "$PROJECT_DIR/docker-compose.yml" exec -T web \
    wp db import - --path="$WP_PATH" --allow-root >/dev/null 2>&1
  success "Snapshot imported"

  # Deactivate plugins so WordPress CLI boots without missing plugin errors
  info "Clearing active_plugins for a clean boot..."
  docker compose -f "$PROJECT_DIR/docker-compose.yml" exec -T web \
    wp db query "UPDATE wp_roc_options SET option_value='a:0:{}' WHERE option_name='active_plugins'" \
    --path="$WP_PATH" --allow-root </dev/null >/dev/null 2>&1
  success "Plugins deactivated for safe boot"

  # ── 4. Rewrite URLs ──────────────────────────────────────────────────────────
  info "Rewriting URLs: $LIVE_URL → $LOCAL_URL ..."
  wp search-replace "$LIVE_URL" "$LOCAL_URL" --all-tables --precise --skip-columns=guid >/dev/null 2>&1
  wp option update home "$LOCAL_URL" >/dev/null 2>&1
  wp option update siteurl "$LOCAL_URL" >/dev/null 2>&1
  success "URLs updated"
  timer_end "pull_sh"
fi

# ── 5. Install free plugins from wordpress.org ────────────────────────────────
timer_start
PLUGINS_DIR="$PROJECT_DIR/src/wp-content/plugins"
FAILED=()

# Determine if plugins are already present
INSTALLED_COUNT=$(find "$PLUGINS_DIR" -mindepth 1 -maxdepth 1 -type d 2>/dev/null \
  | grep -cEv '/(hello|akismet)$' || true)

if [[ "$INSTALLED_COUNT" -gt 6 ]]; then
  info "Skipping plugins.txt installation — plugins already present."
  timer_end "plugins_txt"
else
  info "Installing plugins from plugins.txt..."
  SKIP_PLUGINS=(
    hello
  )

  INSTALLED=0
  SKIPPED=0

  while IFS=',' read -r slug version status || [[ -n "$slug" ]]; do
    [[ "$slug" =~ ^[[:space:]]*# ]] && continue
    [[ -z "${slug// }" ]] && continue
    slug="${slug// /}"

    SKIP=false
    for s in "${SKIP_PLUGINS[@]}"; do
      [[ "$slug" == "$s" ]] && SKIP=true && break
    done
    if [[ "$SKIP" == true ]]; then
      SKIPPED=$((SKIPPED + 1))
      continue
    fi

    if docker compose -f "$PROJECT_DIR/docker-compose.yml" exec -T web \
        wp plugin is-installed "$slug" --path="$WP_PATH" --allow-root </dev/null >/dev/null 2>&1; then
      SKIPPED=$((SKIPPED + 1))
      continue
    fi

    if docker compose -f "$PROJECT_DIR/docker-compose.yml" exec -T web \
        wp plugin install "$slug" --version="$version" --path="$WP_PATH" --allow-root </dev/null >/dev/null 2>&1; then
      INSTALLED=$((INSTALLED + 1))
    elif docker compose -f "$PROJECT_DIR/docker-compose.yml" exec -T web \
        wp plugin install "$slug" --path="$WP_PATH" --allow-root </dev/null >/dev/null 2>&1; then
      INSTALLED=$((INSTALLED + 1))
    else
      FAILED+=("$slug")
    fi
  done < "$PLUGINS_FILE"

  success "Plugins: $INSTALLED installed, $SKIPPED skipped"
  timer_end "plugins_txt"
  if [[ ${#FAILED[@]} -gt 0 ]]; then
    warn "Could not install: ${FAILED[*]}"
  fi
fi

# ── 6. Activate plugins + flush ───────────────────────────────────────────────
timer_start
info "Activating plugins and flushing caches..."
wp plugin activate --all >/dev/null 2>&1 || true
wp rewrite flush --hard >/dev/null 2>&1 || true
wp cache flush >/dev/null 2>&1 || true
success "Done"
timer_end "activate_flush"

# ── Summary ───────────────────────────────────────────────────────────────────
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
printf "  %-30s %4ds\n" "docker up + WP ready"  "$(( t_docker_up + t_wp_ready ))"
printf "  %-30s %4ds\n" "pull.sh (DB + files)"  "$t_pull_sh"
printf "  %-30s %4ds\n" "plugins.txt install"   "$t_plugins_txt"
printf "  %-30s %4ds\n" "activate + flush"      "$t_activate_flush"
printf "  %-30s %4ds  (%dm %02ds)\n" "TOTAL" \
  "$SECONDS" $(( SECONDS / 60 )) $(( SECONDS % 60 ))
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  Site ready at:  $LOCAL_URL"
echo "  Admin:          $LOCAL_URL/wp-admin"
echo ""
echo "  Credentials are whatever was in the snapshot DB."
echo "  If you don't know the password, reset it:"
echo "    docker compose exec web wp user update admin"
echo "      --user_pass=newpassword --path=$WP_PATH --allow-root"
echo ""
if [[ ${#FAILED[@]} -gt 0 ]]; then
  echo "  ⚠ Manual plugin installs needed: ${FAILED[*]}"
  echo ""
fi
echo "  To sync uploads from live:  ./scripts/pull.sh"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
