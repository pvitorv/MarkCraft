#!/usr/bin/env bash
# Rodar NO SERVIDOR Hostoo (SSH)
# Uso: bash hostoo-git-pull.sh [branch]
set -euo pipefail

MARKCRAFT_DIR="${MARKCRAFT_DIR:-$HOME/markcraft}"
BRANCH="${1:-}"

if [[ -z "$BRANCH" ]]; then
  echo "Uso: bash scripts/hostoo-git-pull.sh <branch>"
  echo "Ex.: bash scripts/hostoo-git-pull.sh 014"
  exit 1
fi

cd "$MARKCRAFT_DIR"

if [[ ! -d .git ]]; then
  echo "ERRO: Git não inicializado."
  exit 1
fi

git fetch origin "$BRANCH"
git checkout "$BRANCH"
git pull origin "$BRANCH"

composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan view:clear
php artisan config:cache
php artisan route:cache

echo "OK — branch $BRANCH. Sync build/ se mudou JS/CSS."
