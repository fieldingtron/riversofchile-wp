#!/usr/bin/env bash
# env-sync.sh - Encrypt/decrypt .env with openssl

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"
ENV_FILE="$PROJECT_DIR/.env"
ENV_ENCRYPTED_FILE="$PROJECT_DIR/.env.encrypted"
ENV_BACKUPS_DIR="$PROJECT_DIR/.env.backups"
MAX_BACKUPS=5

usage() {
  cat <<'EOF'
Usage:
  ./scripts/env-sync.sh encrypt
  ./scripts/env-sync.sh decrypt
  ./scripts/env-sync.sh sync

Password source:
  - PASSWORD environment variable, or
  - secure interactive prompt

Examples:
  PASSWORD='my-secret' ./scripts/env-sync.sh encrypt
  PASSWORD='my-secret' ./scripts/env-sync.sh decrypt
EOF
}

need_openssl() {
  command -v openssl >/dev/null 2>&1 || {
    echo "✗ ERROR: openssl is required" >&2
    exit 1
  }
}

get_password() {
  if [[ "${PASSWORD+x}" == "x" ]]; then
    if [[ -z "$PASSWORD" ]]; then
      echo "✗ ERROR: PASSWORD is set but empty" >&2
      exit 1
    fi
    printf "%s" "$PASSWORD"
    return 0
  fi

  local pass1 pass2
  read -r -s -p "Enter env password: " pass1
  echo

  if [[ "${1:-}" == "confirm" ]]; then
    read -r -s -p "Confirm env password: " pass2
    echo
    if [[ "$pass1" != "$pass2" ]]; then
      echo "✗ ERROR: passwords do not match" >&2
      exit 1
    fi
  fi

  if [[ -z "$pass1" ]]; then
    echo "✗ ERROR: password cannot be empty" >&2
    exit 1
  fi

  printf "%s" "$pass1"
}

ensure_backup_dir() {
  mkdir -p "$ENV_BACKUPS_DIR"
}

cleanup_old_backups() {
  local count=0
  local old_file
  while IFS= read -r old_file; do
    count=$((count + 1))
    if (( count > MAX_BACKUPS )); then
      rm -f "$old_file"
    fi
  done < <(ls -1t "$ENV_BACKUPS_DIR"/env.*.enc 2>/dev/null || true)
}

backup_encrypted_file() {
  [[ -f "$ENV_ENCRYPTED_FILE" ]] || return 0
  ensure_backup_dir
  local ts
  ts="$(date +%Y%m%d%H%M%S)"
  cp "$ENV_ENCRYPTED_FILE" "$ENV_BACKUPS_DIR/env.${ts}.enc"
  cleanup_old_backups
}

encrypt_env() {
  [[ -f "$ENV_FILE" ]] || {
    echo "✗ ERROR: .env not found at $ENV_FILE" >&2
    exit 1
  }

  local password
  password="$(get_password confirm)"

  backup_encrypted_file
  openssl enc -aes-256-cbc -pbkdf2 -salt -a \
    -in "$ENV_FILE" -out "$ENV_ENCRYPTED_FILE" \
    -pass "pass:$password"

  echo "✓ Wrote .env.encrypted"
}

decrypt_env() {
  [[ -f "$ENV_ENCRYPTED_FILE" ]] || {
    echo "✗ ERROR: .env.encrypted not found at $ENV_ENCRYPTED_FILE" >&2
    exit 1
  }

  local password
  password="$(get_password)"

  openssl enc -d -aes-256-cbc -pbkdf2 -a \
    -in "$ENV_ENCRYPTED_FILE" -out "$ENV_FILE" \
    -pass "pass:$password"

  echo "✓ Wrote .env"
}

main() {
  need_openssl

  case "${1:-}" in
    encrypt)
      encrypt_env
      ;;
    decrypt)
      decrypt_env
      ;;
    sync)
      decrypt_env
      encrypt_env
      ;;
    *)
      usage
      ;;
  esac
}

main "${1:-}"
