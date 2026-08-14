@php
    $home = $cmsHome ?? [];
    $p = $cmsHostingPartner ?? \App\Support\Cms::defaults()['hosting_partner'] ?? [];
    $url = trim((string) ($p['url'] ?? 'https://hostoo.io/?ref=8pLhQonM'));
    $show = !empty($home['show_hosting_partner']) && !empty($p['enabled']) && $url !== '' && $url !== '#';
    $title = $p['title'] ?? 'Precisa de Hospedagem para Seus Projetos?';
    $blurb = $p['blurb'] ?? 'Hospede seus sites, sistemas e aplicações com alta velocidade, servidores no Brasil e suporte rápido.';
    $cta = $p['cta'] ?? 'Conhecer Planos Hostoo →';
@endphp
@if($show)
<aside class="mx-auto max-w-6xl px-4 pb-8" aria-label="Parceiro Hostoo">
    <div class="h-auto rounded-2xl border border-violet-500/35 bg-neutral-900/80 p-6 sm:p-8 bg-gradient-to-br from-[#635bff]/15 via-neutral-900/90 to-[#070723]">
        <a
            href="{{ $url }}"
            target="_blank"
            rel="sponsored nofollow"
            class="inline-flex items-center"
            aria-label="Hostoo"
        >
            <span class="inline-flex items-center rounded-full border border-[#635bff]/40 bg-[#635bff]/15 px-3 py-1">
                <span class="text-lg font-extrabold tracking-tight text-[#635bff]" style="font-family: 'DM Sans', ui-sans-serif, system-ui, sans-serif;">hostoo</span>
            </span>
        </a>
        <h2 class="mt-4 text-lg sm:text-xl font-bold tracking-tight text-white">{{ $title }}</h2>
        <p class="mt-2 max-w-2xl text-sm leading-relaxed text-neutral-300">{{ $blurb }}</p>
        <a
            href="{{ $url }}"
            target="_blank"
            rel="sponsored nofollow"
            class="mt-5 inline-flex h-auto w-fit items-center rounded-full bg-[#635bff] px-5 py-2.5 text-sm font-semibold text-white shadow-[0_8px_24px_rgba(99,91,255,0.35)] transition hover:bg-[#691aff]"
        >
            {{ $cta }}
        </a>
    </div>
</aside>
@endif
