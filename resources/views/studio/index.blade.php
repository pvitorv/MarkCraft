<!DOCTYPE html>
<html lang="pt-BR" class="overflow-x-hidden">
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
        html, body { overflow-x: hidden !important; max-width: 100% !important; }
        .mc-app { width: 100%; max-width: 100%; min-width: 0; overflow-x: hidden; }
        [x-cloak] { display: none !important; }
        body { font-family: 'DM Sans', system-ui, sans-serif; }
        .mc-brand { font-family: 'Sora', system-ui, sans-serif; letter-spacing: -0.03em; }
        .is-ic-fa-solid { font-family: "Font Awesome 6 Free"; font-weight: 900; font-style: normal; -webkit-font-smoothing: antialiased; }
        .is-ic-fa-regular { font-family: "Font Awesome 6 Free"; font-weight: 400; font-style: normal; -webkit-font-smoothing: antialiased; }
        .is-ic-fa-brands { font-family: "Font Awesome 6 Brands"; font-weight: 400; font-style: normal; -webkit-font-smoothing: antialiased; }
        .is-ic-material { font-family: "Material Symbols Outlined"; font-weight: 400; font-style: normal; font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; -webkit-font-smoothing: antialiased; }
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
        .mc-shortcut-neon-shock {
            border: 1px solid rgba(251, 113, 133, 0.75);
            background: rgba(244, 63, 94, 0.1);
            box-shadow:
                0 0 10px rgba(244, 63, 94, 0.4),
                0 0 24px rgba(251, 113, 133, 0.22);
        }
        .mc-shortcut-neon-shock .mc-hub-icon {
            border-color: rgba(244, 63, 94, 0.55);
            background: rgba(244, 63, 94, 0.16);
            color: #fb7185;
        }
        .mc-shortcut-neon-shock .mc-shortcut-label {
            color: #fda4af;
            text-shadow: 0 0 8px rgba(244, 63, 94, 0.4);
        }
    </style>
</head>
<body class="bg-zinc-950 text-zinc-100 min-h-screen overflow-x-hidden max-w-full" x-data="markCraftHub">
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.getRegistrations().then((regs) => {
                regs.forEach((reg) => reg.unregister());
            }).catch(() => {});
        }
    </script>
    <div class="mc-app">
    @include('partials.dark_site_navbar', ['context' => 'studio'])

    <div
        class="mc-app-shell mx-auto max-w-[1600px] px-3 py-3 overflow-x-hidden min-w-0 w-full"
        x-data="markCraftStudio"
        x-init="init()"
    >
        <div class="mb-3 flex flex-col sm:flex-row sm:flex-wrap sm:items-center sm:justify-between gap-3">
            <div class="min-w-0">
                <h1 class="mc-brand text-lg sm:text-xl font-bold text-white">{{ !empty($markcraftDesktop) ? 'Studio local' : 'Monte seu post' }}</h1>
                <p class="text-xs text-zinc-400">
                    @if(!empty($markcraftDesktop))
                        Cópia desktop · Layouts · Pacotes · Sequência · rembg.
                        Exports vão para download ou pasta MarkCraftExports (Electron).
                    @else
                        Studio gratuito CriaSys · Layouts · Pacotes · Elementos · rembg.
                        Artes não ficam no site — baixe e limpe.
                    @endif
                    <span x-show="imageStudioBgRemovalLabel" x-cloak class="text-zinc-500" x-text="' · ' + imageStudioBgRemovalLabel"></span>
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                @unless(!empty($markcraftDesktop))
                    @php
                        $blogUrl = trim((string) config('markcraft.blog.url', '#')) ?: '#';
                        $blogName = config('markcraft.blog.name', 'Blog CriaSys Web');
                    @endphp
                    <a
                        href="{{ $blogUrl }}"
                        class="is-blog-bridge-btn text-xs px-3 py-2 rounded-lg font-semibold"
                        @if($blogUrl !== '#' && !str_starts_with($blogUrl, '#')) target="_blank" rel="noopener" @endif
                    >
                        Usar no {{ $blogName }}
                    </a>
                @endunless
                <button type="button" @click="window.dispatchEvent(new Event('mc-open-credits'))" class="text-xs px-3 py-2 rounded-lg border border-zinc-600 text-zinc-300 hover:bg-zinc-800/80">Créditos</button>
                <button type="button" @click="imageStudioExport('png')" class="text-xs px-3 py-2 rounded-lg bg-teal-700 hover:bg-teal-600 text-white">Baixar PNG</button>
                <button type="button" @click="imageStudioExport('jpg')" class="text-xs px-3 py-2 rounded-lg bg-zinc-800 hover:bg-zinc-700">Baixar JPG</button>
                <button type="button" @click="imageStudioExport('pptx')" class="text-xs px-3 py-2 rounded-lg bg-violet-800 hover:bg-violet-700 text-white">Baixar PPTX</button>
                <button type="button" @click="clearImageStudioWorkspace()" class="text-xs px-3 py-2 rounded-lg border border-amber-700/60 text-amber-200 hover:bg-amber-950/40">Limpar workspace</button>
            </div>
        </div>

        <p x-show="message" x-cloak x-text="message" class="mb-2 text-xs text-emerald-300"></p>
        <p x-show="error" x-cloak x-text="error" class="mb-2 text-xs text-red-300"></p>

        <div class="flex gap-3 items-start min-w-0 w-full overflow-x-hidden">
            <div class="flex-1 min-w-0 overflow-x-hidden">
                @include('studio.partials.image_studio_workspace')
            </div>
            @unless(!empty($markcraftDesktop))
                <div class="hidden xl:block w-52 shrink-0 sticky top-16 space-y-3">
                    <x-promo-slot slot="studio_sidebar" />
                </div>
            @endunless
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
    </div>
</body>
</html>
