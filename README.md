# MarkCraft

Ferramenta web gratuita da família **CriaSys** para criadores, marketing de vendas e designers aventureiros.

**Produção:** [https://markcraft.criasysweb.com.br/](https://markcraft.criasysweb.com.br/) · Deploy Hostoo: `docs/deploy/hostoo-markcraft.md`

## O que é

- **Studio** (login obrigatório): canvas Fabric.js — presets de redes, texto, formas, remover fundo, export PNG/JPG/WebP/SVG/PSD/PDF/JSON
- **Fluxo:** trabalhar → **baixar** → **limpar workspace** (artes **não** ficam no servidor)
- Hub **Ferramentas** (stubs): encurtador, conversor de imagens, conversor PDF, compressor PDF
- **Packs / afiliados** (stub) + slots de **ADS** / publicidade própria
- Página **Apoiar** — doações Pix/cartão a partir de R$ 2 (gateway stub)

## Formatos

| Suportado | Não prometido |
|-----------|----------------|
| PSD (camadas), PNG, JPG, WebP, SVG, PDF | Projeto nativo Canva, Affinity (`.af*`), Corel (`.cdr`) |

## Ambiente local

```env
APP_NAME=MarkCraft
DB_CONNECTION=mysql
DB_DATABASE=mark_craft
DB_USERNAME=root
DB_PASSWORD=
```

O database `mark_craft` deve existir vazio antes das migrations.

```bash
composer install
npm install
cp .env.example .env   # se necessário — já configure DB MarkCraft
php artisan key:generate
php artisan migrate
npm run build
php artisan serve      # ou vhost markcraft.test (Laragon)
```

## ADS

Slots com `data-ad-slot`:

| ID | Onde |
|----|------|
| `ad_landing_hero` | Landing |
| `ad_landing_mid` | Landing |
| `ad_app_sidebar` | Studio |
| `ad_app_top` | App logado |
| `ad_app_between_tools` | Hub ferramentas |
| `ad_packs_inline` | Packs |

Ligue com `ADS_ENABLED=true` e `ADS_PUBLISHER_ID` / `config/ads.php`. Troque placeholders de publicidade própria nas views/`x-ad-slot`.

## Doações

- Copy oficial em `/apoiar`
- `DONATION_PIX_KEY` e `DONATION_GATEWAY_URL` no `.env`
- Área de membros futura: **sem** upload livre de arquivos de terceiros (copyright)

## Stack

Laravel 12 · Breeze · Vite · Alpine · Tailwind · Fabric.js · ag-psd · `@imgly/background-removal`

## Image Studio (kit BlogCriaSysWeb `014`)

- Entry: `resources/js/image-studio/app-studio.js`
- Layouts ≠ Pacotes (`config/image_studio.php` vs `config/image_studio_packs.php`)
- Remoção de fundo: **rembg** (`IMAGE_STUDIO_BG_REMOVAL_DRIVER=rembg`) — ver `docs/deploy/rembg-hostoo.md`
- MarkCraft mantém: baixar/limpar (sem salvar no servidor), modal Elementos com sidebar, cor padrão teal nas formas

Origem do kit: pasta `image-studio-kit/` (espelho atual). Arquivo antigo: `01-image-studio-kit/`.
