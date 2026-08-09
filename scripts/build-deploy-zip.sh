#!/usr/bin/env bash
# Gera markcraft-deploy-YYYYMMDD-hostoo.zip na raiz do projeto.
# Pré-requisitos: composer install --no-dev, npm run build, testes OK.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

DATE="$(date +%Y%m%d)"
ZIP_NAME="markcraft-deploy-${DATE}-hostoo.zip"
STAGING="$ROOT/deploy-staging/markcraft"
EXCLUDE_FILE="$ROOT/scripts/deploy-exclude.txt"

echo "==> MarkCraft deploy pack → $ZIP_NAME"

if [[ ! -d vendor ]]; then
  echo "ERRO: vendor/ ausente. Rode: composer install --no-dev --optimize-autoloader"
  exit 1
fi

if [[ ! -f public/build/manifest.json ]]; then
  echo "ERRO: public/build/ ausente. Rode: npm ci && npm run build"
  exit 1
fi

rm -rf "$ROOT/deploy-staging"
mkdir -p "$STAGING"

if command -v rsync >/dev/null 2>&1; then
  rsync -a \
    --exclude-from="$EXCLUDE_FILE" \
    --exclude='deploy-staging' \
    "$ROOT/" "$STAGING/"
else
  echo "rsync não encontrado; usando tar..."
  tar -cf - \
    --exclude-from="$EXCLUDE_FILE" \
    --exclude='deploy-staging' \
    -C "$ROOT" . | tar -xf - -C "$STAGING"
fi

# Limpar runtime storage (manter .gitignore)
find "$STAGING/storage/logs" -mindepth 1 ! -name '.gitignore' -delete 2>/dev/null || true
find "$STAGING/storage/framework/cache/data" -mindepth 1 ! -name '.gitignore' -delete 2>/dev/null || true
find "$STAGING/storage/framework/sessions" -mindepth 1 ! -name '.gitignore' -delete 2>/dev/null || true
find "$STAGING/storage/framework/views" -mindepth 1 ! -name '.gitignore' -delete 2>/dev/null || true
find "$STAGING/storage/app/public" -mindepth 1 ! -name '.gitignore' -delete 2>/dev/null || true
find "$STAGING/bootstrap/cache" -name '*.php' ! -name '.gitignore' -delete 2>/dev/null || true

# Incluir guia de deploy dentro do pacote
mkdir -p "$STAGING/docs/deploy"
cp "$ROOT/docs/deploy/hostoo-markcraft.md" "$STAGING/docs/deploy/" 2>/dev/null || true
cp "$ROOT/.env.hostoo.example" "$STAGING/.env.hostoo.example"

rm -f "$ROOT/$ZIP_NAME"

php "$ROOT/scripts/make-deploy-zip.php"

rm -rf "$ROOT/deploy-staging"

SIZE="$(du -h "$ROOT/$ZIP_NAME" 2>/dev/null | cut -f1 || echo '?')"
echo "==> Pronto: $ROOT/$ZIP_NAME ($SIZE)"
echo "    Subdomínio: https://markcraft.criasysweb.com.br"
echo "    Document root Hostoo: .../markcraft/public"
