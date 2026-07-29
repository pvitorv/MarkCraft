# Dependências e acoplamentos — Image Studio Kit

Atualizado com o espelho **BlogCriaSysWeb** (`014`).

## npm (canvas)

Do `package.json` da origem:

```json
{
  "@imgly/background-removal": "^1.7.0",
  "ag-psd": "^31.0.2",
  "alpinejs": "^3.15.12",
  "bootstrap-icons": "^1.13.1",
  "fabric": "^7.4.0",
  "jspdf": "^4.2.1"
}
```

Notas:

- **`@imgly/background-removal`**: legado / opcional. Default do produto é **rembg no servidor**. Só carregue o pacote se `IMAGE_STUDIO_BG_REMOVAL_DRIVER=imgly`.
- `fabric` na origem está em **7.x** (kit antigo usava 6.x).

Scripts úteis:

```bash
npm run icons:sync   # se existir: node scripts/sync-bootstrap-icons.mjs
npm run build        # após mudar JS/CSS do studio
```

## PHP / Laravel

- Controller: `App\Http\Controllers\Painel\ImageStudioController`
- Services: `ImageStudioService`, `BackgroundRemovalService`
- Configs: `image_studio`, `image_studio_packs`, `image_studio_fonts`, `image_studio_icons`, `image_studio_shapes`, `image_studio_stickers`
- Env: `IMAGE_STUDIO_BG_REMOVAL_DRIVER`, `REMBG_PYTHON`

## Python (remoção de fundo — padrão)

```bash
pip install rembg pillow onnxruntime
```

- Script: `scripts/remove-background.py`
- Guia deploy: `source/docs/deploy/rembg-hostoo.md`
- Migração IMG.LY → rembg: `GUIA_REMBG_SUBSTITUIR_IMGLY.md`

## Acoplamentos a adaptar no segundo projeto

O código do espelho assume o **painel do blog**:

| Acoplamento | Onde | No destino |
|-------------|------|------------|
| Middleware `blog.studio` / plano Pro | `routes/web.php` | Gate/plano próprio ou auth simples |
| Prefixo painel do blog | Rotas `studio/*` | Ajustar prefixo/nome das rotas |
| Marca do blog no canvas | `ImageStudioService` / brand | Logo/cores do produto destino |
| Salvar arte no blog | `store` / export | MarkCraft: preferir só download + limpar |
| Vite entry `app-studio.js` | `vite.config` / blade | Registrar entry do studio |
| Meta `studio-remove-bg-url` | Blade | Rota POST rembg do destino |

## Regra Layouts ≠ Pacotes

- `ImageStudioService::templateCatalog()` = só `config('image_studio.templates')`
- Packs = só `config/image_studio_packs.php`
- **Nunca** `union` dos dois no modal Layouts

## Formatos

| Formato | Papel |
|--------|--------|
| PSD | Camadas (`ag-psd`) |
| PNG/JPG/WebP | Import/export |
| SVG | Elementos |
| PDF | Export (`jspdf`) |
| Canva / Affinity / Corel nativos | Não suporte |
