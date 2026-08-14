<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.seo_head', [
        'seo' => [
            'title' => config('seo.default_title'),
            'description' => config('seo.default_description'),
            'canonical' => route('home'),
            'type' => 'website',
        ],
        'seoIncludeWebSite' => true,
    ])
    @include('partials.head_favicon')
    @include('partials.analytics_head', ['analyticsSurface' => 'landing'])
    @php
        $adsenseClient = null;
        foreach (['landing_mid', 'landing_footer'] as $adKey) {
            $row = ($cmsAds ?? [])[$adKey] ?? [];
            if (($row['mode'] ?? '') === 'adsense' && filled($row['adsense_client'] ?? null) && !empty($row['enabled'])) {
                $adsenseClient = $row['adsense_client'];
                break;
            }
        }
    @endphp
    @if($adsenseClient)
        <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ $adsenseClient }}" crossorigin="anonymous"></script>
    @endif
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
        html {
            overflow-x: clip;
            max-width: 100%;
        }
        body.mc-landing {
            font-family: 'DM Sans', ui-sans-serif, system-ui, sans-serif;
            background: var(--mc-bg);
            color: var(--mc-text);
            overflow-x: hidden;
            max-width: 100%;
        }
        .mc-app-shell {
            width: 100%;
            max-width: 100%;
            min-width: 0;
            overflow-x: hidden;
        }
        .mc-brand {
            font-family: 'Sora', ui-sans-serif, system-ui, sans-serif;
            letter-spacing: -0.04em;
        }
        .mc-hero-stage {
            height: auto;
            min-height: 40vh;
            max-height: none;
            position: relative;
            overflow: visible;
            background:
                radial-gradient(900px 520px at 88% 12%, rgba(244, 63, 94, 0.42), transparent 55%),
                radial-gradient(700px 480px at 8% 90%, rgba(147, 51, 234, 0.38), transparent 52%),
                radial-gradient(600px 400px at 55% 40%, rgba(88, 28, 135, 0.28), transparent 60%),
                radial-gradient(500px 360px at 70% 70%, rgba(251, 113, 133, 0.18), transparent 50%),
                linear-gradient(165deg, #050508 0%, #0c0610 35%, #0a0712 65%, #030305 100%);
        }
        .mc-hero-stage::after {
            content: '';
            position: absolute;
            inset: 0;
            pointer-events: none;
            background:
                radial-gradient(ellipse 80% 60% at 100% 0%, rgba(244, 63, 94, 0.2), transparent 50%),
                radial-gradient(ellipse 70% 55% at 0% 100%, rgba(126, 34, 206, 0.22), transparent 48%);
            mix-blend-mode: screen;
            opacity: 0.85;
        }
        .mc-hero-inner {
            height: 100%;
            display: grid;
            align-items: center;
            gap: 1.25rem;
            position: relative;
            z-index: 1;
        }
        @media (min-width: 768px) {
            .mc-hero-inner {
                grid-template-columns: 1fr;
                max-width: 42rem;
            }
        }
        .mc-hero-visual {
            position: relative;
            height: calc(40vh - 2rem);
            max-height: 100%;
            width: 100%;
            max-width: 380px;
            margin-inline: auto;
        }
        @media (min-width: 1024px) {
            .mc-hero-visual {
                max-width: 400px;
                margin-inline: 0 0 0 auto;
                justify-self: end;
            }
        }
        .mc-hero-offer {
            border: 1px solid rgba(255, 255, 255, 0.12);
            background: rgba(8, 8, 12, 0.55);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            box-shadow:
                0 0 0 1px rgba(255, 255, 255, 0.04),
                0 24px 48px rgba(0, 0, 0, 0.45);
        }
        .mc-cta-blog {
            background: #14b8a6;
            color: #09090b;
            box-shadow: 0 10px 28px rgba(20, 184, 166, 0.35);
        }
        .mc-cta-blog:hover {
            background: #2dd4bf;
            color: #09090b;
            box-shadow: 0 14px 36px rgba(20, 184, 166, 0.4);
        }
        /* CTA principal MarkCraft: neon verde */
        .mc-cta-primary {
            border: 1px solid #39ff14;
            background: rgba(57, 255, 20, 0.14);
            color: #39ff14;
            font-weight: 700;
            box-shadow:
                0 0 10px rgba(57, 255, 20, 0.45),
                0 0 24px rgba(57, 255, 20, 0.2);
            text-shadow: 0 0 8px rgba(57, 255, 20, 0.35);
            transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease, color 0.18s ease;
        }
        .mc-cta-primary:hover {
            transform: translateY(-2px);
            background: rgba(57, 255, 20, 0.22);
            color: #b8ff9a;
            box-shadow:
                0 0 14px rgba(57, 255, 20, 0.65),
                0 0 32px rgba(57, 255, 20, 0.3);
        }
        .mc-cta-outline {
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: transparent;
            color: #e4e4e7;
            transition: background 0.18s ease, border-color 0.18s ease, color 0.18s ease;
        }
        .mc-cta-outline:hover {
            border-color: rgba(255, 255, 255, 0.35);
            background: rgba(255, 255, 255, 0.06);
            color: #fafafa;
        }
        .mc-format-thumb {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 2.75rem;
            margin-bottom: 0.75rem;
        }
        .mc-format-frame {
            border: 1.5px solid currentColor;
            border-radius: 3px;
            opacity: 0.85;
            position: relative;
            background: rgba(255, 255, 255, 0.04);
        }
        .mc-hub-tools-grid .mc-hub-icon {
            width: 2.75rem;
            height: 2.75rem;
        }
        .mc-bridge-section {
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            padding-top: 2.25rem;
        }
        .mc-bridge-grid {
            display: grid;
            gap: 2rem;
            align-items: center;
        }
        @media (min-width: 900px) {
            .mc-bridge-grid {
                grid-template-columns: 1.15fr 0.85fr;
                gap: 2.5rem;
            }
        }
        .mc-bridge-art {
            position: relative;
        }
        .mc-bridge-art svg {
            filter: drop-shadow(0 12px 28px rgba(0, 0, 0, 0.35));
        }
        .mc-bridge-tools {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .mc-bridge-tool {
            display: flex;
            gap: 0.85rem;
            align-items: flex-start;
            padding: 1rem;
            border-radius: 0.75rem;
            border: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(255, 255, 255, 0.025);
        }
        .mc-bridge-tool-icon {
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.65rem;
            border: 1px solid rgba(168, 85, 247, 0.35);
            background: rgba(168, 85, 247, 0.12);
            color: #c084fc;
        }
        .mc-bridge-tool-title {
            font-size: 0.9rem;
            font-weight: 700;
            color: #f4f4f5;
            margin: 0 0 0.25rem;
        }
        .mc-bridge-tool-text {
            margin: 0;
            font-size: 0.8rem;
            line-height: 1.45;
            color: #a1a1aa;
        }
        .mc-bridge-extras {
            padding: 0.85rem 1rem;
            border-radius: 0.65rem;
            border: 1px dashed rgba(255, 255, 255, 0.1);
            background: rgba(0, 0, 0, 0.2);
        }
        @media (max-width: 767px) {
            .mc-hero-stage {
                height: auto;
                min-height: 40vh;
                max-height: none;
            }
            .mc-hero-inner {
                padding-top: 1.25rem;
                padding-bottom: 1.25rem;
            }
            .mc-hero-visual {
                height: auto;
                max-width: 100%;
            }
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
            transition: transform 0.2s ease, border-color 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
        }
        .mc-shortcut:hover {
            transform: translateY(-3px);
        }
        /* Formatos: neon azul suave */
        .mc-shortcut-neon-blue {
            border: 1px solid rgba(56, 189, 248, 0.55);
            background: rgba(56, 189, 248, 0.04);
            box-shadow:
                0 0 5px rgba(56, 189, 248, 0.22),
                0 0 12px rgba(56, 189, 248, 0.1),
                inset 0 0 8px rgba(56, 189, 248, 0.03);
        }
        .mc-shortcut-neon-blue:hover {
            border-color: rgba(56, 189, 248, 0.75);
            background: rgba(56, 189, 248, 0.08);
            box-shadow:
                0 0 8px rgba(56, 189, 248, 0.32),
                0 0 18px rgba(56, 189, 248, 0.16),
                inset 0 0 10px rgba(56, 189, 248, 0.05);
        }
        .mc-shortcut-neon-blue .mc-shortcut-bar {
            background: #38bdf8;
            box-shadow: 0 0 6px rgba(56, 189, 248, 0.4);
        }
        .mc-shortcut-neon-blue .mc-shortcut-label {
            color: #e0f2fe;
        }
        .mc-shortcut-neon-blue:hover .mc-shortcut-label {
            color: #f0f9ff;
        }
        .mc-shortcut-neon-blue .mc-shortcut-hint {
            color: rgba(186, 230, 253, 0.92);
        }
        /* Studio completo: neon verde forte */
        .mc-shortcut-neon {
            border: 1px solid #39ff14;
            background: rgba(57, 255, 20, 0.06);
            box-shadow:
                0 0 8px rgba(57, 255, 20, 0.4),
                0 0 18px rgba(57, 255, 20, 0.18),
                inset 0 0 10px rgba(57, 255, 20, 0.05);
        }
        .mc-shortcut-neon:hover {
            border-color: #39ff14;
            background: rgba(57, 255, 20, 0.12);
            box-shadow:
                0 0 12px rgba(57, 255, 20, 0.6),
                0 0 28px rgba(57, 255, 20, 0.3),
                inset 0 0 12px rgba(57, 255, 20, 0.08);
        }
        .mc-shortcut-neon .mc-shortcut-bar {
            background: #39ff14;
            box-shadow: 0 0 8px rgba(57, 255, 20, 0.65);
        }
        .mc-shortcut-neon .mc-shortcut-label {
            color: #39ff14;
            text-shadow: 0 0 8px rgba(57, 255, 20, 0.35);
        }
        .mc-shortcut-neon:hover .mc-shortcut-label {
            color: #b8ff9a;
        }
        .mc-shortcut-neon .mc-shortcut-hint {
            color: rgba(184, 255, 154, 0.9);
        }
        /* Packs: neon amarelo sol */
        .mc-shortcut-neon-sun {
            border: 1px solid rgba(250, 204, 21, 0.7);
            background: rgba(250, 204, 21, 0.06);
            box-shadow:
                0 0 6px rgba(250, 204, 21, 0.28),
                0 0 14px rgba(250, 204, 21, 0.12),
                inset 0 0 8px rgba(250, 204, 21, 0.04);
        }
        .mc-shortcut-neon-sun:hover {
            border-color: #facc15;
            background: rgba(250, 204, 21, 0.1);
            box-shadow:
                0 0 10px rgba(250, 204, 21, 0.4),
                0 0 22px rgba(250, 204, 21, 0.18),
                inset 0 0 10px rgba(250, 204, 21, 0.06);
        }
        .mc-shortcut-neon-sun .mc-hub-icon {
            border-color: rgba(250, 204, 21, 0.45);
            background: rgba(250, 204, 21, 0.12);
            color: #facc15;
            box-shadow: 0 0 8px rgba(250, 204, 21, 0.25);
        }
        .mc-shortcut-neon-sun .mc-shortcut-label {
            color: #fde047;
            text-shadow: 0 0 6px rgba(250, 204, 21, 0.3);
        }
        .mc-shortcut-neon-sun .mc-shortcut-hint {
            color: rgba(253, 224, 71, 0.92);
        }
        /* Apoiar: rosa comunidade (coração + borda) */
        .mc-shortcut-neon-shock {
            border: 1px solid rgba(251, 113, 133, 0.75);
            background: rgba(244, 63, 94, 0.1);
            box-shadow:
                0 0 10px rgba(244, 63, 94, 0.4),
                0 0 24px rgba(251, 113, 133, 0.22),
                inset 0 0 12px rgba(244, 63, 94, 0.06);
        }
        .mc-shortcut-neon-shock:hover {
            border-color: #fb7185;
            background: rgba(244, 63, 94, 0.16);
            box-shadow:
                0 0 14px rgba(244, 63, 94, 0.55),
                0 0 32px rgba(251, 113, 133, 0.3),
                inset 0 0 14px rgba(244, 63, 94, 0.08);
        }
        .mc-shortcut-neon-shock .mc-hub-icon {
            border-color: rgba(244, 63, 94, 0.55);
            background: rgba(244, 63, 94, 0.16);
            color: #fb7185;
            box-shadow: 0 0 8px rgba(244, 63, 94, 0.35);
        }
        .mc-shortcut-neon-shock .mc-shortcut-label {
            color: #fda4af;
            text-shadow: 0 0 8px rgba(244, 63, 94, 0.4);
        }
        .mc-shortcut-neon-shock .mc-shortcut-hint {
            color: rgba(254, 205, 211, 0.95);
        }
        .mc-shortcut-neon-blue .mc-hub-icon {
            border-color: rgba(56, 189, 248, 0.4);
            background: rgba(56, 189, 248, 0.1);
            color: #38bdf8;
            box-shadow: 0 0 6px rgba(56, 189, 248, 0.2);
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
        .mc-nav-neon-sun {
            border: 1px solid rgba(250, 204, 21, 0.65);
            background: rgba(250, 204, 21, 0.08);
            color: #fde047;
            box-shadow: 0 0 6px rgba(250, 204, 21, 0.25);
        }
        .mc-nav-neon-sun:hover {
            background: rgba(250, 204, 21, 0.14);
            color: #fef08a;
        }
        .mc-nav-neon-shock {
            border: 1px solid rgba(251, 113, 133, 0.7);
            background: rgba(244, 63, 94, 0.12);
            color: #fda4af;
            box-shadow:
                0 0 10px rgba(244, 63, 94, 0.4),
                0 0 20px rgba(251, 113, 133, 0.2);
            text-shadow: 0 0 8px rgba(244, 63, 94, 0.35);
        }
        .mc-nav-neon-shock:hover {
            background: rgba(244, 63, 94, 0.2);
            color: #fecdd3;
            border-color: #fb7185;
        }
        .mc-nav-neon-shock .mc-nav-action-icon {
            color: #f43f5e;
        }
        @media (max-width: 639px) {
            .mc-preview-phone { display: none; }
        }
        @media (prefers-reduced-motion: reduce) {
            .mc-rise, .mc-rise-2, .mc-rise-3, .mc-float { animation: none; }
        }
        .mc-showcase-mosaic {
            display: grid;
            grid-template-columns: 1.2fr 0.8fr 1fr;
            grid-template-rows: 1fr 0.85fr;
            gap: 0.4rem;
            padding: 0.85rem;
            height: 100%;
            min-height: 10.5rem;
            background:
                radial-gradient(280px 120px at 80% 0%, rgba(250, 204, 21, 0.22), transparent 55%),
                #0a0a0c;
        }
        .mc-showcase-mosaic span {
            border-radius: 0.35rem;
            border: 1px solid rgba(255,255,255,0.12);
            background: linear-gradient(145deg, rgba(251,113,133,0.35), rgba(88,28,135,0.25));
        }
        .mc-showcase-mosaic span:nth-child(1) { grid-column: 1; grid-row: 1 / span 2; }
        .mc-showcase-mosaic span:nth-child(2) { background: linear-gradient(160deg, rgba(56,189,248,0.3), rgba(15,23,42,0.5)); }
        .mc-showcase-tips {
            display: grid;
            grid-template-columns: 4.5rem 1fr 1fr;
            gap: 0.45rem;
            padding: 0.85rem;
            min-height: 10.5rem;
            background:
                radial-gradient(240px 100px at 10% 100%, rgba(244, 63, 94, 0.2), transparent 50%),
                #0a0a0c;
        }
        .mc-showcase-tips span {
            border-radius: 0.4rem;
            border: 1px solid rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.04);
        }
        .mc-showcase-tips span:first-child {
            grid-row: 1 / span 3;
            display: flex;
            align-items: center;
            justify-content: center;
            font: 700 1.25rem/1 'Sora', sans-serif;
            color: #fde68a;
        }
    </style>
</head>
<body class="mc-landing antialiased min-h-screen overflow-x-hidden" x-data="markCraftHub">
    @include('partials.analytics_body', ['analyticsSurface' => 'landing'])
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.getRegistrations().then((regs) => {
                regs.forEach((reg) => reg.unregister());
            }).catch(() => {});
        }
    </script>
    <style>[x-cloak]{display:none!important}</style>

    @include('partials.dark_site_navbar', ['context' => 'landing'])

    {{-- Hero: portal gratuito (sem card de Blog) --}}
    <section class="mc-hero-stage relative">
        <div class="mc-hero-grain pointer-events-none absolute inset-0" aria-hidden="true"></div>
        <div class="mc-hero-inner relative mx-auto max-w-6xl px-4 py-6 md:py-8">
            <div class="relative z-10 min-w-0">
                <p class="mc-rise text-[10px] uppercase tracking-[0.16em] text-[#39ff14]/80 mb-2">
                    {{ $cmsHome['hero_eyebrow'] ?? 'Studio de imagem gratuito' }}
                </p>
                <p class="mc-rise mc-brand text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-extrabold text-white leading-[0.98]">
                    {{ $cmsHome['hero_title'] ?? 'MarkCraft — Studio de Imagem 100% Gratuito & Privado' }}
                </p>
                <p class="mc-rise-2 mt-3 sm:mt-4 max-w-md text-base sm:text-lg md:text-xl text-zinc-200 leading-relaxed">
                    {{ $cmsHome['hero_blurb'] ?? 'Crie artes para redes sociais, capas e banners com qualidade profissional, sem cadastro, sem marca d\'água e sem complicações.' }}
                </p>
                <div class="mc-rise-3 mt-5 flex flex-col sm:flex-row flex-wrap items-stretch sm:items-center gap-3">
                    @auth
                        <a href="{{ route('studio') }}" class="mc-cta-primary inline-flex justify-center rounded-md px-6 py-3 text-base">
                            Abrir Studio
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="mc-cta-primary inline-flex justify-center rounded-md px-6 py-3 text-base">
                            Começar grátis
                        </a>
                        <a href="{{ route('login') }}" class="mc-cta-outline inline-flex justify-center rounded-md px-5 py-3 text-base font-medium">
                            Já tenho conta
                        </a>
                    @endauth
                </div>
                <p class="mc-rise-3 mt-3 text-sm text-zinc-400">
                    {{ $cmsHome['hero_badges'] ?? 'Sem cartão · 100% grátis · Artes privadas (processadas no navegador)' }}
                </p>
            </div>
        </div>
    </section>

    @if(!empty($cmsHome['show_hero_showcase'] ?? true))
        @include('partials.hero_showcase')
    @endif

    @if(!empty($cmsHome['show_format_shortcuts'] ?? true))
    {{-- Atalhos (inspiração Canva: “o que você quer criar”) --}}
    <section id="formatos" class="mx-auto max-w-6xl px-4 pt-12 sm:pt-16 pb-12 sm:pb-14">
        <h2 class="mc-brand text-xl sm:text-2xl md:text-3xl font-bold text-white">{{ $cmsHome['formats_heading'] ?? 'O que você quer criar agora?' }}</h2>
        <p class="mt-2 max-w-xl text-zinc-400">
            {{ $cmsHome['formats_blurb'] ?? 'Escolha um formato e abra direto no Studio' }}
            @guest
                <span class="text-zinc-500">(se precisar, pedimos login na sequência)</span>
            @endguest
            .
        </p>

        <div class="mt-6 sm:mt-8 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2.5 sm:gap-3">
            @foreach($shortcuts as $item)
                @php
                    $isStudioCard = !empty($item['featured']) || ($item['tone'] ?? '') === 'neon';
                    $icon = $item['icon'] ?? 'square';
                    // Sempre aponta para o Studio (com preset). Guest → login com URL intended preservada.
                    $href = $isStudioCard || empty($item['preset'])
                        ? route('studio')
                        : route('studio', ['preset' => $item['preset']]);
                @endphp
                <a
                    href="{{ $href }}"
                    class="mc-shortcut group block rounded-lg p-3 sm:p-4 text-left {{ $isStudioCard ? 'mc-shortcut-neon' : 'mc-shortcut-neon-blue' }}"
                    @guest title="Entre para abrir no Studio com este formato" @endguest
                >
                    <span class="mc-format-thumb {{ $isStudioCard ? 'text-[#39ff14]' : 'text-sky-300' }}" aria-hidden="true">
                        @if($icon === 'square')
                            <span class="mc-format-frame" style="width:2rem;height:2rem;"></span>
                        @elseif($icon === 'phone')
                            <span class="mc-format-frame" style="width:1.15rem;height:2.05rem;border-radius:4px;"></span>
                        @elseif($icon === 'play')
                            <span class="mc-format-frame flex items-center justify-center" style="width:2.35rem;height:1.35rem;">
                                <span style="width:0;height:0;border-left:6px solid currentColor;border-top:4px solid transparent;border-bottom:4px solid transparent;opacity:0.9;"></span>
                            </span>
                        @elseif($icon === 'brief')
                            <span class="mc-format-frame" style="width:2.4rem;height:1.25rem;"></span>
                        @elseif($icon === 'cover')
                            <span class="mc-format-frame" style="width:2.55rem;height:1.15rem;"></span>
                        @else
                            <span class="mc-format-frame flex items-center justify-center" style="width:2rem;height:2rem;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                            </span>
                        @endif
                    </span>
                    <span class="mc-shortcut-label block text-sm font-semibold">{{ $item['label'] }}</span>
                    <span class="mc-shortcut-hint mt-1 block text-xs">{{ $item['hint'] }}</span>
                </a>
            @endforeach
        </div>
    </section>
    @endif

    @if(!empty($cmsHome['show_hub'] ?? true))
    @php $hubCopy = $cmsLanding['hub'] ?? \App\Support\Cms::landing('hub', []); @endphp
    {{-- Ferramentas + produtos CriaSys --}}
    <section class="mx-auto max-w-6xl px-4 pt-4 sm:pt-6 pb-10" id="ferramentas">
        <div>
            <p class="text-xs uppercase tracking-[0.16em] text-zinc-500">{{ $hubCopy['eyebrow'] ?? 'Hub de utilitários' }}</p>
            <h2 class="mc-brand mt-1 text-2xl font-bold text-white">{{ $hubCopy['headline'] ?? 'Nossa linha de ferramentas gratuitas' }}</h2>
            <p class="mt-1 max-w-xl text-sm text-zinc-400">{{ $hubCopy['intro'] ?? 'Editor, conversor, encurtador e PDF — no mesmo ambiente, sem assinatura para o básico.' }}</p>
            <p class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1.5 text-[11px] text-zinc-400" aria-label="Legenda de cores do hub">
                <span class="inline-flex items-center gap-1.5">
                    <span class="h-2 w-2 rounded-sm bg-sky-400 shadow-[0_0_6px_rgba(56,189,248,0.7)]" aria-hidden="true"></span>
                    Azul · ferramentas grátis
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="h-2 w-2 rounded-sm bg-rose-400 shadow-[0_0_6px_rgba(244,63,94,0.7)]" aria-hidden="true"></span>
                    Rosa · Apoiar / comunidade
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="h-2 w-2 rounded-sm bg-[#39ff14] shadow-[0_0_6px_rgba(57,255,20,0.7)]" aria-hidden="true"></span>
                    Verde · Studio
                </span>
            </p>
        </div>
        <div class="mt-6 mc-hub-tools-grid">
            @include('partials.tool_shortcut_buttons', ['variant' => 'grid'])
        </div>
        <div class="mt-3">
            @include('partials.hub_shortcut_buttons', ['variant' => 'grid', 'showPacks' => false])
        </div>
    </section>
    @endif

    @include('partials.hosting_partner_card')

    @include('partials.social_proof_placeholder')

    @php
        $editorSection = $cmsLanding['editor'] ?? \App\Support\Cms::landing('editor', []);
        $editorToneClass = static function (string $tone): string {
            return match ($tone) {
                'amber' => 'text-amber-300/90',
                'sky' => 'text-sky-300/90',
                default => 'text-teal-300/90',
            };
        };
    @endphp
    <section class="mx-auto max-w-6xl px-4 py-10 sm:py-14 border-t border-white/5">
        <h2 class="mc-brand text-xl sm:text-2xl font-bold text-white">{{ $editorSection['headline'] ?? 'Editor de verdade — feito para quem publica' }}</h2>
        @if(!empty($editorSection['intro']))
            <p class="mt-2 max-w-2xl text-sm sm:text-base text-zinc-400">{{ $editorSection['intro'] }}</p>
        @endif
        <ul class="mt-8 sm:mt-10 grid gap-8 sm:gap-10 md:grid-cols-3">
            @foreach($editorSection['columns'] ?? [] as $col)
                <li>
                    <p class="text-sm font-semibold tracking-wide uppercase {{ $editorToneClass($col['tone'] ?? 'teal') }}">{{ $col['label'] ?? '' }}</p>
                    <p class="mt-2 text-zinc-300 leading-relaxed">{{ $col['text'] ?? '' }}</p>
                </li>
            @endforeach
        </ul>
    </section>

    @if(!empty($cmsHome['show_landing_promo'] ?? true))
    <div class="mx-auto max-w-6xl px-4 pb-10">
        <x-promo-slot slot="landing_mid" />
    </div>
    @endif

    @include('partials.landing_ad_slot', ['key' => 'landing_mid'])
    @include('partials.newsletter_signup')
    @include('partials.landing_ad_slot', ['key' => 'landing_footer'])

    @include('partials.site_footer')

    @include('partials.hub_glass_modals', ['showPacks' => false])
</body>
</html>
