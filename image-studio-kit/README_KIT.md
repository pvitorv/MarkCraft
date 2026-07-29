# Image Studio Kit — espelho BlogCriaSysWeb

**Origem atual:** Blog CriaSys Web · branch **`014`** (código da `013` + memória)  
**Kit legado:** CriaSys Editor `040` (handoffs `017`–`027` ainda em `source/handoffs/`)  
**Tipo:** cópia segura — **não** remove nada do projeto original  
**Uso:** levar para um **segundo projeto** e pedir à IA para integrar / migrar rembg

## Leia nesta ordem

1. **`HISTORICO_ATUALIZACOES.md`** — o que mudou (Layouts/Pacotes, engine, rembg)
2. **`GUIA_REMBG_SUBSTITUIR_IMGLY.md`** — como substituir IMG.LY por rembg no outro projeto
3. **`INVENTARIO.md`** — lista de arquivos em `source/`
4. **`DEPENDENCIAS.md`** — npm / PHP / Python e acoplamentos
5. **`PROMPT_NOVO_PROJETO.md`** — visão MarkCraft (produto irmão; adaptar se o destino for outro)

## Conteúdo

| Caminho | Descrição |
|---------|-----------|
| `source/` | Espelho dos arquivos do Studio (paths do BlogCriaSysWeb) |
| `snippets/` | Rotas, env de exemplo, notas de acoplamento |
| `HISTORICO_ATUALIZACOES.md` | Histórico da sessão Image Studio no blog |
| `GUIA_REMBG_SUBSTITUIR_IMGLY.md` | Migração remoção de fundo |

## Como usar noutro repositório

1. Copie a pasta `image-studio-kit/` para o segundo projeto.  
2. Diga à IA:  
   *“Leia `image-studio-kit/HISTORICO_ATUALIZACOES.md` e `GUIA_REMBG_SUBSTITUIR_IMGLY.md`. Atualize o Image Studio deste projeto com o código em `source/` e migre a remoção de fundo de IMG.LY para rembg como no kit.”*  
3. Adapte namespaces, rotas e auth ao app destino (ver `DEPENDENCIAS.md`).  
4. **Nunca** misture catálogo de **Layouts** com **Pacotes**.  
5. Depois de integrar, pode apagar o kit no destino — o BlogCriaSysWeb continua intacto.

## Regras críticas

- Layouts (`config/image_studio.php` → `templates`) ≠ Pacotes (`config/image_studio_packs.php`)
- Remoção de fundo padrão = **rembg** (servidor), não IMG.LY
- Capricho no **canvas**, não só no modal
- Não versionar `.env` / secrets

## Checkpoint origem

- Branch: `014`
- Commit fechamento Studio: `9714b4c` (branch `013`)
- Handoff: `source/handoffs/HANDOFF_BRANCH_014.md`
