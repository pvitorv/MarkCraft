<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CMS — {{ config('app.name') }}</title>
    @include('partials.head_favicon')
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=sora:600,700|dm-sans:400,500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'DM Sans', system-ui, sans-serif; background: #090c10; color: #e4e4e7; }
        .mc-brand { font-family: 'Sora', system-ui, sans-serif; letter-spacing: -0.03em; }
        .cms-input { width:100%; border-radius:.55rem; border:1px solid rgba(255,255,255,.12); background:rgba(9,12,16,.75); color:#f4f4f5; padding:.6rem .8rem; font-size:.9rem; }
        .cms-input:focus { outline:none; border-color:rgba(45,212,191,.55); box-shadow:0 0 0 3px rgba(20,184,166,.18); }
        .cms-label { display:block; margin-bottom:.35rem; font-size:.78rem; color:#a1a1aa; font-weight:500; }
        .cms-help { font-size:.75rem; color:#71717a; margin-top:.35rem; line-height:1.4; }
        .cms-card { border:1px solid rgba(255,255,255,.08); background:rgba(18,24,33,.9); border-radius:1rem; padding:1.25rem 1.35rem; }
        .cms-btn { display:inline-flex; align-items:center; justify-content:center; border-radius:.55rem; background:linear-gradient(180deg,#2dd4bf,#0d9488); color:#042f2e; font-weight:700; padding:.65rem 1.1rem; border:0; cursor:pointer; }
        .cms-btn-ghost { display:inline-flex; align-items:center; border-radius:.55rem; border:1px solid rgba(255,255,255,.12); color:#e4e4e7; padding:.55rem .9rem; background:transparent; cursor:pointer; }
        .cms-btn-danger { border-color:rgba(244,63,94,.35); color:#fda4af; }
        .cms-nav-item { display:block; width:100%; text-align:left; border-radius:.65rem; padding:.7rem .85rem; border:1px solid transparent; transition:.15s ease; }
        .cms-nav-item:hover { background:rgba(255,255,255,.04); }
        .cms-nav-item.is-active { border-color:rgba(45,212,191,.35); background:rgba(20,184,166,.12); }
        [x-cloak]{display:none!important}
    </style>
</head>
@php
    $home = $cms['home'] ?? [];
    $footer = $cms['footer'] ?? [];
    $promos = $cms['promos'] ?? [];
    $ads = $cms['ads'] ?? [];
    $studio = $cms['studio'] ?? [];
    $blog = $cms['blog'] ?? [];
@endphp
<body class="antialiased min-h-screen">
    <header class="border-b border-white/5 bg-[#07090c]/95 sticky top-0 z-40 backdrop-blur">
        <div class="mx-auto max-w-6xl px-4 py-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="mc-brand text-xl font-bold text-white">Painel CMS</p>
                <p class="text-xs text-zinc-500">Edite um bloco por vez · salve · veja na home</p>
            </div>
            <div class="flex gap-2 text-sm">
                <a href="{{ route('home') }}" target="_blank" class="cms-btn-ghost">Abrir home</a>
                <a href="{{ route('admin.cms.index', ['tab' => 'landing']) }}#cms-blog-links" class="cms-btn-ghost {{ $activeTab === 'landing' ? 'border-teal-400/40 text-teal-100' : '' }}">Links Blog</a>
                <a href="{{ route('admin.cms.index', ['tab' => 'packs']) }}#cms-packs" class="cms-btn-ghost {{ $activeTab === 'packs' ? 'border-amber-400/40 text-amber-100' : '' }}">Packs</a>
                <a href="{{ route('admin.cms.index', ['tab' => 'donations']) }}#cms-donations" class="cms-btn-ghost {{ $activeTab === 'donations' ? 'border-rose-400/40 text-rose-100' : '' }}">Doações</a>
                <a href="{{ route('admin.cms.index', ['tab' => 'landing']) }}" class="cms-btn-ghost {{ $activeTab === 'landing' ? 'border-teal-400/40 text-teal-100' : '' }}">Landing Blog</a>
                <a href="{{ route('admin.cms.index', ['tab' => 'testimonials']) }}" class="cms-btn-ghost {{ $activeTab === 'testimonials' ? 'border-teal-400/40 text-teal-100' : '' }}">Depoimentos</a>
                <a href="{{ route('studio') }}" class="cms-btn-ghost">Studio</a>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-4 py-6 sm:py-8">
        @if(session('status') === 'cms-saved')
            <div class="mb-5 rounded-lg border border-teal-500/30 bg-teal-500/10 px-4 py-3 text-sm text-teal-100">
                <p class="font-semibold">Salvo com sucesso.</p>
                @if(request('tab') === 'testimonials')
                    @php
                        $pub = (int) session('testimonials_published', 0);
                        $saved = (int) session('testimonials_saved', 0);
                        $errors = (array) session('testimonial_errors', []);
                    @endphp
                    @foreach($errors as $err)
                        <p class="mt-2 text-rose-200/95 font-medium">{{ $err }}</p>
                    @endforeach
                    @if($pub > 0)
                        <p class="mt-1 text-teal-200/90"><strong>{{ $pub }}</strong> depoimento(s) publicado(s) na home. <a href="{{ route('home') }}#prova-social" target="_blank" class="underline font-semibold">Abrir home ↗</a></p>
                    @elseif($saved > 0)
                        <p class="mt-1 text-amber-200/90">Salvou {{ $saved }} depoimento(s), mas <strong>nenhum foi publicado</strong>. Marque <strong>Publicar na home</strong> e envie a imagem (ou nome + texto).</p>
                    @elseif($errors === [])
                        <p class="mt-1 text-amber-200/90">Nada foi salvo — adicione um depoimento e envie pelo menos uma imagem.</p>
                    @endif
                @else
                    <p class="mt-1 text-teal-200/90">Atualize a home com Ctrl+F5 se ela já estiver aberta.</p>
                @endif
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-[240px_minmax(0,1fr)]">
            <aside class="cms-card h-fit lg:sticky lg:top-24 space-y-1">
                <p class="text-[10px] uppercase tracking-[0.14em] text-zinc-500 px-1 mb-2">Seções</p>
                @foreach($tabs as $id => $meta)
                    <a
                        href="{{ route('admin.cms.index', ['tab' => $id]) }}"
                        class="cms-nav-item {{ $activeTab === $id ? 'is-active' : '' }}"
                    >
                        <span class="block text-sm font-semibold text-white">{{ $meta['label'] }}</span>
                        <span class="block text-[11px] text-zinc-500 mt-0.5">{{ $meta['hint'] }}</span>
                    </a>
                @endforeach
            </aside>

            <div class="min-w-0 space-y-4">
                {{-- HOME --}}
                @if($activeTab === 'home')
                <div class="cms-card space-y-4">
                    <div>
                        <h1 class="mc-brand text-lg font-bold text-white">Home</h1>
                        <p class="cms-help">Atalhos de formato, hub e o que aparece na página.</p>
                        <p class="cms-help mt-2 rounded-lg border border-teal-500/30 bg-teal-500/10 px-3 py-2 text-teal-100">
                            <strong>Botões e links do Blog</strong> (hero, ponte, funil) → aba
                            <a href="{{ route('admin.cms.index', ['tab' => 'landing']) }}#cms-blog-links" class="underline font-semibold">Landing Blog</a>
                        </p>
                    </div>
                    <form method="POST" action="{{ route('admin.cms.update') }}" class="space-y-4" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="section" value="home">
                        <div>
                            <label class="cms-label">Texto do hero</label>
                            <textarea class="cms-input" name="hero_blurb" rows="3">{{ $home['hero_blurb'] ?? '' }}</textarea>
                        </div>
                        <input type="hidden" name="hero_title" value="{{ $home['hero_title'] ?? '' }}">
                        <div class="grid sm:grid-cols-2 gap-3">
                            <div>
                                <label class="cms-label">Título dos formatos</label>
                                <input class="cms-input" name="formats_heading" value="{{ $home['formats_heading'] ?? '' }}">
                            </div>
                            <div>
                                <label class="cms-label">Texto dos formatos</label>
                                <input class="cms-input" name="formats_blurb" value="{{ $home['formats_blurb'] ?? '' }}">
                            </div>
                        </div>
                        <div class="grid sm:grid-cols-2 gap-3 text-sm">
                            @foreach([
                                'show_format_shortcuts' => 'Atalhos de formato',
                                'show_hub' => 'Hub de ferramentas',
                                'show_blog_bridge' => 'Ponte Blog CriaSys',
                                'show_landing_promo' => 'Promo no meio da home',
                            ] as $field => $label)
                                <label class="flex items-center gap-2 rounded-lg border border-white/10 px-3 py-2.5 text-zinc-300">
                                    <input type="checkbox" name="{{ $field }}" value="1" @checked(!empty($home[$field]))>
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                        <button type="submit" class="cms-btn">Salvar home</button>
                    </form>
                </div>
                @endif

                @if($activeTab === 'testimonials')
                    @include('admin.cms.partials.testimonials')
                @endif

                @if($activeTab === 'landing')
                    @include('admin.cms.partials.landing')
                @endif

                {{-- FOOTER --}}
                @if($activeTab === 'footer')
                <div class="cms-card space-y-4">
                    <div>
                        <h1 class="mc-brand text-lg font-bold text-white">Rodapé</h1>
                        <p class="cms-help">Redes sociais, portfólio e site pai CriaSys Web.</p>
                    </div>
                    <form method="POST" action="{{ route('admin.cms.update') }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="section" value="footer">
                        <div>
                            <label class="cms-label">Tagline</label>
                            <input class="cms-input" name="tagline" value="{{ $footer['tagline'] ?? '' }}">
                        </div>
                        <div class="grid sm:grid-cols-2 gap-3">
                            <div>
                                <label class="cms-label">Botão portfólio — texto</label>
                                <input class="cms-input" name="portfolio_label" value="{{ $footer['portfolio_label'] ?? 'Portfólio' }}">
                            </div>
                            <div>
                                <label class="cms-label">Botão portfólio — link</label>
                                <input class="cms-input" name="portfolio_url" value="{{ $footer['portfolio_url'] ?? '' }}" placeholder="https://…">
                            </div>
                            <div>
                                <label class="cms-label">CriaSys Web — texto</label>
                                <input class="cms-input" name="criasysweb_label" value="{{ $footer['criasysweb_label'] ?? 'CriaSys Web' }}">
                            </div>
                            <div>
                                <label class="cms-label">CriaSys Web — link</label>
                                <input class="cms-input" name="criasysweb_url" value="{{ $footer['criasysweb_url'] ?? '' }}" placeholder="https://criasysweb.com.br">
                            </div>
                        </div>
                        <p class="text-sm font-semibold text-white">Redes (URL vazia = oculto)</p>
                        @foreach(($footer['socials'] ?? []) as $i => $social)
                            <div class="grid sm:grid-cols-[140px_1fr] gap-2">
                                <input type="hidden" name="socials[{{ $i }}][network]" value="{{ $social['network'] ?? '' }}">
                                <input class="cms-input" name="socials[{{ $i }}][label]" value="{{ $social['label'] ?? '' }}">
                                <input class="cms-input" name="socials[{{ $i }}][url]" value="{{ $social['url'] ?? '' }}" placeholder="https://…">
                            </div>
                        @endforeach
                        <button type="submit" class="cms-btn">Salvar rodapé</button>
                    </form>
                </div>
                @endif

                @if($activeTab === 'packs')
                    @include('admin.cms.partials.packs')
                @endif

                @if($activeTab === 'donations')
                    @include('admin.cms.partials.donations')
                @endif

                {{-- PROMOS --}}
                @if($activeTab === 'promos')
                <div class="cms-card space-y-6">
                    <div>
                        <h1 class="mc-brand text-lg font-bold text-white">Promos</h1>
                        <p class="cms-help">Cards promocionais na home e no Studio. Packs afiliados → aba <a href="{{ route('admin.cms.index', ['tab' => 'packs']) }}#cms-packs" class="text-amber-300 underline">Packs CriaSys</a>. Doações → aba <a href="{{ route('admin.cms.index', ['tab' => 'donations']) }}#cms-donations" class="text-rose-300 underline">Doações</a>.</p>
                    </div>
                    <form method="POST" action="{{ route('admin.cms.update') }}" class="space-y-6">
                        @csrf
                        <input type="hidden" name="section" value="promos">
                        @foreach(['landing_mid' => 'Home · meio', 'studio_top' => 'Studio · topo', 'studio_sidebar' => 'Studio · lateral'] as $slot => $label)
                            @php $p = $promos[$slot] ?? []; @endphp
                            <div class="rounded-xl border border-white/10 p-4 space-y-2">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-sm font-semibold text-white">{{ $label }}</p>
                                    <label class="inline-flex items-center gap-2 text-xs text-zinc-400">
                                        <input type="checkbox" name="promo_{{ $slot }}_enabled" value="1" @checked(!empty($p['enabled']))> Ativo
                                    </label>
                                </div>
                                <input class="cms-input" name="promo_{{ $slot }}_eyebrow" value="{{ $p['eyebrow'] ?? '' }}" placeholder="Eyebrow">
                                <input class="cms-input" name="promo_{{ $slot }}_title" value="{{ $p['title'] ?? '' }}" placeholder="Título">
                                <textarea class="cms-input" name="promo_{{ $slot }}_blurb" rows="2" placeholder="Texto">{{ $p['blurb'] ?? '' }}</textarea>
                                <div class="grid sm:grid-cols-2 gap-2">
                                    <input class="cms-input" name="promo_{{ $slot }}_cta" value="{{ $p['cta'] ?? '' }}" placeholder="Botão">
                                    <input class="cms-input" name="promo_{{ $slot }}_url" value="{{ $p['url'] ?? '' }}" placeholder="Link">
                                </div>
                            </div>
                        @endforeach
                        <button type="submit" class="cms-btn">Salvar promos</button>
                    </form>
                </div>
                @endif

                {{-- ADS --}}
                @if($activeTab === 'ads')
                <div class="cms-card space-y-4">
                    <div>
                        <h1 class="mc-brand text-lg font-bold text-white">Ads do Studio</h1>
                        <p class="cms-help">Três banners retangulares (30% cada · 9% da altura da tela) entre o menu e “Monte seu post”. Use placeholder até ter AdSense.</p>
                    </div>
                    <form method="POST" action="{{ route('admin.cms.update') }}" class="space-y-5">
                        @csrf
                        <input type="hidden" name="section" value="ads">
                        @foreach(['studio_header_a' => 'Banner A', 'studio_header_b' => 'Banner B', 'studio_header_c' => 'Banner C'] as $key => $label)
                            @php $ad = $ads[$key] ?? []; @endphp
                            <div class="rounded-xl border border-white/10 p-4 space-y-2">
                                <div class="flex items-center justify-between">
                                    <p class="font-semibold text-white">{{ $label }}</p>
                                    <label class="inline-flex items-center gap-2 text-xs text-zinc-400">
                                        <input type="checkbox" name="{{ $key }}_enabled" value="1" @checked(!empty($ad['enabled']))> Ativo
                                    </label>
                                </div>
                                <input class="cms-input" name="{{ $key }}_label" value="{{ $ad['label'] ?? '' }}" placeholder="Nome interno">
                                <select class="cms-input" name="{{ $key }}_mode">
                                    @foreach(['placeholder' => 'Reservado (placeholder)', 'adsense' => 'Google AdSense', 'html' => 'HTML custom'] as $mode => $modeLabel)
                                        <option value="{{ $mode }}" @selected(($ad['mode'] ?? '') === $mode)>{{ $modeLabel }}</option>
                                    @endforeach
                                </select>
                                <input class="cms-input" name="{{ $key }}_adsense_client" value="{{ $ad['adsense_client'] ?? '' }}" placeholder="ca-pub-…">
                                <input class="cms-input" name="{{ $key }}_adsense_slot" value="{{ $ad['adsense_slot'] ?? '' }}" placeholder="data-ad-slot">
                                <textarea class="cms-input" name="{{ $key }}_html" rows="2" placeholder="HTML (modo html)">{{ $ad['html'] ?? '' }}</textarea>
                            </div>
                        @endforeach
                        <button type="submit" class="cms-btn">Salvar ads</button>
                    </form>
                </div>
                @endif

                {{-- STUDIO --}}
                @if($activeTab === 'studio')
                <div class="cms-card space-y-4">
                    <div>
                        <h1 class="mc-brand text-lg font-bold text-white">Studio</h1>
                        <p class="cms-help">O que aparece dentro do editor (fora do canvas).</p>
                    </div>
                    <form method="POST" action="{{ route('admin.cms.update') }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="section" value="studio">
                        <label class="flex items-center gap-2 text-sm rounded-lg border border-white/10 px-3 py-2.5"><input type="checkbox" name="show_blog_bridge_btn" value="1" @checked(!empty($studio['show_blog_bridge_btn']))> Botão “Usar no Blog”</label>
                        <label class="flex items-center gap-2 text-sm rounded-lg border border-white/10 px-3 py-2.5"><input type="checkbox" name="show_sidebar_promo" value="1" @checked(!empty($studio['show_sidebar_promo']))> Promo lateral</label>
                        <label class="flex items-center gap-2 text-sm rounded-lg border border-white/10 px-3 py-2.5"><input type="checkbox" name="show_header_ads" value="1" @checked(!empty($studio['show_header_ads']))> Faixa de ads (abaixo do menu)</label>
                        <div>
                            <label class="cms-label">Texto do bloco Blog (aba Exportar)</label>
                            <textarea class="cms-input" name="aside_blog_blurb" rows="3">{{ $studio['aside_blog_blurb'] ?? '' }}</textarea>
                        </div>
                        <button type="submit" class="cms-btn">Salvar Studio</button>
                    </form>
                </div>
                @endif

                {{-- BLOG --}}
                @if($activeTab === 'blog')
                <div class="cms-card space-y-4">
                    <div>
                        <h1 class="mc-brand text-lg font-bold text-white">Blog CriaSys</h1>
                        <p class="cms-help">Nome do produto e blurb do card funil. Links dos botões → aba <a href="{{ route('admin.cms.index', ['tab' => 'landing']) }}#cms-blog-links" class="text-teal-300 underline">Landing Blog</a>.</p>
                    </div>
                    <form method="POST" action="{{ route('admin.cms.update') }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="section" value="blog">
                        <input class="cms-input" name="name" value="{{ $blog['name'] ?? '' }}" placeholder="Nome (ex.: Blog CriaSys Web)">
                        <input class="cms-input" name="eyebrow" value="{{ $blog['eyebrow'] ?? '' }}" placeholder="Eyebrow (opcional)">
                        <input class="cms-input" name="headline" value="{{ $blog['headline'] ?? '' }}" placeholder="Headline card hero">
                        <div>
                            <label class="cms-label">Blurb — parágrafo do card “Família CriaSys” no final da home</label>
                            <textarea class="cms-input" name="blurb" rows="3">{{ $blog['blurb'] ?? '' }}</textarea>
                        </div>
                        <textarea class="cms-input" name="bullets" rows="4" placeholder="Bullets (1 por linha, opcional)">{{ implode("\n", $blog['bullets'] ?? []) }}</textarea>
                        <button type="submit" class="cms-btn">Salvar Blog</button>
                    </form>
                </div>
                @endif
            </div>
        </div>
    </main>
</body>
</html>
