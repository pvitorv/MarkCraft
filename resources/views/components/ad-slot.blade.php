@props([
    'key' => 'studio_header_a',
    'variant' => 'mini',
])

@php
    $ad = $cmsAds[$key] ?? [];
    $enabled = (bool) ($ad['enabled'] ?? false);
    $showAds = (bool) ($cmsStudio['show_header_ads'] ?? true);
    $mode = $ad['mode'] ?? 'placeholder';
    $isStrip = $variant === 'strip';
@endphp

@if($showAds && ($isStrip || $enabled))
    <div
        @class([
            'mc-ad-slot overflow-hidden border border-white/10 bg-zinc-950/60',
            'mc-ad-mini shrink-0 rounded-md' => ! $isStrip,
            'mc-ad-strip-slot h-full w-full rounded-lg' => $isStrip,
            'opacity-45' => $isStrip && ! $enabled,
        ])
        data-ad-slot="{{ $key }}"
        aria-label="{{ $ad['label'] ?? 'Publicidade' }}"
        @unless($isStrip)
            style="width:120px;height:40px;max-width:28vw;"
        @endunless
    >
        @if($enabled)
            @if($mode === 'adsense' && !empty($ad['adsense_client']) && !empty($ad['adsense_slot']))
                <ins
                    class="adsbygoogle"
                    style="display:block;width:100%;height:100%"
                    data-ad-client="{{ $ad['adsense_client'] }}"
                    data-ad-slot="{{ $ad['adsense_slot'] }}"
                ></ins>
                <script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
            @elseif($mode === 'html' && !empty($ad['html']))
                <div class="h-full w-full [&>*]:h-full [&>*]:w-full">{!! $ad['html'] !!}</div>
            @else
                <div @class([
                    'flex h-full w-full items-center justify-center px-2 text-center uppercase tracking-wide text-zinc-500',
                    'text-[9px] leading-tight' => ! $isStrip,
                    'text-[11px] sm:text-xs leading-snug' => $isStrip,
                ])>
                    Ad · Google
                </div>
            @endif
        @elseif($isStrip)
            <div class="flex h-full w-full items-center justify-center px-2 text-center text-[11px] sm:text-xs uppercase tracking-wide text-zinc-600">
                Reservado
            </div>
        @endif
    </div>
@endif
