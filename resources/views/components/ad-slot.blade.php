@props([
    'key' => 'studio_header_a',
])

@php
    $ad = $cmsAds[$key] ?? [];
    $enabled = (bool) ($ad['enabled'] ?? false);
    $showAds = (bool) ($cmsStudio['show_header_ads'] ?? true);
    $mode = $ad['mode'] ?? 'placeholder';
@endphp

@if($enabled && $showAds)
    <div
        class="mc-ad-mini shrink-0 overflow-hidden rounded-md border border-white/10 bg-zinc-950/60"
        data-ad-slot="{{ $key }}"
        aria-label="{{ $ad['label'] ?? 'Publicidade' }}"
        style="width:120px;height:40px;max-width:28vw;"
    >
        @if($mode === 'adsense' && !empty($ad['adsense_client']) && !empty($ad['adsense_slot']))
            <ins
                class="adsbygoogle"
                style="display:inline-block;width:120px;height:40px"
                data-ad-client="{{ $ad['adsense_client'] }}"
                data-ad-slot="{{ $ad['adsense_slot'] }}"
            ></ins>
            <script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
        @elseif($mode === 'html' && !empty($ad['html']))
            {!! $ad['html'] !!}
        @else
            <div class="flex h-full w-full items-center justify-center px-1 text-center text-[9px] leading-tight text-zinc-500 uppercase tracking-wide">
                Ad · Google
            </div>
        @endif
    </div>
@endif
