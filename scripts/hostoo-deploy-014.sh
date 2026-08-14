#!/usr/bin/env bash
# Deploy da branch 014 na Hostoo. Rodar no Git Bash na pasta do projeto:
#   bash scripts/hostoo-deploy-014.sh
# Se o git já estiver na 014 e só o composer falhou:
#   HOSTOO_SKIP_GIT=1 bash scripts/hostoo-deploy-014.sh
#   HOSTOO_SCP_ONLY=1 bash scripts/hostoo-deploy-014.sh
# Vai pedir a senha SSH (código + build, ou só build no SCP_ONLY). Não altera MAIL_*.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

if [[ -f "$ROOT/scripts/hostoo.env" ]]; then
  # shellcheck disable=SC1091
  source "$ROOT/scripts/hostoo.env"
fi

HOSTOO_SSH="${HOSTOO_SSH:-}"
HOSTOO_SSH_PORT="${HOSTOO_SSH_PORT:-}"
HOSTOO_REMOTE_BUILD="${HOSTOO_REMOTE_BUILD:-public_html/build}"
BRANCH="${1:-014}"

# Git Bash no Windows expande ~ para C:/Users/... — isso não é a Hostoo.
case "$HOSTOO_REMOTE_BUILD" in
  /c/*|~/public_html*|\$HOME/*)
    HOSTOO_REMOTE_BUILD=public_html/build
    ;;
esac
HOSTOO_REMOTE_BUILD="${HOSTOO_REMOTE_BUILD#\~/}"

if [[ -z "$HOSTOO_SSH" || -z "$HOSTOO_SSH_PORT" ]]; then
  echo "ERRO: preencha scripts/hostoo.env (HOSTOO_SSH e HOSTOO_SSH_PORT)."
  exit 1
fi

SSH=(ssh -m hmac-sha2-512 -o IdentitiesOnly=no -p "$HOSTOO_SSH_PORT" "$HOSTOO_SSH")
SCP=(scp -o MACs=hmac-sha2-512 -P "$HOSTOO_SSH_PORT")

if [[ "${HOSTOO_SCP_ONLY:-0}" != "1" ]]; then
echo "==> 1/3 git + PHP 8.3 no servidor"
"${SSH[@]}" bash -s "$BRANCH" "${HOSTOO_SKIP_GIT:-0}" <<'REMOTE'
set -euo pipefail
BRANCH="$1"
SKIP_GIT="${2:-0}"
cd "$HOME/markcraft"

if [[ "$SKIP_GIT" != "1" ]]; then
  git fetch origin "$BRANCH"
  git checkout "$BRANCH" 2>/dev/null || git checkout -B "$BRANCH" "origin/$BRANCH"
  git reset --hard "origin/$BRANCH"
fi

PHP=""
for c in \
  /opt/cpanel/ea-php83/root/usr/bin/php \
  /opt/cpanel/ea-php82/root/usr/bin/php \
  /opt/alt/php83/usr/bin/php \
  /usr/local/bin/php83 \
  php83
do
  if [[ -x "$c" ]] || command -v "$c" >/dev/null 2>&1; then
    bin="$c"
    [[ -x "$c" ]] || bin="$(command -v "$c")"
    ver="$("$bin" -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')"
    if "$bin" -r 'exit(version_compare(PHP_VERSION,"8.2.0")>=0?0:1);'; then
      PHP="$bin"
      echo "PHP CLI: $PHP ($ver)"
      break
    fi
  fi
done
if [[ -z "$PHP" ]]; then
  echo "ERRO: não achei PHP 8.2+ no SSH (o php padrão é 8.0). No painel Hostoo, ative PHP 8.3 para CLI/SSH."
  echo "Candidatos:"
  ls -d /opt/cpanel/ea-php8*/root/usr/bin/php 2>/dev/null || true
  exit 1
fi

COMPOSER="$(command -v composer || true)"
if [[ -z "$COMPOSER" ]]; then
  echo "ERRO: composer não está no PATH."
  exit 1
fi
"$PHP" "$COMPOSER" install --no-dev --optimize-autoloader --no-interaction

if grep -q '^IMAGE_STUDIO_BG_REMOVAL_DRIVER=' .env; then
  sed -i 's/^IMAGE_STUDIO_BG_REMOVAL_DRIVER=.*/IMAGE_STUDIO_BG_REMOVAL_DRIVER=imgly/' .env
else
  printf '\nIMAGE_STUDIO_BG_REMOVAL_DRIVER=imgly\n' >> .env
fi

"$PHP" artisan migrate --force
"$PHP" artisan view:clear
"$PHP" artisan config:clear
"$PHP" artisan config:cache
"$PHP" artisan route:cache
echo "SERVIDOR: $(git rev-parse --abbrev-ref HEAD) $(git log -1 --oneline)"
REMOTE
fi

echo "==> 2/3 npm run build (já pode estar feito; roda de novo se faltar manifest)"
if [[ ! -f public/build/manifest.json ]]; then
  npm run build
fi

echo "==> 3/3 scp public/build → servidor"
"${SCP[@]}" -r public/build/. "$HOSTOO_SSH:$HOSTOO_REMOTE_BUILD/"

echo "OK — teste https://markcraft.criasysweb.com.br/  e o Studio (remover fundo no navegador)."
