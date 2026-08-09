@php
    $blog = $cmsBlog ?? config('markcraft.blog', []);
    $blogReady = \App\Support\Cms::blogCtaReady($blog);
    $ctaUrl = \App\Support\Cms::blogCtaUrl($blog);
    $ctaLabel = \App\Support\Cms::blogCtaLabel($blog);
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

        @if($blogReady)
            <a
                href="{{ $ctaUrl }}"
                class="mc-cta mc-cta-blog mt-4 inline-flex w-full sm:w-auto justify-center rounded-md px-4 py-2.5 text-sm font-semibold transition"
                target="_blank"
                rel="noopener"
            >
                {{ $ctaLabel }} →
            </a>
        @else
            <a
                href="#blog-criasys"
                class="mc-cta-outline mt-4 inline-flex w-full sm:w-auto justify-center rounded-md px-4 py-2.5 text-sm font-medium transition border-dashed opacity-90"
            >
                {{ $ctaLabel }}
            </a>
            @if(!empty($blog['early_access_note']))
                <p class="mt-2 text-[11px] text-zinc-500 leading-snug">{{ $blog['early_access_note'] }}</p>
            @endif
        @endif
    </div>
</div>
