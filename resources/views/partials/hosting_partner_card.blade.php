@props([
    'variant' => 'banner', // banner | compact
])

@php
    $home = $cmsHome ?? [];
    $p = $cmsHostingPartner ?? \App\Support\Cms::defaults()['hosting_partner'] ?? [];
    $url = trim((string) ($p['url'] ?? ''));
    $show = !empty($home['show_hosting_partner']) && !empty($p['enabled']) && $url !== '' && $url !== '#';
    $title = $p['title'] ?? 'Precisa de Hospedagem para Seus Projetos?';
    $cta = $p['cta'] ?? 'Conhecer Planos Hostoo →';
    $logoLocal = public_path('images/hostoo-logo.png');
    $markLocal = public_path('images/hostoo-mark.png');
    $logo = is_file($logoLocal) ? asset('images/hostoo-logo.png') : 'https://hostoo.io/images/logo.png';
    $mark = is_file($markLocal) ? asset('images/hostoo-mark.png') : 'https://hostoo.io/images/favicon-192.png';
    $compact = $variant === 'compact';
@endphp
@if($show)
<style>
    .mc-hostoo-card {
        font-family: 'DM Sans', ui-sans-serif, system-ui, sans-serif;
        --hostoo-violet: #635bff;
        --hostoo-violet-deep: #691aff;
        --hostoo-ink: #070723;
        --hostoo-ink-2: #0d061b;
    }
    .mc-hostoo-card__panel {
        overflow: hidden;
        border-radius: 1rem;
        border: 1px solid rgba(99, 91, 255, 0.38);
        background:
            radial-gradient(120% 80% at 0% 0%, rgba(99, 91, 255, 0.22), transparent 55%),
            radial-gradient(90% 70% at 100% 100%, rgba(36, 219, 131, 0.12), transparent 50%),
            linear-gradient(165deg, var(--hostoo-ink) 0%, var(--hostoo-ink-2) 55%, #110a26 100%);
        box-shadow: 0 0 0 1px rgba(7, 7, 35, 0.8), 0 18px 40px rgba(99, 91, 255, 0.12);
        height: 100%;
    }
    .mc-hostoo-card__brand { background: #000; }
    .mc-hostoo-card__cta {
        background: var(--hostoo-violet);
        color: #fff;
        border-radius: 999px;
        box-shadow: 0 8px 24px rgba(99, 91, 255, 0.35);
    }
    .mc-hostoo-card__cta:hover {
        background: var(--hostoo-violet-deep);
        color: #fff;
    }
</style>
@if($compact)
<article class="mc-hostoo-card h-full" aria-label="Parceiro Hostoo">
    <div class="mc-hostoo-card__panel flex flex-col">
        <a
            href="{{ $url }}"
            target="_blank"
            rel="sponsored nofollow"
            class="mc-hostoo-card__brand flex items-center justify-center px-6 py-8 min-h-[10.5rem]"
        >
            <img
                src="{{ $logo }}"
                alt="Logo Hostoo"
                width="512"
                height="512"
                class="h-auto w-full max-w-[13rem] object-contain"
            >
        </a>
        <div class="flex flex-1 flex-col justify-center gap-2 px-4 py-4 sm:px-5">
            <a href="{{ $url }}" target="_blank" rel="sponsored nofollow" class="inline-flex w-fit items-center gap-2">
                <img src="{{ $mark }}" alt="Hostoo" width="32" height="32" class="h-8 w-8 object-contain">
                <span class="text-sm font-bold tracking-tight text-[#635bff]">hostoo</span>
            </a>
            <h2 class="text-lg font-bold tracking-tight text-white">{{ $title }}</h2>
            <p class="text-sm leading-relaxed text-[#cbd5df]">{{ $p['blurb'] ?? '' }}</p>
            <a
                href="{{ $url }}"
                target="_blank"
                rel="sponsored nofollow"
                class="mc-hostoo-card__cta mt-2 inline-flex w-fit items-center px-5 py-2.5 text-sm font-semibold transition"
            >
                {{ $cta }}
            </a>
        </div>
    </div>
</article>
@else
<aside class="mc-hostoo-card mx-auto max-w-6xl px-4 pb-8" aria-label="Parceiro Hostoo">
    <div class="mc-hostoo-card__panel sm:flex sm:items-stretch">
        <a
            href="{{ $url }}"
            target="_blank"
            rel="sponsored nofollow"
            class="mc-hostoo-card__brand flex items-center justify-center px-5 py-6 sm:w-72 sm:shrink-0"
        >
            <img src="{{ $logo }}" alt="Logo Hostoo" width="512" height="512" class="h-auto w-full max-w-[14rem] object-contain">
        </a>
        <div class="flex flex-1 flex-col justify-center gap-3 px-5 py-6 sm:px-8">
            <a href="{{ $url }}" target="_blank" rel="sponsored nofollow" class="inline-flex w-fit items-center gap-3">
                <img src="{{ $mark }}" alt="Hostoo" width="48" height="48" class="h-11 w-11 object-contain">
                <span class="text-sm font-bold tracking-tight text-[#635bff]">hostoo</span>
            </a>
            <h2 class="text-lg sm:text-xl font-bold tracking-tight text-white">{{ $title }}</h2>
            <p class="max-w-2xl text-sm leading-relaxed text-[#cbd5df]">{{ $p['blurb'] ?? '' }}</p>
            <a href="{{ $url }}" target="_blank" rel="sponsored nofollow" class="mc-hostoo-card__cta mt-1 inline-flex w-fit items-center px-5 py-2.5 text-sm font-semibold transition">
                {{ $cta }}
            </a>
        </div>
    </div>
</aside>
@endif
@endif
