# Handoff — Branch 014 (pós Image Studio 013)

**Data:** 2026-07-28  
**Para:** próxima IA no Cursor após reabrir o projeto (idealmente pasta `BlogCriaSysWeb`)  
**Autor da sessão:** agente Composer (Cursor) na conversa de Image Studio / Pacotes / rembg

---

## 1. Contexto do produto

Plataforma **Blog CriaSys Web**: blogs com painel, afiliados, billing Mercado Pago, landing pages, hub de Ferramentas e **Image Studio** (editor canvas Fabric.js).

Ambiente local: **Laragon** em Windows (`C:\laragon\www\NEWS-PROJECTS\…`).

### Renomeação da pasta

- Nome antigo: `BlogMiniViajante`
- Nome desejado: `BlogCriaSysWeb`
- **Não foi possível renomear com o Cursor aberto** (Windows: pasta em uso)
- Script pronto: `C:\laragon\www\NEWS-PROJECTS\renomear-para-BlogCriaSysWeb.bat`
  1. Fechar Cursor nesta pasta
  2. Rodar o `.bat`
  3. Abrir `C:\laragon\www\NEWS-PROJECTS\BlogCriaSysWeb` no Cursor
- Git **não perde** commits/branches com o rename
- Vhost Laragon `NEWS-PROJECTS.test` aponta para a pasta pai `NEWS-PROJECTS`, não para o nome do projeto
- `.env`: `APP_NAME=BlogCriaSysWeb`; `REMBG_PYTHON` aponta para o Python do Laragon (não depende do nome da pasta)

> Chats do Cursor podem ficar ligados ao path antigo; por isso este handoff existe.

---

## 2. Estado Git

| Item | Valor |
|---|---|
| Branch ativa | `014` |
| Commit que fechou a 013 | `9714b4c` |
| Mensagem | `Fecha a branch 013: Image Studio com rembg, Pacotes e Layouts separados.` |
| Working tree no handoff | limpa |
| Branches anteriores | `001`…`013` (padrão do projeto), `main` = baseline |

**Política:** só `git commit` / `push` se o usuário pedir. Mensagens de fechamento seguem `Fecha a branch NNN: …`.

---

## 3. O que a branch 013 entregou

### 3.1 Remoção de fundo (rembg)

- Saiu da dependência AGPL `@imgly/background-removal` no browser (ainda há driver `imgly` opcional)
- Servidor: Python **rembg** via
  - `app/Services/ImageStudio/BackgroundRemovalService.php`
  - `scripts/remove-background.py`
  - Rota painel studio remover-fundo
- Config: `IMAGE_STUDIO_BG_REMOVAL_DRIVER` = `rembg` \| `imgly` \| `off`
- Docs deploy Hostoo: `docs/deploy/rembg-hostoo.md`
- Bugs já corrigidos nesta linha: seleção Fabric perdida no clique; cache Laravel com `false` preso; Windows Apache `_Py_HashRandomization_Init` → env com `SystemRoot` + `PYTHONHASHSEED=0`

### 3.2 Pacotes vs Layouts (NÃO MISTURAR)

**Erro grave já cometido e corrigido:** alguém esvaziou `config/image_studio.templates` e mergeou packs no catálogo de Layouts. O usuário perdeu os layouts de redes e ficou furioso. **Não repetir.**

| Superfície UI | Catálogo | Arquivo |
|---|---|---|
| Botão/modal **Layouts** | redes sociais / formatos (~32) | `config/image_studio.php` → `templates` |
| Botão/modal **Pacotes** | editorial, marca, vendas, mockups (~57) | `config/image_studio_packs.php` |

Serviço:

- `ImageStudioService::templateCatalog()` → **somente** `image_studio.templates`
- `ImageStudioService::packTemplateCatalog()` → **somente** packs
- `packsCatalog()` resolve itens a partir do catálogo de packs
- Teste garante: `story_quote` ∈ layouts; `pack_blog_cover_ink` ∉ layouts; `pack_blog_cover_ink` ∈ pack `web_blog`; categoria `sales` existe

### 3.3 Qualidade dos templates de Pacotes

- Usuário reclamou que o modal ficou bonito e o **canvas** não
- Engine JS passou a aceitar: `rect/circle/ellipse/line` + `gradient` + `stroke` + `shadow` + tipografia Playfair / Space Grotesk / DM Sans
- Templates de packs reescritos com composição densa
- Depois: usuário pediu **mais quantidade** + categoria **Vendas & preços (R$)** + mais logos/identidade → feito; disse **satisfeito por hora**

Contagens no fechamento da 013:

- Editorial & web: 13  
- Identidade & logo: 21 (com kit da marca do blog)  
- Vendas & preços: 14  
- Mockups: 9  
- **Total itens em Pacotes ≈ 57**  
- Layouts de redes: **32** (restaurados do commit `be39987` / branch 012)

---

## 4. Arquivos-chave

| Área | Paths |
|---|---|
| Packs | `config/image_studio_packs.php` |
| Layouts sociais | `config/image_studio.php` (`templates`) |
| Fontes | `config/image_studio_fonts.php` |
| Catálogo API | `app/Services/ImageStudio/ImageStudioService.php` |
| Remoção fundo | `app/Services/ImageStudio/BackgroundRemovalService.php`, `scripts/remove-background.py` |
| Controller | `app/Http/Controllers/Painel/ImageStudioController.php` |
| JS Studio | `resources/js/image-studio/imageStudio.js`, `app-studio.js` |
| CSS | `resources/css/studio.css` |
| Views | `resources/views/painel/studio/*` |
| Rotas | `routes/web.php` (painel studio) |
| Testes | `tests/Feature/ImageStudioTest.php` |
| Deploy rembg | `docs/deploy/rembg-hostoo.md` |

---

## 5. Como validar rápido

```bash
php artisan test --filter=ImageStudioTest
php artisan tinker --execute="echo count(app(\App\Services\ImageStudio\ImageStudioService::class)->catalog()['templates']);"
# Layouts devem ser ~32; packs 4 categorias; zero overlap de slugs pack_* nos layouts
npm run build   # se mudar JS/CSS do studio
```

No browser (Ctrl+F5):

1. **Layouts** → ver Instagram/YouTube/etc. (não pacotes)
2. **Pacotes** → chips Editorial / Identidade / Vendas / Mockups
3. Aplicar “Capa tinta” ou “Etiqueta de preço” e checar canvas, não só o tile

---

## 6. Preferências do usuário (aprendidas na dor)

1. Capricho no **resultado do canvas**, não só UI do modal  
2. **Separação rígida** de features (Layouts ≠ Pacotes ≠ Formatos)  
3. **Não apagar** o que já estava bom sem perguntar  
4. Pacotes: modernos, densos, em **quantidade**; vendas em **R$ brasileiro**  
5. Comunicação: PT-BR, direta, sem enrolação  
6. Commits só sob pedido; branches numeradas  

---

## 7. O que NÃO está pedido / não inventar

- Não refatorar afiliados/landing/billing sem pedido (há código relacionado no histórico, mas o foco desta sessão foi Image Studio)
- Não reintroduzir merge Layouts∪Pacotes
- Não “limpar” templates sociais chamando de 2002
- Não commitar `.env` / secrets
- Não forçar push

---

## 8. Possíveis próximos passos (só se o usuário pedir)

- Mais templates em Vendas ou Marca  
- Melhorar preview visual dos tiles (hoje é abstrato por cores)  
- Ajustes rembg em produção (Hostoo)  
- Continuar features da plataforma na branch `014`  
- Concluir rename da pasta se ainda for `BlogMiniViajante`

---

## 9. Checklist da próxima IA (primeiro minuto)

- [ ] Li `AGENTS.md` e este handoff  
- [ ] `git branch` = `014`  
- [ ] Confirmei se a pasta já é `BlogCriaSysWeb`  
- [ ] Perguntei o objetivo da sessão ao usuário  
- [ ] Sei que Layouts e Pacotes são mundos separados  

---

*Fim do handoff 014. Atualize este arquivo quando fechar a próxima fatia relevante de trabalho.*
