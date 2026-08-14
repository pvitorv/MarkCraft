# Deploy MarkCraft na Hostoo (subdomínio)

**Produção:** [https://markcraft.criasysweb.com.br/](https://markcraft.criasysweb.com.br/)

O Blog CriaSys Web fica em **[https://blog.criasysweb.com.br](https://blog.criasysweb.com.br/)** (MarkCraft no subdomínio `markcraft`; site principal em `criasysweb.com.br`).

---

## URLs de entrada (produção)

| O quê | URL |
|-------|-----|
| Landing | https://markcraft.criasysweb.com.br/ |
| Login | https://markcraft.criasysweb.com.br/login |
| Cadastro | https://markcraft.criasysweb.com.br/register |
| Studio | https://markcraft.criasysweb.com.br/studio |
| Health | https://markcraft.criasysweb.com.br/up |
| CMS | https://markcraft.criasysweb.com.br/admin/cms |
| CMS Blog links | https://markcraft.criasysweb.com.br/admin/cms?tab=landing#cms-blog-links |
| CMS Packs | https://markcraft.criasysweb.com.br/admin/cms?tab=packs#cms-packs |
| CMS Doações | https://markcraft.criasysweb.com.br/admin/cms?tab=donations#cms-donations |
| CMS Promos | https://markcraft.criasysweb.com.br/admin/cms?tab=promos |

Bookmark pessoal (não versionado): copie `docs/deploy/ACESSOS-LOCAL.example.md` → `docs/ACESSOS-LOCAL.md`.

---

## Estrutura na Hostoo (SamtooWeb)

Na Hostoo o **document root** costuma ser `public_html`, **separado** da pasta Laravel:

```text
/home/SEU_USUARIO/
  markcraft/              ← zip da app (artisan, app/, vendor/, .env)
  public_html/            ← zip do public (index.php, build/, brand/)
```

O `index.php` em `public_html` deve apontar para a raiz Laravel (caminho absoluto recomendado):

```php
$laravelRoot = '/home/SEU_USUARIO/markcraft';
```

**Symlink obrigatório** (Laravel procura Vite em `markcraft/public/build/`):

```bash
ln -s /home/SEU_USUARIO/public_html/build /home/SEU_USUARIO/markcraft/public/build
```

**PHP 8.3 na web:** o CLI pode ser 8.3 enquanto o site usa 8.0 — force no topo do `.htaccess` do `public_html`:

```apache
AddHandler application/x-httpd-ea-php83 .php
```

**Evite** extrair o zip da app dentro de uma pasta que já se chama `markcraft` (vira `markcraft/markcraft`). Extraia um nível acima ou mova o conteúdo para cima.

---

## 1. Gerar os zips (máquina local)

```bash
composer test
composer install --no-dev --optimize-autoloader
npm ci && npm run build

bash scripts/build-deploy-zip.sh
php scripts/make-public-deploy-zip.php
```

Saída:

| Arquivo | Conteúdo | Tamanho típico |
|---------|----------|----------------|
| `markcraft-deploy-YYYYMMDD-hostoo.zip` | App Laravel **sem** `public/` | ~8 MB |
| `public/markcraft-public-deploy.zip` | Conteúdo de `public/` | ~7 MB |

**Excluído do zip da app:** `public/`, `storage/app/tmp`, kits locais, `node_modules`, `.git`, `tests`, `.env`.

---

## 2. Hostoo — subdomínio e banco

1. Painel → **Subdomínio** `markcraft.criasysweb.com.br`
2. Document root → **`public_html`** (não a pasta `markcraft/`)
3. SSL (Let's Encrypt)
4. MySQL — anotar host, banco, usuário, senha

---

## 3. Upload e extração

1. **App:** extrair `markcraft-deploy-*.zip` em `~/markcraft/` (deve existir `artisan` na raiz).
2. **Public:** extrair `markcraft-public-deploy.zip` **dentro** de `~/public_html/`.
3. Ajustar `public_html/index.php` (caminho Laravel).
4. Criar symlink `build` (ver acima).

---

## 4. `.env` no servidor

Copie `.env.hostoo.example` → `.env` e preencha DB.

| Variável | Produção |
|----------|----------|
| `APP_URL` | `https://markcraft.criasysweb.com.br` |
| `APP_DEBUG` | `false` |
| `BLOG_CRIASYS_CTA_READY` | `false` até Blog no ar |
| `IMAGE_STUDIO_BG_REMOVAL_DRIVER` | `imgly` (navegador). `rembg` só se for usar Python — ver rembg-hostoo.md |

---

## 5. Artisan pós-deploy

```bash
cd ~/markcraft
php artisan key:generate --force
php artisan migrate --force
php artisan storage:link
chmod -R ug+rwx storage bootstrap/cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan markcraft:make-admin seu@email.com
```

---

## 6. Remoção de fundo

**Padrão de produção:** `IMAGE_STUDIO_BG_REMOVAL_DRIVER=imgly` (WASM no navegador, AGPL). Não exige Python.

Motor **rembg** no servidor é legado/opcional — só se precisar: **`docs/deploy/rembg-hostoo.md`**.

---

## 7. Checklist segurança

- [ ] `APP_DEBUG=false`
- [ ] HTTPS ativo
- [ ] `.env` fora do `public_html`
- [ ] Admin via artisan (usuário já cadastrado)
- [ ] `info.php` de teste removido do `public_html`
- [ ] SSH / senhas **não** estão em arquivos versionados (só `docs/ACESSOS-LOCAL.md` e `scripts/hostoo.env`)

---

Documento irmão: `docs/deploy/rembg-hostoo.md` · Git/SSH: `docs/deploy/hostoo-git-ssh.md` · Licença/fonte: `docs/FONTE-E-LICENCA.md` · Segurança: `SECURITY.md`
