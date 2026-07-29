<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MarkCraft — crie posts grátis · família CriaSys</title>
    <meta name="description" content="Monte posts e artes para redes no navegador. Layouts prontos, elementos, remoção de fundo. Cadastre-se grátis e abra o Studio.">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=sora:500,700,800|dm-sans:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --mc-bg: #07090c;
            --mc-surface: #0e1218;
            --mc-line: rgba(255, 255, 255, 0.08);
            --mc-text: #e8edf2;
            --mc-muted: #9aa6b2;
            --mc-accent: #14b8a6;
            --mc-accent-deep: #0f766e;
            --mc-warm: #f59e0b;
        }
        body.mc-landing {
            font-family: 'DM Sans', ui-sans-serif, system-ui, sans-serif;
            background: var(--mc-bg);
            color: var(--mc-text);
        }
        .mc-brand {
            font-family: 'Sora', ui-sans-serif, system-ui, sans-serif;
            letter-spacing: -0.04em;
        }
        .mc-hero-stage {
            background:
                radial-gradient(900px 520px at 78% 18%, rgba(20, 184, 166, 0.22), transparent 58%),
                radial-gradient(700px 420px at 12% 88%, rgba(245, 158, 11, 0.12), transparent 55%),
                radial-gradient(1200px 600px at 50% -20%, rgba(56, 189, 248, 0.08), transparent 50%),
                linear-gradient(165deg, #05070a 0%, #0a0f14 42%, #0c1118 100%);
        }
        .mc-hero-grain {
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.45'/%3E%3C/svg%3E");
            opacity: 0.07;
            mix-blend-mode: overlay;
        }
        .mc-rise {
            animation: mcRise 0.85s cubic-bezier(0.22, 1, 0.36, 1) both;
        }
        .mc-rise-2 {
            animation: mcRise 0.95s 0.1s cubic-bezier(0.22, 1, 0.36, 1) both;
        }
        .mc-rise-3 {
            animation: mcRise 1.05s 0.18s cubic-bezier(0.22, 1, 0.36, 1) both;
        }
        .mc-float {
            animation: mcFloat 7s ease-in-out infinite;
        }
        @keyframes mcRise {
            from { opacity: 0; transform: translateY(18px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes mcFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        .mc-cta {
            transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
        }
        .mc-cta:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 36px rgba(20, 184, 166, 0.28);
        }
        .mc-shortcut {
            transition: transform 0.2s ease, border-color 0.2s ease, background 0.2s ease;
        }
        .mc-shortcut:hover {
            transform: translateY(-3px);
            border-color: rgba(20, 184, 166, 0.45);
            background: rgba(20, 184, 166, 0.08);
        }
        .mc-preview-frame {
            box-shadow:
                0 0 0 1px rgba(255, 255, 255, 0.06),
                0 40px 80px rgba(0, 0, 0, 0.55);
        }
        .mc-studio-neon {
            border: 1px solid #39ff14;
            background: rgba(57, 255, 20, 0.06);
            box-shadow:
                0 0 8px rgba(57, 255, 20, 0.45),
                0 0 18px rgba(57, 255, 20, 0.22),
                inset 0 0 10px rgba(57, 255, 20, 0.06);
            text-shadow: 0 0 8px rgba(57, 255, 20, 0.35);
        }
        .mc-studio-neon:hover {
            background: rgba(57, 255, 20, 0.12);
            box-shadow:
                0 0 12px rgba(57, 255, 20, 0.65),
                0 0 28px rgba(57, 255, 20, 0.35),
                inset 0 0 12px rgba(57, 255, 20, 0.1);
            color: #b8ff9a;
        }
        /* Packs · Apoiar · Studio — mesma caixa na navbar */
        .mc-nav-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.375rem;
            height: 2.25rem;
            min-height: 2.25rem;
            padding: 0 0.75rem;
            font-size: 0.875rem;
            line-height: 1;
            font-weight: 600;
            border-radius: 0.375rem;
            box-sizing: border-box;
            white-space: nowrap;
        }
        .mc-nav-action--sm {
            height: 2.25rem;
            min-height: 2.25rem;
            padding: 0 0.75rem;
            font-size: 0.875rem;
        }
        .mc-nav-action-icon {
            width: 1rem;
            height: 1rem;
        }
        .mc-nav-action-icon svg {
            width: 1rem;
            height: 1rem;
            display: block;
        }
        @media (max-width: 639px) {
            .mc-hero-stage { min-height: auto; }
            .mc-preview-phone { display: none; }
        }
        @media (prefers-reduced-motion: reduce) {
            .mc-rise, .mc-rise-2, .mc-rise-3, .mc-float { animation: none; }
        }
    </style>
</head>
<body class="mc-landing antialiased min-h-screen" x-data="markCraftHub">
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.getRegistrations().then((regs) => {
                regs.forEach((reg) => reg.unregister());
            }).catch(() => {});
        }
    </script>
    <style>[x-cloak]{display:none!important}</style>

    @include('partials.dark_site_navbar', ['context' => 'landing'])

    {{-- Hero: uma composição — marca, frase, CTA, visual de produto --}}
    <section class="mc-hero-stage relative overflow-hidden min-h-[min(92vh,820px)]">
        <div class="mc-hero-grain pointer-events-none absolute inset-0" aria-hidden="true"></div>
        <div class="relative mx-auto max-w-6xl px-4 pt-10 pb-12 sm:pt-14 sm:pb-16 md:pt-20 md:pb-24 grid lg:grid-cols-[1.05fr_0.95fr] gap-8 sm:gap-10 lg:gap-14 items-center">
            <div class="relative z-10">
                <p class="mc-rise mc-brand text-[2.75rem] leading-[0.92] sm:text-6xl md:text-7xl font-extrabold text-white">
                    MarkCraft
                </p>
                <p class="mc-rise-2 mt-4 sm:mt-5 max-w-md text-base sm:text-lg md:text-xl text-zinc-300 leading-relaxed">
                    Do blank ao post pronto — layouts, elementos e export no navegador.
                </p>
                <div class="mc-rise-3 mt-6 sm:mt-8 flex flex-col sm:flex-row flex-wrap items-stretch sm:items-center gap-3">
                    @auth
                        <a href="{{ route('studio') }}" class="mc-cta inline-flex justify-center rounded-md bg-teal-500 px-6 py-3.5 text-base font-semibold text-zinc-950 hover:bg-teal-400">
                            Abrir Studio
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="mc-cta inline-flex justify-center rounded-md bg-teal-500 px-6 py-3.5 text-base font-semibold text-zinc-950 hover:bg-teal-400">
                            Começar grátis
                        </a>
                        <a href="{{ route('login') }}" class="inline-flex justify-center rounded-md border border-white/15 bg-white/5 px-5 py-3.5 text-base font-medium text-zinc-100 hover:bg-white/10 transition">
                            Já tenho conta
                        </a>
                    @endauth
                </div>
                <p class="mc-rise-3 mt-5 text-sm text-zinc-500">
                    Grátis · família CriaSys · artes não ficam no servidor
                </p>
            </div>

            {{-- Visual âncora: mock de prancheta (produto), full-bleed no eixo direito --}}
            <div class="mc-rise-2 mc-float relative lg:justify-self-end w-full max-w-md sm:max-w-lg mx-auto lg:mx-0" aria-hidden="true">
                <div class="mc-preview-frame relative aspect-[4/5] w-full overflow-hidden rounded-sm bg-[#121820]">
                    <div class="absolute inset-0 bg-[linear-gradient(145deg,#134e4a_0%,#0f172a_48%,#1c1917_100%)]"></div>
                    <div class="absolute inset-[12%] border border-white/10 bg-zinc-950/40 backdrop-blur-[2px]">
                        <div class="absolute top-[12%] left-[10%] right-[18%] h-3 rounded-sm bg-teal-400/80"></div>
                        <div class="absolute top-[22%] left-[10%] w-[55%] h-2 rounded-sm bg-white/25"></div>
                        <div class="absolute top-[28%] left-[10%] w-[40%] h-2 rounded-sm bg-white/15"></div>
                        <div class="absolute bottom-[18%] left-[10%] right-[10%] aspect-square rounded-sm bg-gradient-to-br from-amber-500/30 via-teal-500/20 to-transparent border border-white/10"></div>
                        <div class="absolute bottom-[10%] left-[10%] text-[10px] tracking-widest uppercase text-zinc-400">1080 × 1350</div>
                    </div>
                    <div class="mc-preview-phone absolute -right-6 top-1/4 w-28 aspect-[9/16] rounded-sm border border-white/10 bg-zinc-900/90 shadow-2xl rotate-3">
                        <div class="m-2 h-full rounded-sm bg-gradient-to-b from-teal-700/50 to-zinc-900"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Atalhos (inspiração Canva: “o que você quer criar”) --}}
    <section class="mx-auto max-w-6xl px-4 pt-12 sm:pt-16 pb-8">
        <h2 class="mc-brand text-xl sm:text-2xl md:text-3xl font-bold text-white">O que você quer criar agora?</h2>
        <p class="mt-2 max-w-xl text-zinc-400">
            Escolha um formato e
            @guest
                <span class="text-teal-300">crie sua conta</span> para abrir no Studio.
            @else
                abra direto no Studio.
            @endguest
        </p>

        <div class="mt-6 sm:mt-8 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2.5 sm:gap-3">
            @foreach($shortcuts as $item)
                @php
                    $href = auth()->check()
                        ? route('studio', ['preset' => $item['preset']])
                        : route('register', ['preset' => $item['preset']]);
                @endphp
                <a href="{{ $href }}" class="mc-shortcut group block rounded-lg border border-white/10 bg-white/[0.03] p-3 sm:p-4 text-left">
                    <span class="block h-1.5 w-8 rounded-sm mb-4
                        @if($item['tone'] === 'teal') bg-teal-400
                        @elseif($item['tone'] === 'amber') bg-amber-400
                        @elseif($item['tone'] === 'rose') bg-rose-400
                        @elseif($item['tone'] === 'sky') bg-sky-400
                        @elseif($item['tone'] === 'lime') bg-lime-400
                        @else bg-orange-400
                        @endif
                    "></span>
                    <span class="block text-sm font-semibold text-zinc-100 group-hover:text-white">{{ $item['label'] }}</span>
                    <span class="mt-1 block text-xs text-zinc-500">{{ $item['hint'] }}</span>
                </a>
            @endforeach
        </div>

        @guest
            <p class="mt-6 text-sm text-zinc-500">
                Sem cartão · leva menos de um minuto ·
                <a href="{{ route('register') }}" class="text-teal-300 hover:text-teal-200 underline-offset-2 hover:underline">Começar grátis</a>
            </p>
        @endguest
    </section>

    {{-- Ferramentas + Packs + Apoiar com ícones destacados --}}
    <section class="mx-auto max-w-6xl px-4 pt-6 pb-10" id="ferramentas">
        <div>
            <p class="text-xs uppercase tracking-[0.16em] text-zinc-500">Hub CriaSys</p>
            <h2 class="mc-brand mt-1 text-2xl font-bold text-white">Atalhos do hub</h2>
            <p class="mt-1 max-w-xl text-sm text-zinc-400">Ferramentas, packs e apoio — cada atalho com ícone claro.</p>
        </div>
        <div class="mt-6">
            @include('partials.tool_shortcut_buttons', ['variant' => 'grid'])
        </div>
        <div class="mt-3">
            @include('partials.hub_shortcut_buttons', ['variant' => 'grid'])
        </div>
    </section>

    <div class="mx-auto max-w-6xl px-4 pb-6 relative z-10">
        <x-ad-slot slot-id="ad_landing_hero" class="!border-white/10 !bg-zinc-900/50 !text-zinc-500" />
    </div>

    <section class="mx-auto max-w-6xl px-4 py-10 sm:py-14 border-t border-white/5">
        <h2 class="mc-brand text-xl sm:text-2xl font-bold text-white">Feito para publicar, não para enrolar</h2>
        <p class="mt-2 max-w-2xl text-sm sm:text-base text-zinc-400">Presets de redes, texto, formas, remover fundo e export. Sem vídeo, sem áudio — foco em imagem e texto.</p>
        <ul class="mt-8 sm:mt-10 grid gap-8 sm:gap-10 md:grid-cols-3">
            <li>
                <p class="text-sm font-semibold tracking-wide uppercase text-teal-300/90">Layouts & pacotes</p>
                <p class="mt-2 text-zinc-300 leading-relaxed">Comece por um formato de rede ou um pacote pronto e ajuste no canvas.</p>
            </li>
            <li>
                <p class="text-sm font-semibold tracking-wide uppercase text-amber-300/90">Baixe e limpe</p>
                <p class="mt-2 text-zinc-300 leading-relaxed">Edite → baixe PNG/JPG → limpe o workspace. Privacidade por padrão.</p>
            </li>
            <li>
                <p class="text-sm font-semibold tracking-wide uppercase text-sky-300/90">Hub CriaSys</p>
                <p class="mt-2 text-zinc-300 leading-relaxed">Ferramentas e packs na mesma família — atalhos para o fluxo completo.</p>
            </li>
        </ul>
    </section>

    {{-- Bloco de conversão --}}
    <section class="relative mx-auto max-w-6xl px-4 py-12 sm:py-16">
        <div class="relative overflow-hidden rounded-xl border border-white/10 px-5 py-10 sm:px-6 sm:py-12 md:px-12 md:py-14"
             style="background: radial-gradient(800px 280px at 20% 0%, rgba(20,184,166,0.18), transparent 55%), #0c1118;">
            <p class="mc-brand text-2xl sm:text-3xl md:text-4xl font-bold text-white max-w-lg leading-tight">
                Sua próxima arte começa com uma conta grátis.
            </p>
            <p class="mt-3 max-w-md text-zinc-400">
                Cadastro rápido. Studio no navegador. Sem instalar nada.
            </p>
            <div class="mt-8 flex flex-col sm:flex-row flex-wrap gap-3">
                @auth
                    <a href="{{ route('studio') }}" class="mc-cta inline-flex justify-center rounded-md bg-teal-500 px-6 py-3 font-semibold text-zinc-950 hover:bg-teal-400">Ir para o Studio</a>
                @else
                    <a href="{{ route('register') }}" class="mc-cta inline-flex justify-center rounded-md bg-teal-500 px-6 py-3 font-semibold text-zinc-950 hover:bg-teal-400">Criar minha conta</a>
                    <a href="{{ route('login') }}" class="inline-flex justify-center rounded-md border border-white/15 px-5 py-3 font-medium text-zinc-200 hover:bg-white/5 transition">Entrar</a>
                @endauth
            </div>
        </div>
    </section>

    <div class="mx-auto max-w-6xl px-4 pb-10">
        <x-ad-slot slot-id="ad_landing_mid" class="!border-white/10 !bg-zinc-900/50 !text-zinc-500" />
    </div>

    <footer class="border-t border-white/5 bg-[#05070a] text-zinc-400">
        <div class="mx-auto max-w-6xl px-4 py-10 text-sm">
            <p class="mc-brand text-lg text-white">MarkCraft</p>
            <p class="mt-2 max-w-2xl">Família CriaSys · contribuições opcionais mantêm o projeto vivo</p>
            <div class="mt-5 flex flex-wrap gap-5">
                <button type="button" @click="openHub('apoiar')" class="inline-flex items-center gap-2 hover:text-teal-300 transition">
                    <span class="text-rose-300">@include('partials.tool_icon', ['icon' => 'heart', 'size' => 16])</span>
                    Apoiar a partir de R$ 2
                </button>
                <button type="button" @click="openHub('packs')" class="inline-flex items-center gap-2 hover:text-teal-300 transition">
                    <span class="text-amber-300">@include('partials.tool_icon', ['icon' => 'packs', 'size' => 16])</span>
                    Packs
                </button>
                <a href="#ferramentas" class="hover:text-teal-300 transition">Ferramentas</a>
                @guest
                    <a href="{{ route('register') }}" class="hover:text-teal-300 transition">Criar conta</a>
                @else
                    <a href="{{ route('studio') }}" class="hover:text-teal-300 transition">Studio</a>
                @endguest
                <span class="text-zinc-600">PSD · PNG · JPG · WebP · SVG · PDF</span>
            </div>
        </div>
    </footer>

    @include('partials.hub_glass_modals')
</body>
</html>
