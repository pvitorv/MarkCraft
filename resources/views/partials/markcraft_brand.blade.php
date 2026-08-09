@props([
    'href' => null,
    'variant' => 'full', // full | mark
    'badge' => null, // null | criasys | desktop
    'class' => '',
    'markClass' => 'h-8 w-8 sm:h-9 sm:w-9 shrink-0',
    'wordClass' => 'mc-brand text-lg sm:text-xl font-extrabold text-white tracking-tight',
])

@php
    $href = $href ?? (! empty($markcraftDesktop) ? route('studio') : route('home'));
    $badge = $badge ?? (! empty($markcraftDesktop) ? 'desktop' : 'criasys');
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5 shrink-0 relative z-[501] '.$class]) }}>
    @if($variant === 'mark')
        <img
            src="{{ asset('brand/markcraft-mark.svg') }}"
            alt="MarkCraft"
            class="{{ $markClass }}"
            width="36"
            height="36"
            decoding="async"
        >
    @else
        <img
            src="{{ asset('brand/markcraft-logo.svg') }}"
            alt="MarkCraft"
            class="h-8 w-auto sm:h-9 shrink-0"
            width="198"
            height="45"
            decoding="async"
        >
    @endif

    @if($badge === 'desktop')
        @if($variant === 'mark')
            <span class="{{ $wordClass }}">MarkCraft</span>
        @endif
        <span class="ms-0.5 align-middle text-[9px] font-semibold tracking-[0.14em] uppercase text-emerald-300/90 border border-emerald-500/30 px-1.5 py-0.5">Desktop</span>
    @elseif($badge === 'criasys' && $variant === 'mark')
        <span class="{{ $wordClass }}">MarkCraft</span>
        <span class="ms-0.5 align-middle text-[9px] font-semibold tracking-[0.14em] uppercase text-teal-300/90 border border-teal-500/30 px-1.5 py-0.5">CriaSys</span>
    @elseif($badge === 'criasys' && $variant === 'full')
        <span class="ms-0.5 align-middle text-[9px] font-semibold tracking-[0.14em] uppercase text-teal-300/90 border border-teal-500/30 px-1.5 py-0.5">CriaSys</span>
    @endif
</a>
