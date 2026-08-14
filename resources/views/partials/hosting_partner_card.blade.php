@php
    $home = $cmsHome ?? [];
    $p = $cmsHostingPartner ?? \App\Support\Cms::defaults()['hosting_partner'] ?? [];
    $configured = trim((string) ($p['url'] ?? ''));
    $url = 'https://hostoo.io/?ref=8pLhQonM';
    $show = !empty($home['show_hosting_partner']) && !empty($p['enabled']) && $configured !== '' && $configured !== '#';
    $title = 'Precisa de Hospedagem Rápida para Seus Projetos?';
    $blurb = 'Hospede seus sites, sistemas e aplicações com alta velocidade, servidores SSD no Brasil e suporte 24/7.';
    $cta = 'Conhecer Planos Hostoo →';
@endphp
@if($show)
<aside class="mx-auto max-w-6xl px-4 pt-2 pb-10" aria-label="Parceiro Hostoo">
    <div class="h-auto rounded-2xl border border-white/10 bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 p-8 shadow-xl shadow-indigo-900/20">
        <p class="inline-flex rounded-full border border-white/25 bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-white">
            Parceiro oficial de infraestrutura
        </p>
        <p class="mt-4 text-2xl font-extrabold tracking-tight text-white" style="font-family: 'DM Sans', ui-sans-serif, system-ui, sans-serif;">hostoo</p>
        <h3 class="mt-3 text-2xl md:text-3xl font-bold text-white">{{ $title }}</h3>
        <p class="mt-3 text-blue-100 text-base max-w-2xl">{{ $blurb }}</p>
        <a
            href="{{ $url }}"
            target="_blank"
            rel="sponsored nofollow"
            class="mt-6 inline-flex items-center px-6 py-3 rounded-xl bg-white text-blue-900 font-bold hover:bg-blue-50 transition-all shadow-md"
        >
            {{ $cta }}
        </a>
    </div>
</aside>
@endif
