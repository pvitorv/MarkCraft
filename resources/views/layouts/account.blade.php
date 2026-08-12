<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.seo_head', [
        'seo' => [
            'title' => trim($__env->yieldContent('title', 'Minha conta')).' — '.config('app.name', 'MarkCraft'),
            'description' => 'Gerencie sua conta MarkCraft.',
            'noindex' => true,
        ],
        'seoJsonLd' => false,
    ])
    @include('partials.head_favicon')
    @include('partials.analytics_head', ['analyticsSurface' => 'auth'])
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=sora:600,700,800|dm-sans:400,500,600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --mc-auth-bg: #090c10;
            --mc-auth-panel: rgba(18, 24, 33, 0.88);
            --mc-auth-line: rgba(255, 255, 255, 0.1);
        }
        body.mc-account-body {
            font-family: 'DM Sans', ui-sans-serif, system-ui, sans-serif;
            color: #e4e4e7;
            background:
                radial-gradient(900px 480px at 12% -8%, rgba(20, 184, 166, 0.18), transparent 55%),
                radial-gradient(700px 420px at 100% 8%, rgba(245, 158, 11, 0.08), transparent 50%),
                var(--mc-auth-bg);
            min-height: 100vh;
        }
        .mc-brand {
            font-family: 'Sora', ui-sans-serif, system-ui, sans-serif;
            letter-spacing: -0.03em;
        }
        .mc-account-card {
            background: var(--mc-auth-panel);
            border: 1px solid var(--mc-auth-line);
            backdrop-filter: blur(16px);
            box-shadow: 0 20px 48px rgba(0, 0, 0, 0.35);
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
            border-radius: 0.65rem;
            background: linear-gradient(180deg, #2dd4bf 0%, #0d9488 100%);
            color: #042f2e;
            font-weight: 700;
            font-size: 0.9rem;
            padding: 0.65rem 1.1rem;
            border: none;
            cursor: pointer;
            transition: transform 0.15s ease, filter 0.15s ease;
        }
        .mc-auth-btn:hover {
            filter: brightness(1.06);
            transform: translateY(-1px);
        }
        .mc-auth-btn--ghost {
            background: transparent;
            color: #d4d4d8;
            border: 1px solid rgba(255, 255, 255, 0.12);
        }
        .mc-auth-btn--ghost:hover {
            background: rgba(255, 255, 255, 0.05);
            filter: none;
            transform: none;
        }
        .mc-auth-btn--danger {
            background: linear-gradient(180deg, #fb7185 0%, #e11d48 100%);
            color: #fff1f2;
        }
        .mc-auth-link {
            color: #5eead4;
            text-decoration: none;
            font-size: 0.875rem;
        }
        .mc-auth-link:hover { color: #99f6e4; text-decoration: underline; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="mc-account-body antialiased" x-data="markCraftHub">
    @include('partials.analytics_body', ['analyticsSurface' => 'auth'])
    @include('partials.dark_site_navbar', ['context' => 'landing'])

    <main class="relative z-10 mx-auto w-full max-w-2xl px-4 py-8 sm:py-10">
        @yield('content')
    </main>

    @include('partials.hub_glass_modals')
</body>
</html>
