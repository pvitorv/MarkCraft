# Deploy: remoção de fundo no servidor (legado / opcional)

**Produção atual do MarkCraft usa `IMAGE_STUDIO_BG_REMOVAL_DRIVER=imgly`** (`@imgly/background-removal` no navegador, licença AGPL; repo público).

Este documento permanece só se você quiser o motor **Python + rembg** no Hostoo. Não é necessário para o portal gratuito.

---

# Deploy: remoção de fundo (rembg) no servidor

Guia para instalar o motor **Python + rembg** em produção (ex.: **Hostoo Cloud PHP com SSH**).  
Serve para humanos e para colar em outra IA que vá te ajudar via SSH.

## O que é (contexto rápido)

| Peça | Quem instala | Onde roda |
|------|----------------|-----------|
| App Laravel (Image Studio, rota `POST /painel/studio/remover-fundo`) | Deploy normal do projeto (`git`, `composer`, `.env`) | PHP no servidor |
| Script `scripts/remove-background.py` | Já vem no repositório | Chamado pelo PHP |
| **Python 3 + rembg + pillow + onnxruntime** | **Você (ou IA) via SSH — uma vez** | Processo no servidor |
| Modelo U²-Net (~176 MB) | Baixa sozinho na 1ª remoção | Pasta home do usuário (`~/.u2net`) |

- **Não** roda no navegador (driver padrão `rembg`).
- **Não** gera cobrança da IMG.LY.
- Custo = CPU/RAM do hosting.
- Se der errado, o resto do blog continua; dá para desligar só a remoção.

Arquivos relevantes no projeto:

- `app/Services/ImageStudio/BackgroundRemovalService.php`
- `scripts/remove-background.py`
- `config/image_studio.php` → `background_removal`
- Variáveis: `IMAGE_STUDIO_BG_REMOVAL_DRIVER`, `REMBG_PYTHON`

---

## Requisitos do hosting

Confirme com o suporte (ou teste no SSH):

1. Acesso **SSH**
2. Pode instalar / usar **Python 3** e **pip** (ou venv do usuário)
3. PHP pode executar processos externos (`proc_open` / `exec` **não** desabilitados)
4. RAM recomendada: **≥ 2 GB** (ideal **4 GB+**). Planos com 512 MB–1 GB tendem a falhar ou ficar muito lentos
5. Disco livre para o modelo (~200 MB) + dependências

Se o suporte disser que Python ou `proc_open` não são permitidos: use `IMAGE_STUDIO_BG_REMOVAL_DRIVER=off` em produção e deixe rembg só no ambiente local (Laragon).

---

## Checklist — deploy do Laravel (sem Python ainda)

Isso é o deploy normal do app. O rembg **não** vem no `composer install`.

1. Subir o código (git/FTP/CI)
2. `composer install --no-dev --optimize-autoloader`
3. `npm ci && npm run build` (ou build local e enviar `public/build`)
4. Copiar `.env`, `php artisan key:generate` se necessário
5. Migrations, storage link, permissões
6. No `.env` de produção, por enquanto pode deixar:

```env
IMAGE_STUDIO_BG_REMOVAL_DRIVER=off
```

Assim o site sobe sem depender do Python. Ative o rembg só depois dos passos abaixo.

---

## Instalação do Python + rembg (SSH) — passo a passo

Substitua `SEU_USUARIO` e caminhos pelo que existir no Hostoo.  
Muitos planos colocam o site em algo como `/home/SEU_USUARIO/domains/seusite.com/public_html` ou similar — **ajuste**.

### 1) Entrar no SSH e ir para a home

```bash
cd ~
pwd
whoami
```

### 2) Ver se já existe Python

```bash
python3 --version
which python3
```

- Se mostrar `Python 3.9+` (ideal 3.10+): continue com venv abaixo.
- Se **só** existir Python 3.6 (comum na Hostoo): use **Miniconda** (passo 2b). Sem Python 3.9+, pare e use `DRIVER=off`.

### 2b) Miniconda (Hostoo — Python 3.6 no sistema)

Requer **≥ 2 GB RAM** (instalação pode derrubar SSH em planos menores).

```bash
cd ~
wget -q https://repo.anaconda.com/miniconda/Miniconda3-latest-Linux-x86_64.sh -O miniconda.sh
bash miniconda.sh -b -p ~/miniconda3
~/miniconda3/bin/python --version
~/miniconda3/bin/pip install rembg pillow onnxruntime
~/miniconda3/bin/python -c "import rembg; print('rembg OK')"
```

`.env`:

```env
REMBG_PYTHON=/home/SEU_USUARIO/miniconda3/bin/python
```

Pule para o passo 6 (teste do script). MarkCraft em produção (2026-08): ~30–40 s por remoção em 2 GB.

### 3) Criar pasta e ambiente virtual (recomendado se python3 ≥ 3.9)

```bash
mkdir -p ~/apps/rembg-venv-src
cd ~/apps
python3 -m venv rembg-venv
source ~/apps/rembg-venv/bin/activate
python -m pip install --upgrade pip
```

### 4) Instalar pacotes

```bash
pip install rembg pillow onnxruntime
```

Isso pode demorar alguns minutos. Se faltar memória, o processo pode ser morto (`Killed`) — aí precisa de plano com mais RAM ou pedir ao suporte.

### 5) Testar o import

```bash
python -c "import rembg; print('rembg OK', rembg.__version__)"
which python
```

Anote o caminho impresso por `which python`, por exemplo:

```text
/home/SEU_USUARIO/apps/rembg-venv/bin/python
```

### 6) Testar o script do projeto (opcional mas recomendado)

Ajuste `CAMINHO_DO_APP` para a raiz do Laravel (onde está `artisan`):

```bash
cd /CAMINHO_DO_APP
source ~/apps/rembg-venv/bin/activate

# PNG de teste mínimo
python - <<'PY'
from PIL import Image
Image.new("RGB", (64, 64), (255, 0, 0)).save("/tmp/rembg-in.png")
print("in ok")
PY

python scripts/remove-background.py /tmp/rembg-in.png /tmp/rembg-out.png
ls -la /tmp/rembg-out.png
```

Na **primeira** execução o rembg pode baixar o modelo `u2net.onnx` (~176 MB). Precisa de internet de saída no servidor.

### 7) Configurar o `.env` do Laravel

Na raiz do app:

```env
IMAGE_STUDIO_BG_REMOVAL_DRIVER=rembg
REMBG_PYTHON=/home/SEU_USUARIO/apps/rembg-venv/bin/python
```

Use o caminho **exato** do `which python` com o venv ativado (ou o path absoluto do binário do venv).

Depois:

```bash
cd /CAMINHO_DO_APP
php artisan config:clear
php artisan cache:clear
```

### 8) Teste no Image Studio

1. Login no painel → Image Studio  
2. Adicionar/selecionar uma imagem  
3. **Remover fundo da seleção**  
4. Esperar (1ª vez pode demorar por causa do modelo)

Se aparecer erro, veja:

```bash
tail -n 80 storage/logs/laravel.log
```

---

## Variáveis de ambiente (referência)

| Variável | Valores | Função |
|----------|---------|--------|
| `IMAGE_STUDIO_BG_REMOVAL_DRIVER` | `rembg` \| `imgly` \| `off` | Motor ativo |
| `REMBG_PYTHON` | caminho absoluto do `python` | Evita o stub errado / `python` do sistema |

Exemplos:

```env
# Produção (padrão)
IMAGE_STUDIO_BG_REMOVAL_DRIVER=imgly

# Legado: Python no servidor
# IMAGE_STUDIO_BG_REMOVAL_DRIVER=rembg
# REMBG_PYTHON=/home/SEU_USUARIO/apps/rembg-venv/bin/python

# Desligar só a remoção (app normal funciona)
IMAGE_STUDIO_BG_REMOVAL_DRIVER=off
```

Windows local (Laragon) — referência:

```env
IMAGE_STUDIO_BG_REMOVAL_DRIVER=rembg
REMBG_PYTHON=C:/laragon/bin/python/python-3.10/python.exe
```

---

## Problemas comuns

| Sintoma | Causa provável | O que fazer |
|---------|----------------|-------------|
| `rembg indisponível...` | `REMBG_PYTHON` errado ou Python inexistente | Conferir path; `php artisan config:clear` |
| `Fatal Python error: _Py_HashRandomization_Init` | Ambiente Apache sem `SystemRoot` (Windows) | Já tratado no `BackgroundRemovalService` no Windows; no Linux é raro |
| `Killed` no `pip install` / remoção | Sem RAM | Upgrade de plano ou `DRIVER=off` |
| Timeout / 500 na 1ª remoção | Download do modelo | Rodar o script uma vez no SSH; aumentar timeout PHP se preciso |
| `proc_open` / process failed | Função desabilitada no PHP | Pedir ao suporte Hostoo |
| Import ok no SSH, falha no painel | PHP usa outro usuário / path | `REMBG_PYTHON` absoluto; permissões de leitura no venv |

Desligar emergência:

```env
IMAGE_STUDIO_BG_REMOVAL_DRIVER=off
```

```bash
php artisan config:clear
```

---

## Prompt pronto para colar em outra IA (SSH)

Copie o bloco abaixo e envie junto com acesso/contexto do servidor:

```text
Preciso instalar o motor rembg para um app Laravel (Image Studio) neste servidor (Hostoo Cloud PHP com SSH).

Contexto do projeto:
- O Laravel JÁ tem o código: BackgroundRemovalService chama um Python com scripts/remove-background.py
- Composer/npm NÃO instalam o rembg — isso é só no servidor via SSH
- Variáveis .env: IMAGE_STUDIO_BG_REMOVAL_DRIVER=rembg e REMBG_PYTHON=/caminho/absoluto/do/python
- Driver off desliga só a remoção de fundo

Faça comigo, passo a passo, comandos seguros:
1) Descobrir se existe python3
2) Criar venv em ~/apps/rembg-venv
3) pip install rembg pillow onnxruntime
4) Testar: python -c "import rembg; print(rembg.__version__)"
5) Testar scripts/remove-background.py na raiz do Laravel (me peça o caminho do artisan se eu não souber)
6) Me dizer o caminho exato para REMBG_PYTHON
7) Orientar php artisan config:clear e cache:clear
8) Se faltar RAM/Python/proc_open, diga claramente e sugira IMAGE_STUDIO_BG_REMOVAL_DRIVER=imgly (padrão) ou off

Não altere código Laravel sem necessidade. Não faça comandos destrutivos (rm -rf /, formatar disco, etc.).
Se um comando falhar, mostre o erro e a alternativa — não invente que deu certo.
```

---

## O que NÃO é necessário

- Não precisa “aprender Python” para manter o app  
- Não precisa fork do rembg  
- Não precisa reinstalar a cada deploy do Laravel (só se apagar o venv ou mudar de servidor)  
- Não precisa IMG.LY para este fluxo  

---

## Depois que estiver ok

Anote aqui (para você):

```text
Data instalação: ____/____/________
Host: Hostoo / outro: ________
Path REMBG_PYTHON: ________
Python versão: ________
rembg versão: ________
Teste Studio OK? ( ) sim  ( ) não
```

Documento do projeto: `docs/deploy/rembg-hostoo.md`
