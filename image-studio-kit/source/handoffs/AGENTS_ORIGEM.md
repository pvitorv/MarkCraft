# AGENTS.md — Blog CriaSys Web

> **Leia isto primeiro** ao abrir o projeto (pasta pode se chamar `BlogCriaSysWeb` ou ainda `BlogMiniViajante`).
> Memória completa da sessão: [`docs/historico/HANDOFF_BRANCH_014.md`](docs/historico/HANDOFF_BRANCH_014.md)

## Identidade

- Produto: **Blog CriaSys Web** (plataforma multi-blog Laravel)
- Pasta local (alvo): `C:\laragon\www\NEWS-PROJECTS\BlogCriaSysWeb`
- Pasta antiga: `BlogMiniViajante` (renomeação manual — Cursor bloqueia enquanto a pasta está aberta)
- `APP_NAME` no `.env` já é `BlogCriaSysWeb`
- Idioma com o usuário: **Português-BR**
- Stack: Laravel + Blade + Alpine/Vite + Fabric.js (Image Studio)

## Git agora

- Branch atual: **`014`** (checkout feito; limpa no último handoff)
- Último commit fechado: **`013`** → `9714b4c`  
  *Fecha a branch 013: Image Studio com rembg, Pacotes e Layouts separados.*
- Convenção: branches numeradas `001`…`014`; mensagem de fechamento: `Fecha a branch NNN: …`
- **Só commit quando o usuário pedir.** Não push a menos que peçam.

## Regra crítica: Layouts ≠ Pacotes

| Modal | O que é | Fonte | NÃO misturar |
|---|---|---|---|
| **Layouts** | Templates de redes (IG, YT, TikTok…) — ~32 | `config/image_studio.php` → `templates` | Não jogar packs aqui |
| **Pacotes** | Editorial, marca, **vendas R$**, mockups — ~57 | `config/image_studio_packs.php` | Não apagar layouts sociais |

`ImageStudioService::templateCatalog()` = só layouts.  
`ImageStudioService::packTemplateCatalog()` / `packsCatalog()` = só pacotes.  
**Nunca** fazer `union` dos dois no catálogo de Layouts (isso já quebrou a confiança do usuário).

## Image Studio (estado)

- Remoção de fundo: **rembg no servidor** (`BackgroundRemovalService`, `scripts/remove-background.py`)
- Driver: `IMAGE_STUDIO_BG_REMOVAL_DRIVER=rembg` (também `imgly` / `off`)
- Python Laragon tipico: `REMBG_PYTHON=C:/laragon/bin/python/python-3.10/python.exe`
- Deploy rembg: `docs/deploy/rembg-hostoo.md`
- Engine de template (JS): gradiente, line, ellipse, shadow, stroke — `resources/js/image-studio/imageStudio.js`
- Após mudar JS/CSS do studio: `npm run build` + Ctrl+F5 no browser
- Testes: `php artisan test --filter=ImageStudioTest`

## Pacotes (quantidades na 013)

- Editorial & web: 13
- Identidade & logo: 21 (inclui ação “Marca do meu blog”)
- Vendas & preços (R$): 14 — **categoria nova, usuário pediu e aprovou**
- Mockups: 9
- Usuário disse estar **satisfeito por hora** com Pacotes

## Preferências fortes do usuário

- Capricho visual 2026 no **canvas**, não só no modal
- Não misturar features (já explodiu ao misturar Layouts × Pacotes)
- Não deletar layouts de redes “porque pareciam feios”
- Respostas curtas em PT-BR; commits só sob pedido
- Frontend: seguir design system existente; evitar estética genérica “IA”

## O que a próxima IA deve fazer ao começar

1. Ler este arquivo + `docs/historico/HANDOFF_BRANCH_014.md`
2. Confirmar branch: `git branch --show-current` (esperado: `014`)
3. Confirmar pasta: se ainda for `BlogMiniViajante`, lembrar do bat  
   `C:\laragon\www\NEWS-PROJECTS\renomear-para-BlogCriaSysWeb.bat`
4. Perguntar o próximo objetivo — **não inventar refatoração** sem pedido
5. Nunca reverter layouts sociais nem fundir packs no modal Layouts
