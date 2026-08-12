<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="dark">
    <meta name="supported-color-schemes" content="dark">
    <title>Recuperar senha — {{ $appName }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=sora:600,700,800|dm-sans:400,500,600&display=swap" rel="stylesheet">
    <!--[if mso]>
    <style type="text/css">
        body, table, td { font-family: Arial, Helvetica, sans-serif !important; }
    </style>
    <![endif]-->
</head>
<body style="margin:0;padding:0;background-color:#090c10;color:#e4e4e7;">
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;">
        Link para redefinir sua senha no {{ $appName }}. Expira em {{ $expire }} minutos.
    </div>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#090c10;background-image:radial-gradient(900px 480px at 12% -8%, rgba(20,184,166,0.22), transparent 55%), radial-gradient(700px 420px at 100% 8%, rgba(245,158,11,0.1), transparent 50%);">
        <tr>
            <td align="center" style="padding:32px 16px;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:520px;width:100%;">
                    {{-- Brand --}}
                    <tr>
                        <td align="center" style="padding:0 0 24px;">
                            <a href="{{ $appUrl }}" style="text-decoration:none;display:inline-block;">
                                <img
                                    src="{{ $logoUrl }}"
                                    alt="{{ $appName }}"
                                    width="56"
                                    height="56"
                                    style="display:block;border:0;border-radius:14px;margin:0 auto 12px;"
                                >
                                <span style="font-family:'Sora',Segoe UI,Helvetica,Arial,sans-serif;font-size:22px;font-weight:800;letter-spacing:-0.03em;color:#ffffff;line-height:1.2;">
                                    {{ $appName }}
                                </span>
                            </a>
                            <div style="margin-top:8px;">
                                <span style="font-family:'DM Sans',Segoe UI,Helvetica,Arial,sans-serif;font-size:10px;font-weight:600;letter-spacing:0.14em;text-transform:uppercase;color:#5eead4;border:1px solid rgba(45,212,191,0.35);padding:3px 8px;border-radius:4px;">
                                    CriaSys
                                </span>
                            </div>
                        </td>
                    </tr>

                    {{-- Card --}}
                    <tr>
                        <td style="background-color:#121821;border:1px solid rgba(255,255,255,0.1);border-radius:16px;padding:28px 28px 24px;">
                            <p style="margin:0 0 6px;font-family:'DM Sans',Segoe UI,Helvetica,Arial,sans-serif;font-size:10px;font-weight:600;letter-spacing:0.16em;text-transform:uppercase;color:#2dd4bf;">
                                Recuperação
                            </p>
                            <h1 style="margin:0 0 12px;font-family:'Sora',Segoe UI,Helvetica,Arial,sans-serif;font-size:24px;font-weight:700;letter-spacing:-0.03em;color:#ffffff;line-height:1.25;">
                                Redefinir sua senha
                            </h1>
                            <p style="margin:0 0 18px;font-family:'DM Sans',Segoe UI,Helvetica,Arial,sans-serif;font-size:15px;line-height:1.55;color:#a1a1aa;">
                                Olá, <strong style="color:#e4e4e7;font-weight:600;">{{ $userName }}</strong>.
                                Recebemos um pedido para redefinir a senha da sua conta no {{ $appName }}.
                            </p>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin:8px 0 20px;">
                                <tr>
                                    <td align="center">
                                        <a
                                            href="{{ $url }}"
                                            style="display:inline-block;width:100%;max-width:280px;box-sizing:border-box;text-align:center;font-family:'DM Sans',Segoe UI,Helvetica,Arial,sans-serif;font-size:15px;font-weight:700;color:#042f2e;text-decoration:none;padding:14px 22px;border-radius:10px;background:linear-gradient(180deg,#2dd4bf 0%,#0d9488 100%);"
                                        >
                                            Criar nova senha
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 12px;font-family:'DM Sans',Segoe UI,Helvetica,Arial,sans-serif;font-size:13px;line-height:1.5;color:#71717a;">
                                Este link expira em <strong style="color:#a1a1aa;">{{ $expire }} minutos</strong>.
                                Se você não pediu a recuperação, ignore este e-mail — sua senha permanece a mesma.
                            </p>

                            <p style="margin:0;font-family:'DM Sans',Segoe UI,Helvetica,Arial,sans-serif;font-size:12px;line-height:1.5;color:#52525b;word-break:break-all;">
                                Se o botão não funcionar, copie e cole no navegador:<br>
                                <a href="{{ $url }}" style="color:#5eead4;text-decoration:underline;">{{ $url }}</a>
                            </p>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td align="center" style="padding:22px 8px 0;">
                            <p style="margin:0 0 6px;font-family:'DM Sans',Segoe UI,Helvetica,Arial,sans-serif;font-size:12px;color:#71717a;">
                                Studio gratuito da família <span style="color:#5eead4;">CriaSys</span>
                            </p>
                            <p style="margin:0;font-family:'DM Sans',Segoe UI,Helvetica,Arial,sans-serif;font-size:11px;color:#3f3f46;">
                                <a href="{{ $appUrl }}" style="color:#71717a;text-decoration:none;">{{ $appUrl }}</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
