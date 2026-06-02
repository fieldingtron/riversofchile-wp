#!/usr/bin/env bash
# post-install.sh - Initialize local env file after clone.

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"

if [[ -f "$PROJECT_DIR/.env" ]]; then
  echo "✓ .env already exists"
  exit 0
fi

if [[ -f "$PROJECT_DIR/.env.encrypted" ]]; then
  "$SCRIPT_DIR/env-sync.sh" decrypt
  echo "✓ .env created from .env.encrypted"
  exit 0
fi

if [[ -f "$PROJECT_DIR/.env.example" ]]; then
  cp "$PROJECT_DIR/.env.example" "$PROJECT_DIR/.env"
  echo "✓ .env created from .env.example"
  exit 0
fi

echo "✗ ERROR: missing .env.encrypted and .env.example" >&2
exit 1
