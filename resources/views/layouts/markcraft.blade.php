<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name')) — família CriaSys</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=sora:500,700,800|dm-sans:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
    <style>
        :root {
            --mc-ink: #0f1419;
            --mc-paper: #f3f0ea;
            --mc-accent: #0d9488;
            --mc-accent-deep: #0f766e;
            --mc-sand: #e8e2d6;
            --mc-night: #111827;
        }
        body.mc-body {
            font-family: 'DM Sans', ui-sans-serif, system-ui, sans-serif;
            background:
                radial-gradient(1200px 600px at 10% -10%, rgba(13, 148, 136, 0.18), transparent 55%),
                radial-gradient(900px 500px at 100% 0%, rgba(245, 158, 11, 0.12), transparent 50%),
                linear-gradient(180deg, #f7f4ee 0%, #ebe6dc 100%);
            color: var(--mc-ink);
        }
        .mc-brand {
            font-family: 'Sora', ui-sans-serif, system-ui, sans-serif;
            letter-spacing: -0.03em;
        }
        .mc-hero-motion {
            animation: mcRise 0.9s ease-out both;
        }
        .mc-hero-motion-delay {
            animation: mcRise 1s 0.12s ease-out both;
        }
        .mc-cta-pulse:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 30px rgba(13, 148, 136, 0.28);
        }
        @keyframes mcRise {
            from { opacity: 0; transform: translateY(14px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="mc-body antialiased min-h-screen" x-data="markCraftHub">
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.getRegistrations().then((regs) => {
                regs.forEach((reg) => reg.unregister());
            }).catch(() => {});
        }
    </script>
    <style>[x-cloak]{display:none!important}</style>
    <header class="relative z-50 border-b border-zinc-900/10 bg-white/50 backdrop-blur-md">
        <div class="mx-auto max-w-6xl px-4 py-4 flex items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="mc-brand text-2xl font-extrabold text-zinc-900">
                MarkCraft
                <span class="ms-2 align-middle text-[10px] font-semibold tracking-wide uppercase text-teal-700 bg-teal-50 border border-teal-200 px-2 py-0.5">CriaSys</span>
            </a>
            <nav class="flex flex-wrap items-center gap-3 text-sm font-medium text-zinc-700">
                @auth
                    <a href="{{ route('studio') }}" class="hover:text-teal-800">Studio</a>
                    <button type="button" @click="openHub('ferramentas')" class="hover:text-teal-800">Ferramentas</button>
                    <button type="button" @click="openHub('packs')" class="hover:text-teal-800">Packs</button>
                @else
                    <a href="{{ route('login') }}" class="hover:text-teal-800">Entrar</a>
                    <a href="{{ route('register') }}" class="rounded-md bg-teal-700 px-3 py-1.5 text-white hover:bg-teal-800 mc-cta-pulse transition">Começar grátis</a>
                @endauth
                <button type="button" @click="openHub('apoiar')" class="hover:text-teal-800">Apoiar</button>
            </nav>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="mt-16 border-t border-zinc-900/10 bg-zinc-900 text-zinc-300">
        <div class="mx-auto max-w-6xl px-4 py-8 text-sm">
            <p class="mc-brand text-lg text-white">MarkCraft</p>
            <p class="mt-2 max-w-2xl">Feito com carinho na família CriaSys · Contribuições opcionais mantêm o projeto vivo</p>
            <div class="mt-4 flex flex-wrap gap-4 text-zinc-400">
                <button type="button" @click="openHub('apoiar')" class="hover:text-white">Apoiar a partir de R$ 2</button>
                <span>Formatos: PSD · PNG · JPG · WebP · SVG · PDF — não abre Canva/Corel/Affinity nativos</span>
            </div>
        </div>
    </footer>

    @include('partials.hub_glass_modals')
</body>
</html>
