# Histórico de progresso — MarkCraft

Documento de continuidade para retomar o trabalho no chat sem perder contexto.  
**Última atualização:** 29/07/2026 — sequência/kit (PPTX + ZIP PNG + carrosséis) no Image Studio.

---

## Estado atual do repositório

- **Remoto:** https://github.com/pvitorv/MarkCraft (privado)
- **Branch de trabalho atual:** `004-sequencia-pptx-kits` (padrão: `001`, `002`, `003`…)
- **Última branch com entrega:** `004-sequencia-pptx-kits`
- **Branches anteriores:** `003-studio-canvas-ux`, `002-continuidade-produto`, `001-hub-navbar-ferramentas`
- **PR anterior:** https://github.com/pvitorv/MarkCraft/pull/2 (`001-hub-navbar-ferramentas`)

```bash
php artisan serve
npm run build
php artisan migrate
```

```env
BLOG_CRIASYS_URL=
BLOG_CRIASYS_REGISTER_URL=
CRIASYS_PACKS_URL=
ADS_ENABLED=false
IMAGE_STUDIO_BG_REMOVAL_DRIVER=rembg
```

Preferir `npm run build` + `php artisan serve` no Studio (evitar HMR).

---

## Posicionamento (estratégia interna × copy pública)

| Papel | Nome | Como falar na UI |
|-------|------|------------------|
| Entrada / studio gratuito | **MarkCraft** | “Studio gratuito”, “editor de imagem”, “mesmo DNA do Image Studio do Blog” |
| Produto completo da linha | **Blog CriaSys Web** | “plataforma completa”, “blog + painel + studio no mesmo fluxo” |

**Evitar na interface:** isca, joia da coroa, cozinha, sabor.

Config: `config/markcraft.php` (`blog`, `promos`, `affiliate_packs`, `role: entry`).  
Sem AdSense genérico — vitrine CriaSys. Prova social só com feedback real.

---

## Entregas acumuladas

### Hub / landing
- Hub: 4 ferramentas + Packs/Apoiar em modais glass; encurtador `short_links`
- Navbar dark + hamburger; Packs amarelo / Apoiar rosa / Studio verde neon / tools azul
- Hero: valor MarkCraft primeiro; CTA neon verde; painel Blog secundário
- Formatos com miniaturas de proporção; hub com ícones maiores + legenda de cores
- Seção ponte Blog (`bridge_blog_section`); card Blog glass
- Placeholder **prova social reservado** (`#prova-social`) — sem números inventados
- Microcopy hero única: “Sem cartão · 100% grátis · Artes privadas…”
- Promo slots (`x-promo-slot`); ads legados off
- Landing com `overflow-x: hidden` no shell global

### Studio / Image Studio
- Sidebar abas verticais: Ferramentas · Texto · Mídia · Fundo · Camadas · **Sequência** · Exportar (`image_studio_aside`)
- Bug `\n` literal: templates com `chr(10)`; `normalizeMultilineText` + **Textbox**; repair no load/template
- rembg destacado (badge GRÁTIS) na aba Mídia
- CTA “Usar no Blog CriaSys” no topo e Exportar
- Expandido: z-index acima do navbar; modais acima do expand
- **Limpar workspace** = prancheta de verdade (branco, sem objetos, sem underlay, limpa draft)
- Modal **Créditos** (home rodapé + Studio + menu mobile) + `docs/CREDITS.md`
- **Scroll horizontal:** shell `.mc-app` / `.mc-app-shell` com `overflow-x: hidden`; viewport do canvas com `overflow: hidden`
- **Drag-and-drop:** soltar imagem(ns) da pasta na área do canvas (PNG/JPG/WebP/GIF/SVG)

#### Sequência / kit (29/07/2026 — branch `004`)
- Deck multi-página (`imageStudioDeckPages`) no rascunho local `markcraft-studio-draft-v3`
- Aba sidebar **Sequência** (não na toolbar do canvas)
- **Bug crítico:** `setImageStudioSidebarTab` precisa incluir `'slides'` no whitelist — senão o botão fica inerte
- Tipos de kit: **Apresentação** · **Redes sociais** · **Carrossel web**
- Presets rápidos: PPT 16:9, IG carrossel 1:1/4:5, LinkedIn, `web_carousel_*`
- Export:
  - **PPTX** via `pptxgenjs` (PNG full-bleed por página; MVP sem shapes editáveis)
  - **ZIP PNG sequência** via `jszip` (`card-01.png`, `frame-01.png`…)
  - **PDF multipágina** quando N>1
- Formatos em `config/image_studio.php`: `pptx`, `zip` + presets presentation/web carousel
- `exportBlob` propaga `pagePngDataUrls` / `pageJpegDataUrls` / `zipPrefix`

### Arquivos-chave
- `config/image_studio.php` (export_formats, presets presentation/web carousel)
- `resources/js/image-studio/imageStudio.js` (deck + pptx/zip/pdf multipágina)
- `resources/js/image-studio/app-studio.js` (draft v3 + download local)
- `resources/views/studio/partials/image_studio_aside.blade.php` (aba Sequência)
- `resources/views/studio/partials/image_studio_sidebar_panels.blade.php` (export ZIP/PPTX)
- `resources/css/studio.css` (chips do deck)
- `docs/CREDITS.md`, `docs/HISTORICO_PROGRESSO.md`

---

## Handoff rápido — portar para Blog CriaSys Web

1. `npm i pptxgenjs` (jszip já costuma vir transitivo; declare se precisar)
2. Registrar `pptx` + `zip` em `export_formats`
3. `exportPptxBlob` / `exportZipPngBlob` + deck Alpine (`imageStudioDeckPages`)
4. UI na **sidebar** (aba Sequência), não na toolbar
5. Incluir `'slides'` em `setImageStudioSidebarTab` allowed list
6. MVP = PNG full-bleed por página — não prometer PPT 100% editável
7. `npm run build`

---

## Próximos passos

1. Confirmar `BLOG_CRIASYS_URL` / `BLOG_CRIASYS_REGISTER_URL` reais no `.env`
2. Teste com 10–20 usuários → preencher `#prova-social` com depoimentos reais
3. Portar sequência/PPTX/ZIP para o Blog CriaSys Web
4. Fase 2 opcional: mapear textos Fabric → text boxes nativos do PPT
5. Revisar hit-test de scale nas bolinhas do Fabric (ainda sensível em alguns zooms)

> Continuar via `docs/HISTORICO_PROGRESSO.md`. Branches numeradas. MarkCraft = studio gratuito; Blog CriaSys Web = plataforma completa.
