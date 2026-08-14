@php
    $home = $cmsHome ?? \App\Support\Cms::defaults()['home'];
    $design = $home['showcase']['design'] ?? [];
    $designImg = \App\Support\Cms::publicArt((string) ($design['image'] ?? ''), '/images/portal/inspire-studio.png');
    $show = !empty($home['show_hero_showcase'] ?? true);
@endphp
@if($show)
<section class="mx-auto max-w-6xl px-4 pb-8" aria-label="Dicas de design">
    <article class="mc-showcase-card overflow-hidden rounded-xl border border-white/10 bg-zinc-950/40 sm:flex sm:items-stretch">
        <div class="mc-showcase-art sm:w-64 sm:shrink-0 border-b sm:border-b-0 sm:border-r border-white/5">
            <img src="{{ $designImg }}" alt="{{ $design['title'] ?? 'Domine o Design' }}">
        </div>
        <div class="flex flex-1 flex-col justify-center px-5 py-6 sm:px-8">
            <p class="text-[10px] uppercase tracking-[0.16em] text-rose-300/90">{{ $design['eyebrow'] ?? 'Domine o Design' }}</p>
            <h2 class="mc-brand mt-1 text-lg sm:text-xl font-bold text-white">{{ $design['title'] ?? '' }}</h2>
            <p class="mt-2 max-w-2xl text-sm text-zinc-400 leading-relaxed">{{ $design['text'] ?? '' }}</p>
        </div>
    </article>
</section>
@endif
