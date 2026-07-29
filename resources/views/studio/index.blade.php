<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="studio-catalog-url" content="{{ route('api.image-studio.catalog') }}">
    <meta name="studio-remove-bg-url" content="{{ route('api.image-studio.remove-background') }}">
    <meta name="studio-bg-removal-driver" content="{{ config('image_studio.background_removal.driver', 'rembg') }}">
    @if(!empty($initialPreset))
        <meta name="studio-initial-preset" content="{{ $initialPreset }}">
    @endif
    <title>Studio — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=sora:600,700|dm-sans:400,500&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/css/studio.css', 'resources/js/image-studio/app-studio.js'])
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'DM Sans', system-ui, sans-serif; }
        .mc-brand { font-family: 'Sora', system-ui, sans-serif; letter-spacing: -0.03em; }
        .is-ic-fa-solid { font-family: "Font Awesome 6 Free"; font-weight: 900; font-style: normal; -webkit-font-smoothing: antialiased; }
        .is-ic-fa-regular { font-family: "Font Awesome 6 Free"; font-weight: 400; font-style: normal; -webkit-font-smoothing: antialiased; }
        .is-ic-fa-brands { font-family: "Font Awesome 6 Brands"; font-weight: 400; font-style: normal; -webkit-font-smoothing: antialiased; }
        .is-ic-material { font-family: "Material Symbols Outlined"; font-weight: 400; font-style: normal; font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; -webkit-font-smoothing: antialiased; }
    </style>
</head>
<body class="bg-zinc-950 text-zinc-100 min-h-screen" x-data="markCraftHub">
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.getRegistrations().then((regs) => {
                regs.forEach((reg) => reg.unregister());
            }).catch(() => {});
        }
    </script>
    {{-- z-[500] acima de canvas/modais do studio para o navbar não ficar "morto" --}}
    <header class="border-b border-zinc-800 bg-zinc-950/95 sticky top-0 z-[500] backdrop-blur">
        <div class="mx-auto max-w-[1600px] px-3 py-2.5 flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-3 min-w-0">
                <a href="{{ route('home') }}" class="mc-brand text-lg font-bold text-white shrink-0 relative z-[501]">MarkCraft</a>
                <span class="text-[10px] uppercase tracking-wide text-teal-300/90 border border-teal-800/60 px-1.5 py-0.5">CriaSys</span>
                <x-ad-slot slot-id="ad_app_top" class="hidden md:block max-w-sm py-2" />
            </div>
            <nav class="relative z-[501] flex flex-wrap items-center gap-2 text-xs text-zinc-300">
                <a href="{{ route('studio') }}" class="px-2 py-1 rounded bg-zinc-800 text-white">Studio</a>
                <button type="button" @click="openHub('ferramentas')" class="px-2 py-1 rounded hover:bg-zinc-800">Ferramentas</button>
                <button type="button" @click="openHub('packs')" class="px-2 py-1 rounded hover:bg-zinc-800">Packs</button>
                <button type="button" @click="openHub('apoiar')" class="px-2 py-1 rounded hover:bg-zinc-800">Apoiar</button>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="px-2 py-1 rounded hover:bg-zinc-800">Sair</button>
                </form>
            </nav>
        </div>
    </header>

    <div
        class="mx-auto max-w-[1600px] px-3 py-3"
        x-data="markCraftStudio"
        x-init="init()"
    >
        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
            <div>
                <h1 class="mc-brand text-xl font-bold text-white">Monte seu post</h1>
                <p class="text-xs text-zinc-400">
                    Layouts · Pacotes · Elementos · rembg no servidor.
                    Artes não ficam no site — baixe e limpe.
                    <span x-show="imageStudioBgRemovalLabel" x-cloak class="text-zinc-500" x-text="' · ' + imageStudioBgRemovalLabel"></span>
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" @click="imageStudioExport('png')" class="text-xs px-3 py-2 rounded-lg bg-teal-700 hover:bg-teal-600 text-white">Baixar PNG</button>
                <button type="button" @click="imageStudioExport('jpg')" class="text-xs px-3 py-2 rounded-lg bg-zinc-800 hover:bg-zinc-700">Baixar JPG</button>
                <button type="button" @click="clearImageStudioWorkspace()" class="text-xs px-3 py-2 rounded-lg border border-amber-700/60 text-amber-200 hover:bg-amber-950/40">Limpar workspace</button>
            </div>
        </div>

        <p x-show="message" x-cloak x-text="message" class="mb-2 text-xs text-emerald-300"></p>
        <p x-show="error" x-cloak x-text="error" class="mb-2 text-xs text-red-300"></p>

        <div class="flex gap-3 items-start">
            <div class="flex-1 min-w-0">
                @include('studio.partials.image_studio_workspace')
            </div>
            <div class="hidden xl:block w-44 shrink-0 sticky top-16">
                <x-ad-slot slot-id="ad_app_sidebar" class="min-h-[240px]" />
            </div>
        </div>
    </div>

    @php
        $c = $imageStudioCatalog;
    @endphp
    <script type="application/json" id="criasys-image-studio-presets">@json($c['presets'] ?? [])</script>
    <script type="application/json" id="criasys-image-studio-defaults">@json($c['defaults'] ?? [])</script>
    <script type="application/json" id="criasys-image-studio-primary-formats">@json($c['primary_formats'] ?? [])</script>
    <script type="application/json" id="criasys-image-studio-group-order">@json($c['group_order'] ?? [])</script>
    <script type="application/json" id="criasys-image-studio-fonts">@json($c['fonts'] ?? [])</script>
    <script type="application/json" id="criasys-image-studio-icons">@json($c['icon_glyphs'] ?? [])</script>
    <script type="application/json" id="criasys-image-studio-icon-fonts">@json($c['icon_fonts'] ?? [])</script>
    <script type="application/json" id="criasys-image-studio-elements">@json($c['elements'] ?? [])</script>
    <script type="application/json" id="criasys-image-studio-element-groups">@json($c['element_groups'] ?? [])</script>
    <script type="application/json" id="criasys-image-studio-templates">@json($c['templates'] ?? [])</script>
    <script type="application/json" id="criasys-image-studio-packs">@json($c['packs'] ?? [])</script>

    @include('partials.hub_glass_modals')
</body>
</html>
