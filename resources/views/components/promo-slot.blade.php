@props([
    'slot' => 'landing_mid', // chave em config('markcraft.promos')
])

@php
    $promo = config('markcraft.promos.'.$slot, []);
    $enabled = (bool) ($promo['enabled'] ?? false);
    $url = $promo['url'] ?? config('markcraft.blog.url');
@endphp

@if($enabled)
    <aside
        {{ $attributes->merge([
            'class' => 'mc-promo rounded-xl border border-teal-500/25 bg-gradient-to-br from-teal-500/10 via-white/[0.03] to-amber-500/5 px-4 py-4 sm:px-5 sm:py-5',
        ]) }}
        data-promo-slot="{{ $slot }}"
        aria-label="{{ $promo['title'] ?? 'Promo CriaSys' }}"
    >
        @if(!empty($promo['eyebrow']))
            <p class="text-[10px] uppercase tracking-[0.16em] text-teal-300/90">{{ $promo['eyebrow'] }}</p>
        @endif
        <p class="mc-brand mt-1 text-base sm:text-lg font-bold text-white">{{ $promo['title'] ?? '' }}</p>
        @if(!empty($promo['blurb']))
            <p class="mt-1.5 text-sm text-zinc-400 leading-snug max-w-2xl">{{ $promo['blurb'] }}</p>
        @endif
        <a
            href="{{ $url }}"
            target="_blank"
            rel="noopener"
            class="mc-cta mt-3 inline-flex rounded-md bg-teal-500 px-4 py-2 text-sm font-semibold text-zinc-950 hover:bg-teal-400"
        >
            {{ $promo['cta'] ?? 'Saiba mais' }} →
        </a>
    </aside>
@endif
