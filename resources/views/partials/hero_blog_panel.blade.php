{{-- Painel secundário no hero: Blog CriaSys (não disputa com o CTA principal) --}}
@php
    $blog = config('markcraft.blog', []);
    $url = trim((string) ($blog['url'] ?? '#')) ?: '#';
@endphp

<div class="mc-hero-visual mc-rise-2">
    <div class="mc-hero-offer h-full flex flex-col justify-center rounded-xl px-4 py-4 sm:px-5 sm:py-5">
        <p class="text-[10px] uppercase tracking-[0.16em] text-zinc-500 font-semibold">Também na família CriaSys</p>
        <p class="mc-brand mt-1.5 text-base sm:text-lg font-bold text-zinc-100 leading-tight">
            {{ $blog['name'] ?? 'Blog CriaSys Web' }}
        </p>
        <p class="mt-1.5 text-xs sm:text-sm text-zinc-400 leading-snug line-clamp-3">
            {{ $blog['headline'] ?? 'Blog, painel e Image Studio no mesmo fluxo' }}
        </p>
        <a
            href="{{ $url }}"
            class="mc-cta-outline mt-4 inline-flex w-full sm:w-auto justify-center rounded-md px-4 py-2 text-sm font-medium transition"
            @if($url !== '#' && !str_starts_with($url, '#')) target="_blank" rel="noopener" @endif
        >
            Saiba mais sobre o Blog →
        </a>
    </div>
</div>
