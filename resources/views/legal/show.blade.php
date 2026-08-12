<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.seo_head', [
        'seo' => [
            'title' => ($pageMeta['title'] ?? 'Legal').' — '.config('app.name', 'MarkCraft'),
            'description' => $pageMeta['description'] ?? 'Documentos legais do MarkCraft.',
            'canonical' => route('legal.show', $pageKey),
            'type' => 'website',
        ],
        'seoIncludeWebSite' => false,
        'seoJsonLd' => false,
    ])
    @include('partials.head_favicon')
    @include('partials.analytics_head', ['analyticsSurface' => 'landing'])
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=sora:600,700,800|dm-sans:400,500,600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --mc-bg: #07090c;
            --mc-surface: #0e1218;
            --mc-line: rgba(255, 255, 255, 0.08);
            --mc-text: #e8edf2;
            --mc-muted: #9aa6b2;
            --mc-accent: #14b8a6;
        }
        body.mc-legal {
            font-family: 'DM Sans', ui-sans-serif, system-ui, sans-serif;
            color: var(--mc-text);
            background:
                radial-gradient(900px 480px at 10% -10%, rgba(20, 184, 166, 0.16), transparent 55%),
                radial-gradient(700px 400px at 100% 0%, rgba(245, 158, 11, 0.07), transparent 50%),
                var(--mc-bg);
            min-height: 100vh;
        }
        .mc-brand { font-family: 'Sora', ui-sans-serif, system-ui, sans-serif; letter-spacing: -0.03em; }
        .legal-prose h2 {
            font-family: 'Sora', ui-sans-serif, system-ui, sans-serif;
            font-size: 1.15rem;
            font-weight: 700;
            color: #fff;
            margin: 2rem 0 0.75rem;
            letter-spacing: -0.02em;
        }
        .legal-prose h3 {
            font-size: 0.95rem;
            font-weight: 600;
            color: #e4e4e7;
            margin: 1.35rem 0 0.5rem;
        }
        .legal-prose p, .legal-prose li {
            color: #a1a1aa;
            font-size: 0.95rem;
            line-height: 1.7;
        }
        .legal-prose p { margin: 0.65rem 0; }
        .legal-prose ul, .legal-prose ol {
            margin: 0.5rem 0 0.85rem;
            padding-left: 1.25rem;
        }
        .legal-prose ul { list-style: disc; }
        .legal-prose ol { list-style: decimal; }
        .legal-prose li + li { margin-top: 0.35rem; }
        .legal-prose a { color: #5eead4; text-decoration: underline; text-underline-offset: 2px; }
        .legal-prose a:hover { color: #99f6e4; }
        .legal-prose strong { color: #d4d4d8; font-weight: 600; }
        .legal-nav a.is-active {
            border-color: rgba(45, 212, 191, 0.45);
            background: rgba(20, 184, 166, 0.12);
            color: #99f6e4;
        }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="mc-legal antialiased" x-data="markCraftHub">
    @include('partials.analytics_body', ['analyticsSurface' => 'landing'])
    @include('partials.dark_site_navbar', ['context' => 'landing'])

    <main class="mx-auto max-w-6xl px-4 py-8 sm:py-10">
        <div class="mb-6">
            <p class="text-[10px] uppercase tracking-[0.16em] text-teal-400/80">Documentos legais</p>
            <h1 class="mc-brand mt-2 text-3xl sm:text-4xl font-extrabold text-white leading-tight">
                {{ $pageMeta['title'] }}
            </h1>
            <p class="mt-2 text-sm text-zinc-500">
                Última atualização: {{ $legal['last_updated'] ?? '' }}
                · {{ $legal['product_name'] ?? 'MarkCraft' }} · família {{ $legal['brand_family'] ?? 'CriaSys' }}
            </p>
        </div>

        <div class="grid gap-8 lg:grid-cols-[220px_minmax(0,1fr)]">
            <aside class="legal-nav h-fit lg:sticky lg:top-24 space-y-2">
                <p class="text-[10px] uppercase tracking-[0.14em] text-zinc-500 px-1">Índice</p>
                @foreach($allPages as $slug => $meta)
                    <a
                        href="{{ route('legal.show', $slug) }}"
                        class="block rounded-lg border border-transparent px-3 py-2 text-sm text-zinc-400 hover:text-teal-200 hover:bg-white/[0.03] transition {{ $pageKey === $slug ? 'is-active' : '' }}"
                    >{{ $meta['nav'] }}</a>
                @endforeach
                <a href="{{ route('home') }}" class="block rounded-lg border border-white/10 px-3 py-2 text-sm text-zinc-500 hover:text-zinc-300 transition mt-3">← Voltar à home</a>
            </aside>

            <article class="rounded-2xl border border-white/8 bg-[rgba(14,18,24,0.88)] px-5 py-6 sm:px-8 sm:py-8 legal-prose">
                @include('legal.pages.'.$pageKey)
                <p class="mt-10 pt-6 border-t border-white/5 text-xs text-zinc-600">
                    Este documento integra o conjunto de políticas do {{ $legal['product_name'] ?? 'MarkCraft' }}.
                    Em caso de conflito entre versões, prevalece o texto publicado nestas páginas na data da última atualização.
                </p>
            </article>
        </div>
    </main>

    @include('partials.site_footer')
    @include('partials.hub_glass_modals')
</body>
</html>
