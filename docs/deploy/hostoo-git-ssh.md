# Hostoo — Git + SSH (deploy sem FTP)

**Produção:** https://markcraft.criasysweb.com.br/  
**Repo:** `git@github.com:pvitorv/MarkCraft.git`

Ver passo a passo completo na sessão 2026-08-11 (Deploy Key + `git pull` em `~/markcraft`).

## Deploy código

```bash
cd ~/markcraft
git fetch origin BRANCH
git checkout BRANCH
git pull origin BRANCH
composer install --no-dev --optimize-autoloader
php artisan view:clear
php artisan config:cache
```

## Deploy build (local)

```bash
npm run build
scp -P PORTA -r public/build/. usuario@seu-host-ssh:~/public_html/build/
```

Scripts: `scripts/hostoo-git-pull.sh`, `scripts/hostoo-sync-build.sh`
