# Segurança

O repositório **MarkCraft é público** (GNU AGPL-3.0). Isso é intencional (fonte correspondente da remoção de fundo IMG.LY e do nosso código). Não significa que o servidor de produção, o banco ou o SSH estejam abertos.

## O que nunca entra no Git

| Pode | Não pode |
|------|----------|
| `.env.example`, `.env.hostoo.example` | `.env`, senhas, `APP_KEY` real |
| `docs/deploy/ACESSOS-LOCAL.example.md` | `docs/ACESSOS-LOCAL.md` |
| Placeholders `usuario@host` e `PORTA` | Usuário SSH, host, porta, chaves |
| `scripts/hostoo.env.example` | `scripts/hostoo.env` |
| IDs de anúncio de exemplo | Tokens GA/GTM/Clarity de produção, se forem secretos para o seu caso |

`docs/HISTORICO_PROGRESSO.md` e `desktop/` também são locais (`.gitignore`).

## Deploy

Credenciais SSH ficam só na sua máquina: copie `scripts/hostoo.env.example` → `scripts/hostoo.env` e preencha. Os scripts **não** têm host/usuário/porta padrão.

## Relatar falha

Abra um [aviso de vulnerabilidade privado](https://github.com/pvitorv/MarkCraft/security/advisories/new) no GitHub, ou escreva para o contato em `/legal/privacidade`. Não abra issue pública com exploit.

Não oferecemos recompensa formal. Corrigimos o que for razoável no produto gratuito.

## Produção (lembrete)

- `APP_DEBUG=false`
- `.env` fora de `public_html`
- Remoção de fundo padrão: `IMAGE_STUDIO_BG_REMOVAL_DRIVER=imgly` (navegador)
- Não deixe `info.php` nem backups `.sql` no document root
