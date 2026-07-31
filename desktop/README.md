# MarkCraft Desktop — instalador com atalho

App **local** (Electron). Instalador Windows cria:
- atalho na **Área de trabalho**
- atalho no **Menu Iniciar**
- desinstalador

Não conecta a MarkCraft online. Usa o Laravel em `C:\laragon\www\MarkCraft`.

---

## Gerar o instalador (uma vez)

Na pasta do projeto:

```bash
cd /c/laragon/www/MarkCraft/desktop
npm install
npm run dist
```

Saída:

`desktop/dist/MarkCraft-Desktop-Setup-1.0.0.exe`

Instale esse `.exe` (próximo → escolher pasta → criar atalhos).

Também dá para gerar portátil:

```bash
npm run dist:portable
```

---

## Uso diário

1. Preferível: **Laragon → Start All** (MySQL + PHP)
2. Clique no atalho **MarkCraft Desktop**
3. O app tenta abrir `http://127.0.0.1:8000`
4. Se o servidor estiver parado, tenta subir `php artisan serve` sozinho

No `.env` do MarkCraft:

```
MARKCRAFT_SHELL=desktop
APP_URL=http://127.0.0.1:8000
```

---

## Variáveis opcionais (antes de abrir o atalho)

| Variável | Padrão |
|----------|--------|
| `MARKCRAFT_DESKTOP_URL` | `http://127.0.0.1:8000` |
| `MARKCRAFT_ROOT` | `C:\laragon\www\MarkCraft` |
| `MARKCRAFT_EXPORTS_DIR` | `%USERPROFILE%\MarkCraftExports` |
| `PHP_BIN` | auto (Laragon) |

Exports (com Electron):

```
MarkCraftExports/
  Instagram/
  Facebook/
  LinkedIn/
  Blog/
  Apresentacao/
  Outros/
```

---

## Dev sem instalador

```bash
cd /c/laragon/www/MarkCraft/desktop
npm start
```
