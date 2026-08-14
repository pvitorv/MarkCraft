@php
    $home = $cmsHome ?? \App\Support\Cms::defaults()['home'];
    $showcase = $home['showcase'] ?? [];
    $inspire = $showcase['inspire'] ?? [];
    $inspireImg = \App\Support\Cms::existingPublicUrl((string) ($inspire['image'] ?? ''));
@endphp

<section class="mx-auto max-w-6xl px-4 pt-4 pb-6 sm:pt-6 sm:pb-8" aria-label="Inspiração e parceiro">
    <div class="grid gap-4 md:grid-cols-2 md:items-stretch">
        <article class="mc-showcase-card overflow-hidden rounded-xl border border-white/10 bg-zinc-950/40">
            <div class="mc-showcase-art relative min-h-[10.5rem] border-b border-white/5">
                @if($inspireImg !== '')
                    <img src="{{ $inspireImg }}" alt="{{ $inspire['title'] ?? 'Inspire-se' }}" class="absolute inset-0 h-full w-full object-cover">
                @else
                    <div class="mc-showcase-mosaic" aria-hidden="true">
                        <span></span><span></span><span></span><span></span><span></span><span></span>
                    </div>
                @endif
            </div>
            <div class="px-4 py-4 sm:px-5">
                <p class="text-[10px] uppercase tracking-[0.16em] text-amber-300/90">{{ $inspire['eyebrow'] ?? 'Inspire-se' }}</p>
                <h2 class="mc-brand mt-1 text-lg font-bold text-white">{{ $inspire['title'] ?? '' }}</h2>
                <p class="mt-2 text-sm text-zinc-400 leading-relaxed">{{ $inspire['text'] ?? '' }}</p>
                <a href="#formatos" class="mt-4 inline-flex text-sm font-semibold text-amber-200 hover:text-amber-100 transition">
                    Abrir formatos no Studio →
                </a>
            </div>
        </article>

        @include('partials.hosting_partner_card', ['variant' => 'compact'])
    </div>
</section>
