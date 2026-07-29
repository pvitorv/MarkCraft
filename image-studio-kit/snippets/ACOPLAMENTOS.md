# Acoplamentos — referência

## A) BlogCriaSysWeb (origem atual do kit)

1. Rotas painel + middleware `blog.studio` → ver `snippets/routes_studio_painel.php`.
2. `Painel\ImageStudioController@index` carrega a view `painel.studio.index`.
3. Vite entry: `resources/js/image-studio/app-studio.js` (Alpine + `imageStudioMethods()`).
4. Catálogo via `studio/catalogo` (presets, templates/layouts, packs, fontes, brand, status rembg).
5. Remoção de fundo: `POST studio/remover-fundo` + meta `studio-remove-bg-url`.
6. Configs: `image_studio.php` (layouts) + `image_studio_packs.php` (pacotes) — **não misturar**.

## B) CriaSys Editor (kit legado 040 — só histórico)

1. `ProjectWebController::editor` injeta `$imageStudioCatalog`.
2. `editor.blade.php` embute JSON `#criasys-image-studio-*`.
3. `editor.js` faz `...imageStudioMethods()` de `imageStudio.js` (path antigo).
4. Partials em `projects/partials/` — **não usem** no espelho atual (removidos).

## Env (padrão atual)

```env
IMAGE_STUDIO_BG_REMOVAL_DRIVER=rembg
# REMBG_PYTHON=...
```

Detalhes: `GUIA_REMBG_SUBSTITUIR_IMGLY.md` e `snippets/env_image_studio.example.txt`.

## Capacidades (017–027 Editor + 012–013 Blog)

- Canvas Fabric.js, presets redes, texto/fontes, shapes, stickers, ícones
- Filtros, undo/redo, grid/snap, crop, marca do blog
- Templates Layouts + Pacotes (catálogos separados)
- Engine: gradient, line, ellipse, shadow, stroke
- Export PNG/JPG/SVG/JSON/PSD/PDF
- Remover fundo: **rembg padrão**; IMG.LY só se driver `imgly`

## O que o app destino deve IGNORAR (se for MarkCraft / hub)

- Slideshow, vídeo, narração, TTS do Editor
- Billing Mercado Pago do blog (a menos que o destino peça)
- Misturar packs no modal Layouts
