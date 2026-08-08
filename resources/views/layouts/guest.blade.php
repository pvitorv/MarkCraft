<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>
        @php
            $authTitle = match (true) {
                request()->routeIs('login') => 'Entrar',
                request()->routeIs('register') => 'Criar conta',
                request()->routeIs('password.request') => 'Recuperar senha',
                request()->routeIs('password.reset') => 'Nova senha',
                request()->routeIs('verification.notice') => 'Confirmar e-mail',
                request()->routeIs('password.confirm') => 'Confirmar senha',
                default => 'Acesso',
            };
        @endphp
        {{ $authTitle }} — {{ config('app.name', 'MarkCraft') }}
    </title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=sora:600,700,800|dm-sans:400,500,600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --mc-auth-bg: #090c10;
            --mc-auth-panel: rgba(18, 24, 33, 0.88);
            --mc-auth-line: rgba(255, 255, 255, 0.1);
            --mc-auth-teal: #14b8a6;
            --mc-auth-teal-deep: #0d9488;
        }
        body.mc-auth-body {
            font-family: 'DM Sans', ui-sans-serif, system-ui, sans-serif;
            color: #e4e4e7;
            background:
                radial-gradient(900px 480px at 12% -8%, rgba(20, 184, 166, 0.22), transparent 55%),
                radial-gradient(700px 420px at 100% 8%, rgba(245, 158, 11, 0.1), transparent 50%),
                radial-gradient(600px 400px at 50% 100%, rgba(13, 148, 136, 0.08), transparent 45%),
                var(--mc-auth-bg);
            min-height: 100vh;
        }
        .mc-brand {
            font-family: 'Sora', ui-sans-serif, system-ui, sans-serif;
            letter-spacing: -0.03em;
        }
        .mc-auth-grain {
            pointer-events: none;
            position: fixed;
            inset: 0;
            opacity: 0.35;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.45'/%3E%3C/svg%3E");
            mix-blend-mode: soft-light;
        }
        .mc-auth-card {
            background: var(--mc-auth-panel);
            border: 1px solid var(--mc-auth-line);
            backdrop-filter: blur(16px);
            box-shadow: 0 24px 64px rgba(0, 0, 0, 0.45);
        }
        .mc-auth-input {
            width: 100%;
            border-radius: 0.65rem;
            border: 1px solid rgba(255, 255, 255, 0.12);
            background: rgba(9, 12, 16, 0.65);
            color: #f4f4f5;
            padding: 0.7rem 0.85rem;
            font-size: 0.95rem;
            outline: none;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        .mc-auth-input:focus {
            border-color: rgba(20, 184, 166, 0.7);
            box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.2);
        }
        .mc-auth-input::placeholder { color: #71717a; }
        .mc-auth-label {
            display: block;
            margin-bottom: 0.35rem;
            font-size: 0.8rem;
            font-weight: 500;
            color: #a1a1aa;
        }
        .mc-auth-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            border-radius: 0.65rem;
            background: linear-gradient(180deg, #2dd4bf 0%, #0d9488 100%);
            color: #042f2e;
            font-weight: 700;
            font-size: 0.95rem;
            padding: 0.75rem 1rem;
            border: none;
            cursor: pointer;
            transition: transform 0.15s ease, filter 0.15s ease;
        }
        .mc-auth-btn:hover {
            filter: brightness(1.06);
            transform: translateY(-1px);
        }
        .mc-auth-link {
            color: #5eead4;
            text-decoration: none;
            font-size: 0.875rem;
        }
        .mc-auth-link:hover { color: #99f6e4; text-decoration: underline; }
        .mc-auth-rise {
            animation: mcAuthRise 0.7s ease-out both;
        }
        @keyframes mcAuthRise {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="mc-auth-body antialiased">
    <div class="mc-auth-grain" aria-hidden="true"></div>

    <div class="relative z-10 flex min-h-screen flex-col px-4 py-8 sm:px-6">
        <header class="mx-auto flex w-full max-w-md items-center justify-between gap-3">
            <a href="{{ \App\Support\MarkCraftShell::isDesktop() ? route('login') : url('/') }}" class="mc-brand text-2xl font-extrabold text-white">
                MarkCraft
                <span class="ms-1.5 align-middle text-[9px] font-semibold tracking-[0.14em] uppercase text-teal-300/90 border border-teal-500/30 px-1.5 py-0.5">CriaSys</span>
            </a>
            @auth
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-zinc-400 hover:text-rose-300 transition">Sair</button>
                </form>
            @endauth
        </header>

        <main class="mx-auto flex w-full max-w-md flex-1 flex-col justify-center py-10">
            <div class="mc-auth-card mc-auth-rise rounded-2xl px-6 py-7 sm:px-8 sm:py-8">
                {{ $slot }}
            </div>
            <p class="mt-6 text-center text-[11px] text-zinc-500">
                Studio gratuito da família CriaSys
            </p>
        </main>
    </div>
</body>
</html>
