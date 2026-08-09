<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name')) — família CriaSys</title>
    @include('partials.head_favicon')
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
    <header class="sticky top-0 z-50 border-b border-zinc-900/10 bg-white/70 backdrop-blur-md">
        <div class="mx-auto max-w-6xl px-4 py-3.5 flex items-center justify-between gap-3">
            <a href="{{ route('home') }}" class="mc-brand text-xl sm:text-2xl font-extrabold text-zinc-900 shrink-0">
                MarkCraft
                <span class="ms-1.5 sm:ms-2 align-middle text-[9px] sm:text-[10px] font-semibold tracking-wide uppercase text-teal-700 bg-teal-50 border border-teal-200 px-1.5 sm:px-2 py-0.5">CriaSys</span>
            </a>

            <nav class="hidden lg:flex flex-wrap items-center justify-end gap-3 text-sm font-medium text-zinc-700">
                @auth
                    <a href="{{ route('profile.edit') }}" class="hover:text-teal-800" title="{{ Auth::user()->email }}">{{ Auth::user()->name }}</a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="hover:text-teal-800">Desconectar</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="hover:text-teal-800">Entrar</a>
                    <a href="{{ route('register') }}" class="rounded-md bg-teal-700 px-3 py-1.5 text-white hover:bg-teal-800 mc-cta-pulse transition">Começar grátis</a>
                @endauth
                <a href="{{ route('home') }}#ferramentas" class="hover:text-teal-800">Ferramentas</a>
                <button type="button" @click="openHub('packs')" class="inline-flex items-center gap-1.5 hover:text-teal-800" title="Packs">
                    <span class="text-amber-600">@include('partials.tool_icon', ['icon' => 'packs', 'size' => 16])</span>
                    Packs
                </button>
                <button type="button" @click="openHub('apoiar')" class="inline-flex items-center gap-1.5 hover:text-teal-800" title="Apoiar">
                    <span class="text-rose-600">@include('partials.tool_icon', ['icon' => 'heart', 'size' => 16])</span>
                    Apoiar
                </button>
                <a href="{{ auth()->check() ? route('studio') : route('login') }}"
                   class="inline-flex items-center gap-1.5 rounded-md border border-emerald-500 px-3 py-1.5 font-semibold text-emerald-600 shadow-[0_0_10px_rgba(16,185,129,0.45)] hover:bg-emerald-50 transition">
                    Studio
                </a>
            </nav>

            <div class="flex lg:hidden items-center gap-2">
                <a href="{{ auth()->check() ? route('studio') : route('login') }}"
                   class="inline-flex items-center gap-1 rounded-md border border-emerald-500 px-2.5 py-1 text-xs font-semibold text-emerald-600 shadow-[0_0_8px_rgba(16,185,129,0.35)]">
                    Studio
                </a>
                <button
                    type="button"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-md border border-zinc-300 bg-white text-zinc-800"
                    :class="{ 'border-teal-500 bg-teal-50 text-teal-800': navOpen }"
                    @click="toggleNav()"
                    :aria-expanded="navOpen.toString()"
                    aria-controls="mc-light-mobile-nav"
                    aria-label="Abrir menu"
                >
                    <svg x-show="!navOpen" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/>
                    </svg>
                    <svg x-show="navOpen" x-cloak class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/>
                    </svg>
                </button>
            </div>
        </div>

        <div
            id="mc-light-mobile-nav"
            x-show="navOpen"
            x-cloak
            class="lg:hidden border-t border-zinc-900/10 bg-white/95"
        >
            <div class="mx-auto max-w-6xl px-4 py-4 space-y-2 text-sm font-medium text-zinc-700 max-h-[min(78vh,640px)] overflow-y-auto">
                @auth
                    <div class="rounded-xl border border-zinc-200 bg-zinc-50 px-3.5 py-3 mb-3">
                        <p class="font-semibold text-zinc-900 truncate">{{ Auth::user()->name }}</p>
                        <p class="mt-0.5 text-xs text-zinc-500 truncate">{{ Auth::user()->email }}</p>
                    </div>
                    <a href="{{ route('profile.edit') }}" class="block rounded-lg px-3 py-2.5 hover:bg-teal-50" @click="closeNav()">Perfil</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left rounded-lg px-3 py-2.5 text-rose-700 hover:bg-rose-50">Desconectar</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="block rounded-lg px-3 py-2.5 hover:bg-teal-50" @click="closeNav()">Entrar</a>
                    <a href="{{ route('register') }}" class="block rounded-lg bg-teal-700 px-3 py-2.5 text-center text-white" @click="closeNav()">Começar grátis</a>
                @endauth
                <a href="{{ route('home') }}#ferramentas" class="block rounded-lg px-3 py-2.5 hover:bg-teal-50" @click="closeNav()">Ferramentas</a>
                <button type="button" @click="openHub('packs')" class="w-full text-left rounded-lg px-3 py-2.5 hover:bg-amber-50">Packs</button>
                <button type="button" @click="openHub('apoiar')" class="w-full text-left rounded-lg px-3 py-2.5 hover:bg-rose-50">Apoiar</button>
                <a href="{{ auth()->check() ? route('studio') : route('login') }}" class="block rounded-lg border border-emerald-500 px-3 py-2.5 text-center font-semibold text-emerald-700" @click="closeNav()">Abrir Studio</a>
            </div>
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
