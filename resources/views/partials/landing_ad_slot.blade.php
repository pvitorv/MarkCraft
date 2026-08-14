@props([
    'key' => 'landing_mid',
])

@php
    $ad = $cmsAds[$key] ?? [];
    $home = $cmsHome ?? [];
    $enabled = (bool) ($ad['enabled'] ?? false);
    $allow = !empty($home['show_landing_ads']);
    $mode = $ad['mode'] ?? 'placeholder';
@endphp
@if($allow && $enabled)
    <div class="mx-auto max-w-6xl px-4 pb-8" data-ad-slot="{{ $key }}" aria-label="{{ $ad['label'] ?? 'Publicidade' }}">
        <div class="overflow-hidden rounded-lg border border-white/10 bg-zinc-950/50 min-h-[90px] flex items-center justify-center">
            @if($mode === 'adsense' && !empty($ad['adsense_client']) && !empty($ad['adsense_slot']))
                <ins
                    class="adsbygoogle"
                    style="display:block;width:100%;min-height:90px"
                    data-ad-client="{{ $ad['adsense_client'] }}"
                    data-ad-slot="{{ $ad['adsense_slot'] }}"
                ></ins>
                <script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
            @elseif($mode === 'html' && !empty($ad['html']))
                <div class="w-full">{!! $ad['html'] !!}</div>
            @else
                <p class="px-4 py-6 text-center text-[11px] uppercase tracking-wide text-zinc-600">Espaço para anúncio · ative no CMS</p>
            @endif
        </div>
    </div>
@endif
