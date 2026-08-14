# Créditos e atribuições — MarkCraft

Documento de compliance para bibliotecas e ativos usados no MarkCraft / Image Studio.  
Atualizado em 14/08/2026.

## Licença do MarkCraft

O código próprio do MarkCraft é **GNU AGPL-3.0-or-later**. Fonte: [github.com/pvitorv/MarkCraft](https://github.com/pvitorv/MarkCraft).  
Frameworks e libs de terceiros permanecem nas licenças originais (não reliceiamos Laravel, Fabric, etc.).

## Ícones e elementos

| Ativo | Uso | Licença | Atribuição |
|-------|-----|---------|------------|
| [Bootstrap Icons](https://icons.getbootstrap.com/) | SVG no modal Elementos (`public/icons/bootstrap/`) | MIT | Recomendada |
| [Font Awesome Free](https://fontawesome.com/) | Glyphs / CDN no Studio | **CC BY 4.0** | **Obrigatória** — citar “Font Awesome” |
| [Material Symbols](https://fonts.google.com/icons) | Glyphs de ícones | Apache 2.0 | Recomendada |
| Emojis Unicode | Stickers/reações | Unicode | Renderização depende do sistema operacional |
| Formas e stickers geométricos | Catálogo MarkCraft / CriaSys | © MarkCraft (CriaSys) | Conteúdo do produto |

## Tipografia

- **Google Fonts** — famílias do catálogo em `config/image_studio_fonts.php` (OFL / Apache 2.0 conforme a família). Ver [Google Fonts](https://fonts.google.com/).
- **Fontes de sistema (Windows)** — quando listadas no catálogo; licença do SO do usuário.

## Tecnologias

| Tecnologia | Licença / nota |
|------------|----------------|
| [Fabric.js](https://fabricjs.com/) | MIT |
| [Alpine.js](https://alpinejs.dev/) | MIT |
| Laravel / Vite | MIT (framework e toolchain) |
| [@imgly/background-removal](https://github.com/imgly/background-removal-js) | **AGPL-3.0** — driver **padrão** (`IMAGE_STUDIO_BG_REMOVAL_DRIVER=imgly`); roda no navegador |
| rembg (Python) | Open source; motor **legado/opcional** (`driver=rembg`) |

## Marcas de terceiros

Logotipos e nomes de redes sociais (Instagram, YouTube, etc.) pertencem aos respectivos titulares. No MarkCraft são usados apenas de forma **indicativa** (formatos, ícones de identificação), sem endosso.

## Conteúdo do usuário

Quem cria, envia ou publica artes no MarkCraft é responsável pelo material. Por padrão as artes **não ficam armazenadas no servidor** — baixe e limpe o workspace.

## Contato / produto

MarkCraft — família [CriaSys](https://criasysweb.com.br).  
UI de créditos: botão **Créditos** no Studio e no rodapé da home.
