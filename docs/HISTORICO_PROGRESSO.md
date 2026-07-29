# Histórico de progresso — MarkCraft

Documento de continuidade para retomar o trabalho no chat sem perder contexto.  
**Última atualização:** 29/07/2026 — scroll horizontal oculto + drag-and-drop de imagem no canvas.

---

## Estado atual do repositório

- **Remoto:** https://github.com/pvitorv/MarkCraft (privado)
- **Branch de trabalho atual:** `003-studio-canvas-ux` (padrão: `001`, `002`, `003`…)
- **Última branch com entrega:** `002-continuidade-produto`
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
- Sidebar abas verticais: Ferramentas · Texto · Mídia · Fundo · Camadas · Exportar (`image_studio_aside`)
- Bug `\n` literal: templates com `chr(10)`; `normalizeMultilineText` + **Textbox**; repair no load/template; draft `v2`
- rembg destacado (badge GRÁTIS) na aba Mídia
- CTA “Usar no Blog CriaSys” no topo e Exportar
- Expandido: z-index acima do navbar; modais acima do expand
- **Limpar workspace** = prancheta de verdade (branco, sem objetos, sem underlay, limpa draft)
- Modal **Créditos** (home rodapé + Studio + menu mobile) + `docs/CREDITS.md`
- **Scroll horizontal:** shell `.mc-app` / `.mc-app-shell` com `overflow-x: hidden`; viewport do canvas com `overflow: hidden` (corta vazamento do `transform: scale`); dropzone `overflow-x: hidden`
- **Drag-and-drop:** soltar imagem(ns) da pasta na área do canvas (PNG/JPG/WebP/GIF/SVG); highlight “Solte a imagem aqui”; reutiliza `imageStudioAddImageFromFile`

### Arquivos-chave
- `resources/views/partials/credits_modal.blade.php`, `hero_blog_panel`, `bridge_blog_section`, `social_proof_placeholder`
- `resources/views/studio/partials/image_studio_aside.blade.php`
- `resources/views/studio/partials/image_studio_workspace.blade.php` (dropzone + clip do scale)
- `resources/js/image-studio/imageStudio.js` (drag/drop + upload compartilhado)
- `resources/css/studio.css` (overflow global + dropzone)
- `docs/CREDITS.md`, `docs/HISTORICO_PROGRESSO.md`

---

## Próximos passos

1. Confirmar `BLOG_CRIASYS_URL` / `BLOG_CRIASYS_REGISTER_URL` reais no `.env`
2. Teste com 10–20 usuários → preencher `#prova-social` com depoimentos reais
3. Portar melhorias do Studio (sidebar Elementos split, créditos) para o Blog CriaSys Web se ainda pendente
4. Revisar mobile do hero / sidebar Studio em notebooks

> Continuar via `docs/HISTORICO_PROGRESSO.md`. Branches numeradas. MarkCraft = studio gratuito; Blog CriaSys Web = plataforma completa.
