# Deploy MarkCraft na Hostoo (subdomínio)

Subdomínio sugerido: **`markcraft.criasysweb.com.br`**  
Document root do subdomínio: **pasta `public/`** dentro da app (não a raiz do Laravel).

O domínio principal [criasysweb.com.br](http://criasysweb.com.br/) pode estar suspenso — o subdomínio é independente.

---

## 1. Gerar o zip (máquina local)

Na branch `008-cms-links-brand` (ou main após merge):

```bash
composer test
composer audit          # revisar advisories
npm audit --production

composer install --no-dev --optimize-autoloader
npm ci && npm run build

bash scripts/build-deploy-zip.sh
```

Saída: `markcraft-deploy-YYYYMMDD-hostoo.zip` na raiz do projeto.

**Pastas excluídas do zip:** kits locais (`image-studio-kit`, `kitStartTransform`, `desktop`, `_PACK_*`), `node_modules`, `.git`, `tests`, `.env`, logs/cache.

**Incluído:** `vendor/` (prod), `public/build/`, código da app.

---

## 2. Hostoo — criar subdomínio

1. Painel Hostoo → **Subdomínios** → criar `markcraft`
2. **Document root:** apontar para `.../markcraft/public` (após upload)
3. Ativar **SSL** (Let’s Encrypt)
4. Criar banco **MySQL** e anotar host, nome, usuário, senha

Estrutura típica:

```text
/home/SEU_USUARIO/
  domains/markcraft.criasysweb.com.br/
    markcraft/          ← extrair zip aqui (pasta interna do zip)
      app/
      public/           ← document root do subdomínio
      artisan
      ...
```

---

## 3. Upload e extração

- Enviar o zip via **Gerenciador de arquivos** ou **SFTP**
- Extrair mantendo a pasta `markcraft/` (conteúdo com `artisan` na raiz)

---

## 4. Configurar `.env` no servidor

```bash
cd /caminho/para/markcraft
cp .env.hostoo.example .env
nano .env   # preencher DB, APP_KEY, mail
php artisan key:generate
```

Variáveis críticas:

| Variável | Produção |
|----------|----------|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | `https://markcraft.criasysweb.com.br` |
| `BLOG_CRIASYS_CTA_READY` | `false` até Blog no ar |
| `IMAGE_STUDIO_BG_REMOVAL_DRIVER` | `off` na 1ª subida |

---

## 5. Artisan pós-deploy

```bash
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache

chmod -R ug+rwx storage bootstrap/cache

php artisan markcraft:make-admin seu@email.com
```

Teste: `https://markcraft.criasysweb.com.br/up`  
CMS: `https://markcraft.criasysweb.com.br/admin/cms`

---

## 6. rembg (opcional — fase 2)

Ver `docs/deploy/rembg-hostoo.md`. Só depois do site estável.

---

## 7. Checklist segurança (pré-go-live)

- [ ] `APP_DEBUG=false`
- [ ] HTTPS ativo
- [ ] `.env` fora do web root (nunca em `public/`)
- [ ] Admin criado via artisan (não seed público)
- [ ] Links Blog no CMS desativados se destino suspenso
- [ ] `composer audit` / `npm audit` revisados

---

## Auditorias (última geração do pacote)

Rodar localmente antes de cada deploy:

- **PHPUnit:** 55 testes (auth, CMS, landing, studio shortcuts)
- **composer audit:** advisories em `guzzlehttp/guzzle`, `league/commonmark` (transitivos Laravel) — avaliar `composer update` antes do próximo deploy
- **npm audit:** `dompurify`, `image-size` via `pptxgenjs` — avaliar `npm audit fix` com cuidado

---

Documento irmão: `docs/deploy/rembg-hostoo.md`
