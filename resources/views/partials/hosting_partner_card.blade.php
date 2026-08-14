@php
    $home = $cmsHome ?? [];
    $p = $cmsHostingPartner ?? \App\Support\Cms::defaults()['hosting_partner'] ?? [];
    $url = trim((string) ($p['url'] ?? ''));
    $show = !empty($home['show_hosting_partner']) && !empty($p['enabled']) && $url !== '' && $url !== '#';
@endphp
@if($show)
<aside class="mx-auto max-w-6xl px-4 pb-8" aria-label="{{ $p['title'] ?? 'Parceiro' }}">
    <div class="rounded-xl border border-amber-500/25 bg-gradient-to-br from-amber-500/10 via-white/[0.02] to-teal-500/5 px-5 py-6 sm:px-7">
        <p class="text-[10px] uppercase tracking-[0.16em] text-amber-300/90">Parceiro</p>
        <p class="mc-brand mt-1 text-lg font-bold text-white">{{ $p['title'] ?? 'Hospedagem Recomendada' }}</p>
        <p class="mt-2 max-w-2xl text-sm text-zinc-400 leading-relaxed">{{ $p['blurb'] ?? '' }}</p>
        <a
            href="{{ $url }}"
            target="_blank"
            rel="sponsored nofollow noopener"
            class="mt-4 inline-flex rounded-md bg-amber-400 px-4 py-2.5 text-sm font-semibold text-zinc-950 hover:bg-amber-300"
        >
            {{ $p['cta'] ?? 'Conhecer' }} →
        </a>
    </div>
</aside>
@endif
