#!/usr/bin/env bash
# backup.sh — Export local WordPress DB to db/
#
# Usage:
#   ./scripts/backup.sh             # → db/backup-YYYYMMDD-HHMMSS.sql.gz
#   ./scripts/backup.sh --no-gzip   # → db/backup-YYYYMMDD-HHMMSS.sql (plain)

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"
DB_DIR="$PROJECT_DIR/db"
TIMESTAMP="$(date +%Y%m%d-%H%M%S)"

GZIP=true
[[ "${1:-}" == "--no-gzip" ]] && GZIP=false

mkdir -p "$DB_DIR"

if [[ "$GZIP" == true ]]; then
  OUTFILE="$DB_DIR/backup-${TIMESTAMP}.sql.gz"
  echo "▶ Exporting local DB to $OUTFILE ..."
  docker compose -f "$PROJECT_DIR/docker-compose.yml" exec -T web \
    wp db export - --path=/var/www/html --allow-root \
  | gzip > "$OUTFILE"
else
  OUTFILE="$DB_DIR/backup-${TIMESTAMP}.sql"
  echo "▶ Exporting local DB to $OUTFILE ..."
  docker compose -f "$PROJECT_DIR/docker-compose.yml" exec -T web \
    wp db export - --path=/var/www/html --allow-root \
  > "$OUTFILE"
fi

SIZE="$(du -sh "$OUTFILE" | cut -f1)"
echo "✓ Done: $OUTFILE ($SIZE)"
echo ""
echo "To restore:"
if [[ "$GZIP" == true ]]; then
  echo "  gunzip -c $OUTFILE | docker compose exec -T db mysql -u\${MYSQL_USER} -p\${MYSQL_PASSWORD} \${MYSQL_DATABASE}"
else
  echo "  docker compose exec -T db mysql -u\${MYSQL_USER} -p\${MYSQL_PASSWORD} \${MYSQL_DATABASE} < $OUTFILE"
fi
