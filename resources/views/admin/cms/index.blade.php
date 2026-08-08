<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CMS — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=sora:600,700|dm-sans:400,500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'DM Sans', system-ui, sans-serif; background: #090c10; color: #e4e4e7; }
        .mc-brand { font-family: 'Sora', system-ui, sans-serif; letter-spacing: -0.03em; }
        .cms-input { width:100%; border-radius:.5rem; border:1px solid rgba(255,255,255,.12); background:rgba(9,12,16,.7); color:#f4f4f5; padding:.55rem .75rem; font-size:.9rem; }
        .cms-label { display:block; margin-bottom:.3rem; font-size:.75rem; color:#a1a1aa; font-weight:500; }
        .cms-card { border:1px solid rgba(255,255,255,.08); background:rgba(18,24,33,.85); border-radius:1rem; padding:1.25rem; }
        .cms-btn { display:inline-flex; border-radius:.5rem; background:linear-gradient(180deg,#2dd4bf,#0d9488); color:#042f2e; font-weight:700; padding:.6rem 1rem; border:0; cursor:pointer; }
        [x-cloak]{display:none!important}
    </style>
</head>
<body class="antialiased min-h-screen" x-data="{ tab: '{{ request('tab', 'home') }}' }">
    <header class="border-b border-white/5 bg-[#07090c]/95">
        <div class="mx-auto max-w-5xl px-4 py-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="mc-brand text-xl font-bold text-white">Painel CMS</p>
                <p class="text-xs text-zinc-500">Home · rodapé · ads · afiliados · Studio</p>
            </div>
            <div class="flex gap-2 text-sm">
                <a href="{{ route('home') }}" class="rounded-md border border-white/10 px-3 py-1.5 text-zinc-300 hover:bg-white/5">Ver home</a>
                <a href="{{ route('studio') }}" class="rounded-md border border-white/10 px-3 py-1.5 text-zinc-300 hover:bg-white/5">Studio</a>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-5xl px-4 py-8">
        @if(session('status') === 'cms-saved')
            <div class="mb-4 rounded-lg border border-teal-500/30 bg-teal-500/10 px-3 py-2 text-sm text-teal-100">Alterações salvas.</div>
        @endif

        <div class="flex flex-wrap gap-2 mb-6">
            @foreach($tabs as $id => $label)
                <button type="button" class="rounded-md px-3 py-1.5 text-sm border transition"
                    :class="tab==='{{ $id }}' ? 'border-teal-400/50 bg-teal-500/15 text-teal-100' : 'border-white/10 text-zinc-400 hover:bg-white/5'"
                    @click="tab='{{ $id }}'">{{ $label }}</button>
            @endforeach
        </div>

        @php
            $home = $cms['home'] ?? [];
            $footer = $cms['footer'] ?? [];
            $promos = $cms['promos'] ?? [];
            $ads = $cms['ads'] ?? [];
            $studio = $cms['studio'] ?? [];
            $blog = $cms['blog'] ?? [];
            $packs = $cms['affiliate_packs'] ?? [];
            $donations = $cms['donations'] ?? [];
        @endphp

        {{-- HOME --}}
        <div x-show="tab==='home'" x-cloak class="cms-card space-y-4">
            <form method="POST" action="{{ route('admin.cms.update') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="section" value="home">
                <div>
                    <label class="cms-label">Título hero (legado visual; blurb abaixo é o texto principal)</label>
                    <input class="cms-input" name="hero_title" value="{{ $home['hero_title'] ?? '' }}">
                </div>
                <div>
                    <label class="cms-label">Texto do hero</label>
                    <textarea class="cms-input" name="hero_blurb" rows="3">{{ $home['hero_blurb'] ?? '' }}</textarea>
                </div>
                <div>
                    <label class="cms-label">Título da seção de formatos</label>
                    <input class="cms-input" name="formats_heading" value="{{ $home['formats_heading'] ?? '' }}">
                </div>
                <div>
                    <label class="cms-label">Texto da seção de formatos</label>
                    <input class="cms-input" name="formats_blurb" value="{{ $home['formats_blurb'] ?? '' }}">
                </div>
                <div class="grid sm:grid-cols-2 gap-3 text-sm">
                    @foreach([
                        'show_format_shortcuts' => 'Mostrar atalhos de formato',
                        'show_hub' => 'Mostrar hub ferramentas/packs',
                        'show_blog_bridge' => 'Mostrar ponte Blog',
                        'show_landing_promo' => 'Mostrar promo mid da home',
                    ] as $field => $label)
                        <label class="flex items-center gap-2 text-zinc-300">
                            <input type="checkbox" name="{{ $field }}" value="1" @checked(!empty($home[$field]))>
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
                <button type="submit" class="cms-btn">Salvar home</button>
            </form>
        </div>

        {{-- FOOTER --}}
        <div x-show="tab==='footer'" x-cloak class="cms-card space-y-4">
            <form method="POST" action="{{ route('admin.cms.update') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="section" value="footer">
                <div>
                    <label class="cms-label">Tagline</label>
                    <input class="cms-input" name="tagline" value="{{ $footer['tagline'] ?? '' }}">
                </div>
                <div class="grid sm:grid-cols-2 gap-3">
                    <div>
                        <label class="cms-label">Label portfólio</label>
                        <input class="cms-input" name="portfolio_label" value="{{ $footer['portfolio_label'] ?? 'Portfólio' }}">
                    </div>
                    <div>
                        <label class="cms-label">URL portfólio</label>
                        <input class="cms-input" name="portfolio_url" value="{{ $footer['portfolio_url'] ?? '' }}" placeholder="https://...">
                    </div>
                    <div>
                        <label class="cms-label">Label CriaSys Web</label>
                        <input class="cms-input" name="criasysweb_label" value="{{ $footer['criasysweb_label'] ?? 'CriaSys Web' }}">
                    </div>
                    <div>
                        <label class="cms-label">URL CriaSys Web (site pai)</label>
                        <input class="cms-input" name="criasysweb_url" value="{{ $footer['criasysweb_url'] ?? '' }}" placeholder="https://criasysweb.com.br">
                    </div>
                </div>
                <p class="text-xs text-zinc-500">Redes sociais — deixe URL vazia para ocultar</p>
                @foreach(($footer['socials'] ?? []) as $i => $social)
                    <div class="grid sm:grid-cols-3 gap-2">
                        <input type="hidden" name="socials[{{ $i }}][network]" value="{{ $social['network'] ?? '' }}">
                        <input class="cms-input" name="socials[{{ $i }}][label]" value="{{ $social['label'] ?? '' }}" placeholder="Label">
                        <input class="cms-input sm:col-span-2" name="socials[{{ $i }}][url]" value="{{ $social['url'] ?? '' }}" placeholder="https://...">
                    </div>
                @endforeach
                <button type="submit" class="cms-btn">Salvar rodapé</button>
            </form>
        </div>

        {{-- PROMOS / PACKS / DOAÇÕES --}}
        <div x-show="tab==='promos'" x-cloak class="cms-card space-y-6">
            <form method="POST" action="{{ route('admin.cms.update') }}" class="space-y-6">
                @csrf
                <input type="hidden" name="section" value="promos">
                @foreach(['landing_mid' => 'Home mid', 'studio_top' => 'Studio top', 'studio_sidebar' => 'Studio sidebar'] as $slot => $label)
                    @php $p = $promos[$slot] ?? []; @endphp
                    <div class="rounded-lg border border-white/10 p-4 space-y-2">
                        <p class="text-sm font-semibold text-white">{{ $label }} <span class="text-zinc-500">({{ $slot }})</span></p>
                        <label class="flex items-center gap-2 text-sm text-zinc-300">
                            <input type="checkbox" name="promo_{{ $slot }}_enabled" value="1" @checked(!empty($p['enabled']))> Ativo
                        </label>
                        <input class="cms-input" name="promo_{{ $slot }}_eyebrow" value="{{ $p['eyebrow'] ?? '' }}" placeholder="Eyebrow">
                        <input class="cms-input" name="promo_{{ $slot }}_title" value="{{ $p['title'] ?? '' }}" placeholder="Título">
                        <textarea class="cms-input" name="promo_{{ $slot }}_blurb" rows="2" placeholder="Texto">{{ $p['blurb'] ?? '' }}</textarea>
                        <div class="grid sm:grid-cols-2 gap-2">
                            <input class="cms-input" name="promo_{{ $slot }}_cta" value="{{ $p['cta'] ?? '' }}" placeholder="CTA">
                            <input class="cms-input" name="promo_{{ $slot }}_url" value="{{ $p['url'] ?? '' }}" placeholder="URL afiliado/destino">
                        </div>
                    </div>
                @endforeach

                <p class="text-sm font-semibold text-white">Packs / afiliados (modal Packs)</p>
                @foreach(array_pad($packs, 3, ['title'=>'','blurb'=>'','affiliate_url'=>'','tag'=>'']) as $i => $pack)
                    <div class="grid sm:grid-cols-2 gap-2 rounded-lg border border-white/5 p-3">
                        <input class="cms-input" name="packs[{{ $i }}][tag]" value="{{ $pack['tag'] ?? '' }}" placeholder="Tag">
                        <input class="cms-input" name="packs[{{ $i }}][title]" value="{{ $pack['title'] ?? '' }}" placeholder="Título">
                        <input class="cms-input sm:col-span-2" name="packs[{{ $i }}][blurb]" value="{{ $pack['blurb'] ?? '' }}" placeholder="Descrição">
                        <input class="cms-input sm:col-span-2" name="packs[{{ $i }}][affiliate_url]" value="{{ $pack['affiliate_url'] ?? '' }}" placeholder="URL afiliado">
                    </div>
                @endforeach

                <p class="text-sm font-semibold text-white">Doações (modal Apoiar)</p>
                <div class="grid sm:grid-cols-3 gap-2">
                    <input class="cms-input" name="donation_min_brl" value="{{ $donations['min_brl'] ?? 2 }}" placeholder="Mín R$">
                    <input class="cms-input" name="donation_pix_key" value="{{ $donations['pix_key'] ?? '' }}" placeholder="Chave Pix">
                    <input class="cms-input" name="donation_gateway_url" value="{{ $donations['gateway_url'] ?? '' }}" placeholder="URL gateway">
                </div>
                <button type="submit" class="cms-btn">Salvar promos e afiliados</button>
            </form>
        </div>

        {{-- ADS --}}
        <div x-show="tab==='ads'" x-cloak class="cms-card space-y-4">
            <p class="text-sm text-zinc-400">Dois mini banners no cabeçalho do Image Studio (≈120×40). Use placeholder até colar AdSense, ou HTML custom.</p>
            <form method="POST" action="{{ route('admin.cms.update') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="section" value="ads">
                @foreach(['studio_header_a' => 'Ad A (esquerda)', 'studio_header_b' => 'Ad B (direita)'] as $key => $label)
                    @php $ad = $ads[$key] ?? []; @endphp
                    <div class="rounded-lg border border-white/10 p-4 space-y-2">
                        <p class="font-semibold text-white">{{ $label }}</p>
                        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="{{ $key }}_enabled" value="1" @checked(!empty($ad['enabled']))> Ativo</label>
                        <input class="cms-input" name="{{ $key }}_label" value="{{ $ad['label'] ?? '' }}" placeholder="Label interno">
                        <select class="cms-input" name="{{ $key }}_mode">
                            @foreach(['placeholder','adsense','html'] as $mode)
                                <option value="{{ $mode }}" @selected(($ad['mode'] ?? '') === $mode)>{{ $mode }}</option>
                            @endforeach
                        </select>
                        <input class="cms-input" name="{{ $key }}_adsense_client" value="{{ $ad['adsense_client'] ?? '' }}" placeholder="ca-pub-...">
                        <input class="cms-input" name="{{ $key }}_adsense_slot" value="{{ $ad['adsense_slot'] ?? '' }}" placeholder="data-ad-slot">
                        <textarea class="cms-input" name="{{ $key }}_html" rows="3" placeholder="HTML custom (modo html)">{{ $ad['html'] ?? '' }}</textarea>
                    </div>
                @endforeach
                <button type="submit" class="cms-btn">Salvar ads</button>
            </form>
        </div>

        {{-- STUDIO --}}
        <div x-show="tab==='studio'" x-cloak class="cms-card space-y-4">
            <form method="POST" action="{{ route('admin.cms.update') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="section" value="studio">
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="show_blog_bridge_btn" value="1" @checked(!empty($studio['show_blog_bridge_btn']))> Botão “Usar no Blog”</label>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="show_sidebar_promo" value="1" @checked(!empty($studio['show_sidebar_promo']))> Promo lateral</label>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="show_header_ads" value="1" @checked(!empty($studio['show_header_ads']))> Mini ads no header</label>
                <div>
                    <label class="cms-label">Texto do bloco Blog no painel Exportar</label>
                    <textarea class="cms-input" name="aside_blog_blurb" rows="3">{{ $studio['aside_blog_blurb'] ?? '' }}</textarea>
                </div>
                <button type="submit" class="cms-btn">Salvar Studio</button>
            </form>
        </div>

        {{-- BLOG --}}
        <div x-show="tab==='blog'" x-cloak class="cms-card space-y-4">
            <form method="POST" action="{{ route('admin.cms.update') }}" class="space-y-3">
                @csrf
                <input type="hidden" name="section" value="blog">
                <input class="cms-input" name="name" value="{{ $blog['name'] ?? '' }}" placeholder="Nome">
                <input class="cms-input" name="eyebrow" value="{{ $blog['eyebrow'] ?? '' }}" placeholder="Eyebrow">
                <input class="cms-input" name="headline" value="{{ $blog['headline'] ?? '' }}" placeholder="Headline">
                <textarea class="cms-input" name="blurb" rows="3" placeholder="Blurb">{{ $blog['blurb'] ?? '' }}</textarea>
                <input class="cms-input" name="cta" value="{{ $blog['cta'] ?? '' }}" placeholder="CTA">
                <input class="cms-input" name="url" value="{{ $blog['url'] ?? '' }}" placeholder="URL">
                <input class="cms-input" name="register_url" value="{{ $blog['register_url'] ?? '' }}" placeholder="URL cadastro">
                <input class="cms-input" name="early_access_note" value="{{ $blog['early_access_note'] ?? '' }}" placeholder="Nota early access">
                <textarea class="cms-input" name="bullets" rows="4" placeholder="Bullets (1 por linha)">{{ implode("\n", $blog['bullets'] ?? []) }}</textarea>
                <button type="submit" class="cms-btn">Salvar Blog / CriaSys</button>
            </form>
        </div>
    </main>
</body>
</html>
