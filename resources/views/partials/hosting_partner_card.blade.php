@php
    $home = $cmsHome ?? [];
    $p = $cmsHostingPartner ?? \App\Support\Cms::defaults()['hosting_partner'] ?? [];
    $configured = trim((string) ($p['url'] ?? ''));
    $url = 'https://hostoo.io/?ref=8pLhQonM';
    $show = !empty($home['show_hosting_partner']) && !empty($p['enabled']) && $configured !== '' && $configured !== '#';
    $title = 'Precisa de Hospedagem Rápida para Seus Projetos?';
    $blurb = 'Hospede seus sites, sistemas e aplicações com alta velocidade, servidores SSD no Brasil e suporte 24/7.';
    $cta = 'Conhecer Planos Hostoo →';
    $logo = 'https://hostoo.io/images/logo.png';
@endphp
@if($show)
<article class="h-auto" aria-label="Parceiro Hostoo">
    <div class="flex h-full flex-col justify-between rounded-2xl border border-white/10 bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 p-5 sm:p-6 shadow-xl shadow-indigo-900/20">
        <div>
            <a href="{{ $url }}" target="_blank" rel="sponsored nofollow" class="inline-flex items-center gap-2">
                <img
                    src="{{ $logo }}"
                    alt="Hostoo"
                    width="160"
                    height="40"
                    class="h-8 w-auto max-h-8 object-contain object-left mix-blend-screen"
                    onerror="this.remove()"
                >
                <span class="text-lg font-extrabold tracking-tight text-white" style="font-family: 'DM Sans', ui-sans-serif, system-ui, sans-serif;">hostoo</span>
            </a>
            <p class="mt-3 inline-flex rounded-full border border-white/25 bg-white/10 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.16em] text-white">
                Parceiro oficial de infraestrutura
            </p>
            <h3 class="mt-3 text-xl font-bold leading-snug text-white sm:text-2xl">{{ $title }}</h3>
            <p class="mt-2 text-sm leading-relaxed text-blue-100">{{ $blurb }}</p>
        </div>
        <a
            href="{{ $url }}"
            target="_blank"
            rel="sponsored nofollow"
            class="mt-5 inline-flex w-fit items-center rounded-xl bg-white px-5 py-2.5 text-sm font-bold text-blue-900 shadow-md transition-all hover:bg-blue-50"
        >
            {{ $cta }}
        </a>
    </div>
</article>
@endif
