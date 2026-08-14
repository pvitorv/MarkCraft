@props([
    'slot' => 'landing_mid',
])

@php
    $promo = \App\Support\Cms::promo($slot);
    $enabled = (bool) ($promo['enabled'] ?? false);
    $url = trim((string) ($promo['url'] ?? ''));
    $hasLink = $url !== '' && $url !== '#' && ! str_starts_with($url, '#');
    $image = \App\Support\Cms::normalizeStoragePath((string) ($promo['image'] ?? ''));
@endphp

@if($enabled)
    <aside
        {{ $attributes->merge([
            'class' => 'mc-promo overflow-hidden rounded-xl border border-white/10 bg-gradient-to-br from-zinc-900/80 via-white/[0.03] to-amber-500/5',
        ]) }}
        data-promo-slot="{{ $slot }}"
        aria-label="{{ $promo['title'] ?? 'Destaque' }}"
    >
        @if($image !== '')
            <img src="{{ $image }}" alt="" class="h-36 w-full object-cover sm:h-44">
        @else
            <div class="h-28 w-full mc-showcase-mosaic" aria-hidden="true">
                <span></span><span></span><span></span><span></span><span></span><span></span>
            </div>
        @endif
        <div class="px-4 py-4 sm:px-5 sm:py-5">
            @if(!empty($promo['eyebrow']))
                <p class="text-[10px] uppercase tracking-[0.16em] text-teal-300/90">{{ $promo['eyebrow'] }}</p>
            @endif
            <p class="mc-brand mt-1 text-base sm:text-lg font-bold text-white">{{ $promo['title'] ?? '' }}</p>
            @if(!empty($promo['blurb']))
                <p class="mt-1.5 text-sm text-zinc-400 leading-snug max-w-2xl">{{ $promo['blurb'] }}</p>
            @endif
            @if($hasLink)
                <a
                    href="{{ $url }}"
                    target="_blank"
                    rel="noopener sponsored nofollow"
                    class="mc-cta mt-3 inline-flex rounded-md bg-teal-500 px-4 py-2 text-sm font-semibold text-zinc-950 hover:bg-teal-400"
                >
                    {{ $promo['cta'] ?? 'Saiba mais' }} →
                </a>
            @endif
        </div>
    </aside>
@endif
