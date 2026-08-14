#!/usr/bin/env bash
# Rodar NA MÁQUINA LOCAL — envia public/build/ via scp
# Credenciais: scripts/hostoo.env (não versionado). Ver scripts/hostoo.env.example
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

if [[ -f "$ROOT/scripts/hostoo.env" ]]; then
  # shellcheck disable=SC1091
  source "$ROOT/scripts/hostoo.env"
fi

HOSTOO_SSH="${HOSTOO_SSH:-}"
HOSTOO_SSH_PORT="${HOSTOO_SSH_PORT:-}"
HOSTOO_REMOTE_BUILD="${HOSTOO_REMOTE_BUILD:-~/public_html/build}"

if [[ -z "$HOSTOO_SSH" || -z "$HOSTOO_SSH_PORT" ]]; then
  echo "ERRO: defina HOSTOO_SSH e HOSTOO_SSH_PORT em scripts/hostoo.env (veja scripts/hostoo.env.example)."
  exit 1
fi

echo "==> npm run build"
npm run build

echo "==> scp build + images → $HOSTOO_SSH"
scp -P "$HOSTOO_SSH_PORT" -r public/build/. "$HOSTOO_SSH:${HOSTOO_REMOTE_BUILD:-public_html/build}/"
scp -P "$HOSTOO_SSH_PORT" -r public/images/. "$HOSTOO_SSH:${HOSTOO_REMOTE_IMAGES:-public_html/images}/"

echo "OK"
