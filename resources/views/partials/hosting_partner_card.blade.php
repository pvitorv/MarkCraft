@php
    $home = $cmsHome ?? [];
    $p = $cmsHostingPartner ?? \App\Support\Cms::defaults()['hosting_partner'] ?? [];
    $url = trim((string) ($p['url'] ?? ''));
    $show = !empty($home['show_hosting_partner']) && !empty($p['enabled']) && $url !== '' && $url !== '#';
    $title = $p['title'] ?? 'Precisa de Hospedagem para Seus Projetos?';
    $cta = $p['cta'] ?? 'Conhecer Planos Hostoo →';
@endphp
@if($show)
<aside class="mx-auto max-w-6xl px-4 pb-8" aria-label="Parceiro Hostoo">
    <div class="overflow-hidden rounded-xl border border-amber-500/25 bg-gradient-to-br from-amber-500/10 via-zinc-950/80 to-teal-500/10 sm:flex sm:items-stretch">
        <a
            href="{{ $url }}"
            target="_blank"
            rel="sponsored nofollow"
            class="relative block min-h-[8.5rem] sm:w-56 sm:shrink-0 border-b sm:border-b-0 sm:border-r border-white/5"
            aria-label="{{ $title }}"
        >
            <div class="mc-showcase-mosaic absolute inset-0" aria-hidden="true">
                <span></span><span></span><span></span><span></span><span></span><span></span>
            </div>
            <span class="absolute inset-0 flex items-end p-4">
                <span class="rounded-md border border-amber-400/30 bg-black/50 px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.16em] text-amber-200">Hostoo</span>
            </span>
        </a>
        <div class="flex flex-1 flex-col justify-center px-5 py-6 sm:px-7">
            <p class="text-[10px] uppercase tracking-[0.16em] text-amber-300/90">Parceiro de hospedagem</p>
            <h2 class="mc-brand mt-1 text-lg sm:text-xl font-bold text-white">{{ $title }}</h2>
            <p class="mt-2 max-w-2xl text-sm text-zinc-400 leading-relaxed">{{ $p['blurb'] ?? '' }}</p>
            <a
                href="{{ $url }}"
                target="_blank"
                rel="sponsored nofollow"
                class="mt-4 inline-flex w-fit rounded-md bg-amber-400 px-4 py-2.5 text-sm font-semibold text-zinc-950 hover:bg-amber-300 transition"
            >
                {{ $cta }}
            </a>
        </div>
    </div>
</aside>
@endif
