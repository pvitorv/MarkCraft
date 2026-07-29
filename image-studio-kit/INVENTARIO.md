# Inventário — arquivos no kit (BlogCriaSysWeb)

Espelho em `source/` com **paths relativos ao root** do BlogCriaSysWeb.  
Atualizado em **2026-07-28** (branch `014`).

## Backend

- `app/Http/Controllers/Painel/ImageStudioController.php`
- `app/Services/ImageStudio/ImageStudioService.php`
- `app/Services/ImageStudio/BackgroundRemovalService.php`

## Config

- `config/image_studio.php` — presets, layouts (`templates`), `background_removal`
- `config/image_studio_packs.php` — **Pacotes** (editorial, marca, vendas, mockups)
- `config/image_studio_fonts.php`
- `config/image_studio_icons.php`
- `config/image_studio_shapes.php`
- `config/image_studio_stickers.php`

## Frontend

- `resources/js/image-studio/imageStudio.js` — engine Fabric + mixin Alpine
- `resources/js/image-studio/app-studio.js` — bootstrap da página painel
- `resources/js/image-studio/imageStudioShapes.js`
- `resources/js/image-studio/imageStudioTextFonts.js`
- `resources/css/studio.css`
- `resources/views/painel/studio/index.blade.php`
- `resources/views/painel/studio/image_studio_workspace.blade.php`
- `resources/views/painel/studio/image_studio_sidebar_panels.blade.php`
- `resources/views/painel/studio/image_studio_modals.blade.php`

## Scripts

- `scripts/remove-background.py` — CLI rembg chamada pelo PHP
- `scripts/sync-bootstrap-icons.mjs`

## Docs / testes

- `docs/deploy/rembg-hostoo.md`
- `tests/Feature/ImageStudioTest.php`

## Handoffs

- `handoffs/HANDOFF_017.md` … `HANDOFF_027.md` — histórico CriaSys Editor
- `handoffs/HANDOFF_BRANCH_014.md` — estado atual no blog
- `handoffs/AGENTS_ORIGEM.md` — cópia do `AGENTS.md` da origem

## Snippets (referência)

- `snippets/routes_studio_painel.php` — rotas studio do painel
- `snippets/env_image_studio.example.txt` — nomes de variáveis (sem secrets)
- `snippets/ACOPLAMENTOS.md` — pontos de integração
- `snippets/routes_api.php` — legado (API do Editor antigo; preferir rotas painel)

## NÃO copiado de propósito

- Billing / afiliados / landing do blog inteiro
- Middleware `blog.studio` (reimplementar no destino)
- Modelos `Blog` / storage de artes do painel (adaptar)
- `node_modules`, `vendor`, `.env`, `public/build`
- Vídeo, TTS, slideshow do Editor CriaSys
