# Histórico de progresso — MarkCraft

Documento de continuidade para retomar o trabalho no chat sem perder contexto.  
**Última sessão registrada:** 28–29/07/2026 (noite).

---

## Estado atual do repositório

- **Remoto:** https://github.com/pvitorv/MarkCraft (privado)
- **Branch desta entrega:** `feature/2026-07-28-hub-navbar-ferramentas`
- **Base anterior em `main`:** landing dark, Studio autenticado, hub em modais glass, `.gitignore` reforçado

### Como rodar local

```bash
php artisan serve          # preferir build estático (não HMR)
npm run build              # após mudanças em JS/CSS Vite
php artisan migrate        # inclui tabela short_links
```

Evitar `npm run dev` no Studio se o HMR voltar a “recarregar” o canvas (histórico de loops com `public/hot`).

---

## O que foi feito nesta sessão

### 1. Hub: Ferramentas reais (client-side + encurtador)

Quatro ferramentas no modal glass (`/?hub=ferramentas` ou atalhos):

| Slug | Onde roda | Observação |
|------|-----------|------------|
| `encurtador` | servidor | `POST /api/tools/shorten`, redirect `GET /s/{code}`, model `ShortLink` |
| `conversor-imagens` | navegador | via `markCraftTools.js` |
| `conversor-pdf` | navegador | `pdfjs-dist` + `jspdf` |
| `compressor-pdf` | navegador | client-side |

Arquivos-chave:
- `resources/js/markCraftTools.js`
- `resources/js/markCraftHub.js` (Alpine: `open`, `tool`, `navOpen`, openHub/openTool)
- `resources/views/partials/hub_glass_modals.blade.php`
- `app/Http/Controllers/ShortLinkController.php`
- `database/migrations/2026_07_29_002729_create_short_links_table.php`
- `config/markcraft.php` (lista de tools/packs/donations)

Rotas antigas `/ferramentas`, `/packs`, `/apoiar` **redirecionam** para `/?hub=...`.

### 2. Auth e conta

- Após login/registro/verificação → redireciona para **`route('home')`** (não mais dashboard→studio direto).
- Menu de conta dark: nome, e-mail, perfil, desconectar (`partials/account_menu.blade.php`).
- Perfil em português (labels dos forms).

### 3. Navbar dark unificada + responsiva

- Partial: `resources/views/partials/dark_site_navbar.blade.php`
- Usada na **landing** e no **Studio**.
- **Desktop (`lg+`):** links/conta + Packs + Apoiar + Studio.
- **Mobile/tablet (`< lg`):** Studio neon + **menu hamburger** (painel glass com ferramentas, packs, apoiar, conta).
- Layout claro (`layouts/markcraft.blade.php`) também ganhou hamburger.

Botões **Packs · Apoiar · Studio** padronizados com a classe compartilhada:

- `.mc-nav-action` — altura 36px, padding/fonte/ícone iguais
- Studio: `partials/studio_nav_button.blade.php` (neon `#39ff14`)
- Packs/Apoiar: `partials/hub_shortcut_buttons.blade.php`

### 4. Landing responsiva

- Tipografia/paddings/CTAs adaptados a mobile.
- Mock “telefone” do hero oculto em telas estreitas.
- Atalhos de formato + hub com grids responsivos.
- Anúncio `ad_landing_hero` fica **abaixo** do bloco de hub (não no hero).

### 5. Studio em telas menores

- Workspace: sidebar empilha acima do canvas (`flex-col` → `lg:flex-row`).
- Navbar do Studio compartilhada com a landing (hamburger + hub).

---

## Decisões de produto (não reabrir sem pedido)

- Artes **não** persistem no servidor — editar → baixar → limpar.
- Hub Packs/Apoiar/Ferramentas = **modais**, não páginas dedicadas.
- Família **CriaSys**; Studio só autenticado em `/studio`.
- Preferir assets via `public/build` + `npm run build` no fluxo diário.

---

## Próximos passos sugeridos (amanhã)

1. Validar visualmente navbar hamburger + botões Packs/Apoiar/Studio alinhados (mobile 375 / tablet 768 / desktop).
2. Rodar `php artisan migrate` se `short_links` ainda não existir no ambiente.
3. Testar as 4 ferramentas de ponta a ponta (arquivos reais + encurtador).
4. Revisar se `public/build` está atualizado após pull (`npm run build`).
5. Backlog possível: packs afiliados reais, Pix/checkout de apoio, polish do Studio mobile (toolbar densa).

---

## Como orientar o próximo chat

Cole ou cite este arquivo no início, por exemplo:

> Continuar a partir de `docs/HISTORICO_PROGRESSO.md` na branch `feature/2026-07-28-hub-navbar-ferramentas`.

Checklist rápido para o agente:
- [ ] Ler este histórico
- [ ] `git status` / branch atual
- [ ] Não reintroduzir `beforeunload` / HMR problemático no Studio
- [ ] Não commitar `.env` nem credenciais

---

## Arquivos novos relevantes desta entrega

```
app/Http/Controllers/ShortLinkController.php
app/Models/ShortLink.php
database/migrations/2026_07_29_002729_create_short_links_table.php
resources/js/markCraftTools.js
resources/views/partials/account_menu.blade.php
resources/views/partials/dark_site_navbar.blade.php
resources/views/partials/hub_shortcut_buttons.blade.php
resources/views/partials/studio_nav_button.blade.php
resources/views/partials/tool_icon.blade.php
resources/views/partials/tool_shortcut_buttons.blade.php
docs/HISTORICO_PROGRESSO.md
```
