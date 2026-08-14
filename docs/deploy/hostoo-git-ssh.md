# Hostoo — Git + SSH (deploy sem FTP)

**Produção:** https://markcraft.criasysweb.com.br/  
**Repo (público, AGPL):** https://github.com/pvitorv/MarkCraft

**Não** coloque usuário SSH, host, porta ou senha neste arquivo. Guarde em `docs/ACESSOS-LOCAL.md` e em `scripts/hostoo.env` (ambos no `.gitignore`).

## Deploy código (no servidor, via SSH)

```bash
cd ~/markcraft
git fetch origin BRANCH
git checkout BRANCH
git pull origin BRANCH
composer install --no-dev --optimize-autoloader
php artisan view:clear
php artisan config:cache
```

Ou: `bash scripts/hostoo-git-pull.sh BRANCH` **já logado no servidor**.

## Deploy build (máquina local)

1. Copie `scripts/hostoo.env.example` → `scripts/hostoo.env` e preencha `HOSTOO_SSH` e `HOSTOO_SSH_PORT`.
2. `bash scripts/hostoo-sync-build.sh`

O script recusa rodar sem essas variáveis (sem valor padrão no Git).

## Depois de tornar o repo público

Se algum commit antigo ainda mostrar host/usuário/porta de SSH, **troque a senha SSH / chaves no painel da hospedagem**. Apagar só o arquivo no commit novo **não** apaga o histórico.
