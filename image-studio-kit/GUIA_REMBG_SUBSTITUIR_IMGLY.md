# Guia — Substituir IMG.LY (`@imgly/background-removal`) por rembg

**Para:** próxima IA no **segundo projeto** (ou qualquer app que ainda remova fundo no browser com IMG.LY).  
**Referência de código:** pasta `source/` deste kit (espelho do BlogCriaSysWeb, branch `014`).  
**Deploy:** `source/docs/deploy/rembg-hostoo.md`

---

## 0. Objetivo

Trocar remoção de fundo **no cliente** (AGPL / modelo WASM) por remoção **no servidor** com **Python rembg** (open source), mantendo IMG.LY só como fallback opcional e **desligado por padrão**.

| | Antes | Depois (padrão) |
|--|-------|-----------------|
| Onde roda | Browser | PHP → Python |
| Pacote | `@imgly/background-removal` | `rembg` + `pillow` (+ onnxruntime) |
| Licença | AGPL (atenção!) | Open source rembg |
| Env | — | `IMAGE_STUDIO_BG_REMOVAL_DRIVER=rembg` |
| Rota | Não precisa | `POST …/remover-fundo` (multipart imagem → PNG) |

---

## 1. Arquivos a copiar / alinhar

Do kit (`source/` → root do projeto destino):

```
app/Services/ImageStudio/BackgroundRemovalService.php
scripts/remove-background.py
config/image_studio.php          # seção background_removal
docs/deploy/rembg-hostoo.md      # opcional mas recomendado
```

No front (já no `imageStudio.js` atual do kit):

- Engine: `bgRemovalDriver`, `bgRemovalUrl`
- `removeBackgroundFromBlob()` → se `rembg`, chama `removeBackgroundViaRembg()` (FormData + fetch)
- Só faz `import('@imgly/background-removal')` se driver === `imgly`

Controller (adaptar namespace/rota do destino):

- Método `removeBackground` que grava upload temp → `BackgroundRemovalService::remove()` → devolve PNG
- Throttle (ex.: `20,1`)

Meta / URL no HTML (exemplo BlogCriaSysWeb):

```html
<meta name="studio-remove-bg-url" content="{{ route('….studio.remover-fundo') }}">
```

---

## 2. Config Laravel

Em `config/image_studio.php` (ou equivalente):

```php
'background_removal' => [
    'driver' => env('IMAGE_STUDIO_BG_REMOVAL_DRIVER', 'rembg'), // rembg | imgly | off
    'python' => env('REMBG_PYTHON'),
],
```

No `.env` / `.env.example` do destino (**só nomes; sem secrets**):

```env
IMAGE_STUDIO_BG_REMOVAL_DRIVER=rembg
# REMBG_PYTHON=C:/laragon/bin/python/python-3.10/python.exe
# Linux: /home/USUARIO/apps/rembg-venv/bin/python
```

Catálogo / status da API deve expor algo como:

- `background_removal_driver`
- `background_removal_label`  
(via `BackgroundRemovalService::status()`)

---

## 3. Instalação local (Laragon / Windows)

```bash
# Python do Laragon (ajuste a versão)
C:/laragon/bin/python/python-3.10/python.exe -m pip install rembg pillow onnxruntime

# Teste rápido do script
C:/laragon/bin/python/python-3.10/python.exe scripts/remove-background.py --help
# ou: entrada.png saída.png (conforme CLI do script do kit)
```

No `.env` local:

```env
IMAGE_STUDIO_BG_REMOVAL_DRIVER=rembg
REMBG_PYTHON=C:/laragon/bin/python/python-3.10/python.exe
```

`php artisan config:clear`

---

## 4. Fluxo runtime (o que a IA deve implementar)

1. Usuário clica “Remover fundo” com imagem selecionada no Fabric.  
2. JS exporta blob da imagem.  
3. Se driver `rembg`: `POST` multipart para a rota do servidor + CSRF.  
4. PHP valida, grava temp, chama `BackgroundRemovalService::remove($in, $out)`.  
5. Service resolve binário Python (`REMBG_PYTHON` ou PATH), sobe processo com env seguro (`SystemRoot`, `PYTHONHASHSEED=0` no Windows).  
6. Script Python usa rembg → PNG com alpha.  
7. PHP devolve o arquivo; JS coloca a imagem de volta no canvas **sem perder seleção/UX**.

Se driver `imgly`: comportamento legado no browser (só se o dono pedir / tiver licença).  
Se `off`: botão desabilitado / mensagem clara.

---

## 5. Armadilhas (já vistas no BlogCriaSysWeb)

1. **Não** usar `Cache::remember` que persiste `false` por minutos quando rembg falha no probe — o botão fica morto. Preferir: “Python encontrado = available”; validar import no `remove()` ou com cache positivo só.  
2. **Windows + Apache**: processo Python precisa herdar `SystemRoot` (e `PYTHONHASHSEED=0` ajuda). Ver implementação em `BackgroundRemovalService`.  
3. **1ª remoção** baixa modelo U²-Net (~176 MB) em `~/.u2net` — pode demorar; avisar na UI.  
4. Hosting sem `proc_open` / sem Python → `DRIVER=off` em produção até ter SSH/venv.  
5. Não remover `@imgly` do `package.json` à força se o código ainda tiver import dinâmico — ou deixe o pacote e o branch `imgly` no JS; o importante é **não carregar** no default.  
6. Throttle + tamanho máximo de upload — remoção é CPU-pesada.

---

## 6. Checklist de migração no segundo projeto

- [ ] Copiar `BackgroundRemovalService.php` + `remove-background.py`
- [ ] Garantir seção `background_removal` no config
- [ ] Variáveis no `.env.example` (sem valores secretos)
- [ ] Rota POST autenticada + throttle + CSRF
- [ ] JS: driver `rembg` → fetch servidor; `imgly` só se explícito
- [ ] Meta/`bgRemovalUrl` apontando para a rota
- [ ] `pip install rembg pillow onnxruntime` no Python do ambiente
- [ ] `REMBG_PYTHON` apontando para esse interpretador
- [ ] Teste: upload foto → remover fundo → PNG com transparência no canvas
- [ ] Em produção: seguir `source/docs/deploy/rembg-hostoo.md` (venv + DRIVER)
- [ ] `php artisan config:clear` + hard refresh (Ctrl+F5) após build

---

## 7. Critério de pronto

- Com `DRIVER=rembg` e Python ok: botão funciona **sem** carregar chunk AGPL da IMG.LY no Network.  
- Com `DRIVER=off`: feature desligada sem quebrar o resto do studio.  
- Com `DRIVER=imgly`: só então o dynamic import da IMG.LY aparece (opcional).

---

## 8. Referência rápida de arquivos neste kit

| Peça | Path no kit |
|------|-------------|
| Service | `source/app/Services/ImageStudio/BackgroundRemovalService.php` |
| Script | `source/scripts/remove-background.py` |
| Config | `source/config/image_studio.php` |
| JS engine | `source/resources/js/image-studio/imageStudio.js` |
| App Alpine | `source/resources/js/image-studio/app-studio.js` |
| Controller | `source/app/Http/Controllers/Painel/ImageStudioController.php` |
| Deploy | `source/docs/deploy/rembg-hostoo.md` |
| Histórico | `HISTORICO_ATUALIZACOES.md` |
