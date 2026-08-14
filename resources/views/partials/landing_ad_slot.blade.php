@props([
    'key' => 'landing_mid',
])

@php
    $ad = $cmsAds[$key] ?? [];
    $home = $cmsHome ?? [];
    $enabled = (bool) ($ad['enabled'] ?? false);
    $allow = ! empty($home['show_landing_ads']);
    $mode = $ad['mode'] ?? 'placeholder';
    $artImg = \App\Support\Cms::normalizeStoragePath((string) ($ad['art_image'] ?? ''));
@endphp
@if($allow && $enabled)
    <div class="mx-auto max-w-6xl px-4 pb-8" data-ad-slot="{{ $key }}" aria-label="{{ $ad['label'] ?? 'Destaque' }}">
        <div class="overflow-hidden rounded-lg border border-white/10 bg-zinc-950/50 min-h-[90px]">
            @if($mode === 'adsense' && !empty($ad['adsense_client']) && !empty($ad['adsense_slot']))
                <div class="flex min-h-[90px] items-center justify-center">
                    <ins
                        class="adsbygoogle"
                        style="display:block;width:100%;min-height:90px"
                        data-ad-client="{{ $ad['adsense_client'] }}"
                        data-ad-slot="{{ $ad['adsense_slot'] }}"
                    ></ins>
                    <script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
                </div>
            @elseif($mode === 'html' && !empty($ad['html']))
                <div class="w-full">{!! $ad['html'] !!}</div>
            @else
                @if($artImg !== '')
                    <img src="{{ $artImg }}" alt="" class="h-36 w-full object-cover sm:h-40">
                @else
                    <div class="h-24 mc-showcase-mosaic" aria-hidden="true">
                        <span></span><span></span><span></span><span></span><span></span><span></span>
                    </div>
                @endif
                <div class="px-4 py-4 text-center sm:text-left">
                    <p class="mc-brand text-sm font-bold text-white">{{ $ad['art_heading'] ?? 'Arte do portal' }}</p>
                    <p class="mt-1 text-xs text-zinc-400 leading-relaxed">{{ $ad['art_blurb'] ?? '' }}</p>
                </div>
            @endif
        </div>
    </div>
@endif
