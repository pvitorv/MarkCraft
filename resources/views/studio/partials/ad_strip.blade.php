@php
    $showAds = (bool) ($cmsStudio['show_header_ads'] ?? true);
    $slots = ['studio_header_a', 'studio_header_b', 'studio_header_c'];
@endphp

@if($showAds && empty($markcraftDesktop))
    <section class="mc-studio-ad-strip w-full max-w-none px-3 lg:px-5 xl:px-6 shrink-0" aria-label="Publicidade do Studio">
        <div class="mc-studio-ad-strip__row">
            @foreach($slots as $slotKey)
                <x-ad-slot :key="$slotKey" variant="strip" />
            @endforeach
        </div>
    </section>
@endif
