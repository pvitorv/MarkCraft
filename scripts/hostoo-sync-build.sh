#!/usr/bin/env bash
# Rodar NA MÁQUINA LOCAL — envia public/build/ via scp
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

HOSTOO_SSH="${HOSTOO_SSH:-usuario@seu-host-ssh}"
HOSTOO_SSH_PORT="${HOSTOO_SSH_PORT:-}"
HOSTOO_REMOTE_BUILD="${HOSTOO_REMOTE_BUILD:-~/public_html/build}"

echo "==> npm run build"
npm run build

echo "==> scp → $HOSTOO_SSH:$HOSTOO_REMOTE_BUILD"
scp -P "$HOSTOO_SSH_PORT" -r public/build/. "$HOSTOO_SSH:$HOSTOO_REMOTE_BUILD/"

echo "OK"
