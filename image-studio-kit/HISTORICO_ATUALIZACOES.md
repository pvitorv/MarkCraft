# Histórico — Image Studio Kit (atualização BlogCriaSysWeb)

**Data deste pacote:** 2026-07-28  
**Origem atual:** `BlogCriaSysWeb` · branch **`014`** (pós fechamento da **`013`**)  
**Kit anterior:** CriaSys Editor · branch `040` (handoffs `017`–`027`)

Este arquivo resume **tudo o que mudou** no Image Studio desde o kit antigo até o espelho atual em `source/`.  
Para a próxima IA num **segundo projeto**: leia também `GUIA_REMBG_SUBSTITUIR_IMGLY.md` e `INVENTARIO.md`.

---

## Linha do tempo (resumo)

| Fase | Onde | O quê |
|------|------|--------|
| Kit original | CriaSys Editor `040` | Fabric + Alpine, layouts, tipografia, ícones, rembg **opcional**, `@imgly` como remoção no browser |
| Importação | Blog / MiniViajante → BlogCriaSysWeb | Studio no painel do blog (`/painel/.../studio`) |
| Branches `012`–`013` | BlogCriaSysWeb | Formatos, layouts, marca, crop; **rembg padrão**; **Pacotes** separados de **Layouts**; engine visual (gradiente, sombra, stroke…) |
| Branch `014` | BlogCriaSysWeb | Handoff + memória do projeto; este kit sincronizado |

Handoffs antigos do Editor (ainda em `source/handoffs/HANDOFF_017.md` … `027`): tipografia, ícones, guias, zoom, etc.  
Handoff novo do blog: `source/handoffs/HANDOFF_BRANCH_014.md`.

---

## 1. Remoção de fundo: IMG.LY → rembg (mudança crítica)

### Antes (kit / browser)

- Pacote npm **`@imgly/background-removal`** (licença **AGPL**) rodava **no navegador**.
- Cada cliente baixava modelo WASM; risco de licença/compliance; UX lenta no 1º uso.

### Depois (BlogCriaSysWeb — padrão)

- Driver padrão: **`rembg`** (Python open source) **no servidor**.
- PHP chama `scripts/remove-background.py` via `BackgroundRemovalService`.
- Front envia blob para `POST …/studio/remover-fundo` e recebe PNG sem fundo.
- `@imgly` **permanece instalado**, mas **só carrega** se `IMAGE_STUDIO_BG_REMOVAL_DRIVER=imgly` (isolado; não usar sem licença).
- `off` desliga a feature.

**Instruções completas para repetir noutro projeto:** → `GUIA_REMBG_SUBSTITUIR_IMGLY.md`  
**Deploy Hostoo / VPS:** → `source/docs/deploy/rembg-hostoo.md`

Bugs já resolvidos nesta linha (não reintroduzir):

- Seleção Fabric perdida no clique após remoção.
- `Cache::remember` guardando `false` e “envenenando” disponibilidade por ~30 min.
- Windows Apache: `_Py_HashRandomization_Init` → processo precisa de `SystemRoot` + `PYTHONHASHSEED=0`.

---

## 2. Layouts ≠ Pacotes (nunca misturar)

Erro grave já cometido e corrigido no blog: esvaziar templates de redes e fazer `union` com packs no modal **Layouts**.

| Modal UI | Conteúdo | Config |
|----------|----------|--------|
| **Layouts** | Redes / formatos (~32) | `config/image_studio.php` → `templates` |
| **Pacotes** | Editorial, marca, vendas R$, mockups (~57) | `config/image_studio_packs.php` |

Serviço:

- `templateCatalog()` → **só** layouts
- `packTemplateCatalog()` / `packsCatalog()` → **só** packs

Teste de regressão em `tests/Feature/ImageStudioTest.php` (slugs `story_quote` vs `pack_*`, categoria `sales`).

---

## 3. Engine de templates (canvas, não só modal)

O usuário exigiu qualidade **no canvas Fabric**, não só tiles bonitos.

`resources/js/image-studio/imageStudio.js` passou a renderizar templates com:

- `rect` / `circle` / `ellipse` / `line`
- `gradient`, `stroke`, `shadow`
- Tipografia Playfair / Space Grotesk / DM Sans (além do catálogo de fontes)

Pacotes reescritos com composição densa; categoria **Vendas & preços (R$)** adicionada a pedido.

Contagens no fechamento da `013`:

- Editorial & web: 13  
- Identidade & logo: 21  
- Vendas & preços: 14  
- Mockups: 9  
- Layouts de redes: **32** (restaurados; não apagar)

---

## 4. Paths no BlogCriaSysWeb (vs kit antigo)

| Antigo (CriaSys Editor / kit 040) | Atual (BlogCriaSysWeb) |
|-----------------------------------|-------------------------|
| `resources/js/imageStudio.js` | `resources/js/image-studio/imageStudio.js` (+ `app-studio.js`) |
| `Api/ImageStudioController` | `Painel/ImageStudioController` |
| Partials em `projects/` | `resources/views/painel/studio/*` |
| API genérica de projeto | Rotas painel + middleware `blog.studio` |
| — | `config/image_studio_packs.php` (**novo**) |
| — | `resources/css/studio.css` |
| — | `docs/deploy/rembg-hostoo.md` |

O espelho em `source/` usa os **paths atuais**. Pastas antigas do kit foram removidas do espelho para não confundir.

---

## 5. Dependências npm relevantes

- `fabric` ^7.4 (kit antigo tinha 6.x)
- `ag-psd`, `alpinejs`, `bootstrap-icons`, `jspdf`
- `@imgly/background-removal` — **opcional / legado**; default é rembg

Python (servidor): `rembg`, `pillow`, `onnxruntime` — ver guia rembg.

---

## 6. O que levar para um segundo projeto

1. Copiar pasta `image-studio-kit/` para o outro repositório (ou só `source/` + docs).
2. Ler **`GUIA_REMBG_SUBSTITUIR_IMGLY.md`** se o outro projeto ainda usa IMG.LY no browser.
3. Adaptar acoplamentos de `Blog` / painel (ver `snippets/ACOPLAMENTOS.md` e `DEPENDENCIAS.md`).
4. **Não** fundir packs no catálogo de Layouts.
5. Rodar `npm run build` + testes de studio após integrar.
6. Configurar `.env` com `IMAGE_STUDIO_BG_REMOVAL_DRIVER=rembg` e `REMBG_PYTHON=…`.

---

## 7. Preferências do dono (aprendidas na dor)

1. Capricho no **resultado do canvas**.  
2. Separação rígida: Layouts ≠ Pacotes ≠ Formatos.  
3. Não apagar layouts de redes “porque pareciam feios”.  
4. Pacotes densos, modernos, com vendas em **R$**.  
5. PT-BR, direto; commits só sob pedido.

---

*Atualize este histórico quando fechar a próxima fatia relevante do Studio.*
