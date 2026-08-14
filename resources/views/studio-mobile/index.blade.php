<!DOCTYPE html>
<html lang="pt-BR" class="overflow-x-hidden">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="studio-layout" content="mobile">
    <meta name="studio-catalog-url" content="{{ route('api.image-studio.catalog') }}">
    <meta name="studio-remove-bg-url" content="{{ route('api.image-studio.remove-background') }}">
    <meta name="studio-bg-removal-driver" content="{{ config('image_studio.background_removal.driver', 'imgly') }}">
    <meta name="studio-bg-driver" content="{{ config('image_studio.background_removal.driver', 'imgly') }}">
    @if(!empty($initialPreset))
        <meta name="studio-initial-preset" content="{{ $initialPreset }}">
    @endif
    @php
        $cs = config('image_studio.content_safety', []);
        $csThresholds = json_encode([
            'porn' => (float) ($cs['porn_threshold'] ?? 0.55),
            'hentai' => (float) ($cs['hentai_threshold'] ?? 0.55),
            'combined' => (float) ($cs['combined_threshold'] ?? 0.75),
            'blockSexy' => ! empty($cs['block_sexy']),
        ], JSON_UNESCAPED_UNICODE);
    @endphp
    <meta name="studio-content-safety" content="{{ !empty($cs['enabled']) ? '1' : '0' }}">
    <meta name="studio-content-safety-thresholds" content="{{ $csThresholds }}">
    @include('partials.seo_head', [
        'seo' => [
            'title' => 'Studio — '.config('app.name'),
            'description' => 'Editor MarkCraft (área autenticada).',
            'noindex' => true,
        ],
        'seoJsonLd' => false,
    ])
    @include('partials.head_favicon')
    @include('partials.analytics_head', ['analyticsSurface' => 'studio'])
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=sora:600,700|dm-sans:400,500&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/css/studio-mobile.css', 'resources/css/studio.css', 'resources/js/image-studio/app-studio.js'])
    <style>
        html, body { overflow: hidden !important; max-width: 100% !important; height: 100%; }
        [x-cloak] { display: none !important; }
        body { font-family: 'DM Sans', system-ui, sans-serif; }
        .mc-brand { font-family: 'Sora', system-ui, sans-serif; letter-spacing: -0.03em; }
    </style>
</head>
<body class="sm-body" x-data="markCraftHub">
    @include('partials.analytics_body', ['analyticsSurface' => 'studio'])
    <div class="sm-shell" x-data="markCraftStudio" x-init="init()">
        @include('studio-mobile.partials.header')
        @include('studio-mobile.partials.workspace')
    </div>

    @php $c = $imageStudioCatalog; @endphp
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

    @include('partials.hub_glass_modals', ['showPacks' => true])
</body>
</html>
