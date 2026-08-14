# Código-fonte, licença e remoção de fundo

Documento para humanos e para o próximo agente. O MarkCraft é um **portal de ferramentas gratuitas** (ganho em publicidade). A ferramenta **não** é vendida como SaaS.

## Repositório

- **Público:** https://github.com/pvitorv/MarkCraft
- **Licença do código próprio:** GNU AGPL-3.0-or-later (`LICENSE` na raiz, campo em `composer.json`)
- **`package.json` `"private": true`:** impede publicar o app no npm; **não** é visibilidade do GitHub

Laravel, Fabric.js, Alpine, Vite e o restante das dependências **continuam nas licenças originais**. Não reliceiamos o Laravel. O Blog CriaSys Web (outro produto) **não** herda AGPL só porque o MarkCraft é AGPL.

## Por que AGPL

O motor de remoção de fundo padrão é [`@imgly/background-removal`](https://github.com/imgly/background-removal-js) (AGPL-3.0), rodando **no navegador**. Usar essa biblioteca num produto fechado exigiria licença comercial da IMG.LY. Com o GitHub público + AGPL no nosso código, o uso OSS fica alinhado aos termos da lib.

Anúncios e doações **são permitidos** pela AGPL. O que a AGPL pede é **fonte correspondente** para quem usa o programa em rede — daí o link “Código-fonte” no rodapé e nos créditos.

## Remoção de fundo

| Driver | Onde roda | Quando usar |
|--------|-----------|-------------|
| `imgly` (**padrão**) | Navegador (WASM). 1ª vez baixa o modelo. | Produção e desktop |
| `rembg` | Python no servidor | Só se precisar do motor legado |
| `off` | — | Desliga o recurso |

Variável: `IMAGE_STUDIO_BG_REMOVAL_DRIVER` (`.env.example` / `.env.hostoo.example`).

O código PHP `BackgroundRemovalService` + `scripts/remove-background.py` permanece como **fallback**. Produção Hostoo **não** precisa de Miniconda se o driver for `imgly`.

## Obrigação prática (AGPL)

Quem modifica e publica o MarkCraft na internet deve oferecer o código correspondente (fork no GitHub ou equivalente). Créditos da IMG.LY e das demais libs: `docs/CREDITS.md` e o modal Créditos no site.

## Segurança do repo público

Ver **`SECURITY.md`**. Nunca versionar SSH, `.env` real ou `docs/ACESSOS-LOCAL.md`.
