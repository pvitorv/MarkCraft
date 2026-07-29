@props([
    'size' => 'md', // md | sm — sm só no mobile compacto; navbar desktop usa md
])

@php
    $href = auth()->check() ? route('studio') : route('login');
    // Mesma caixa de Packs / Apoiar (mc-nav-action)
    $box = $size === 'sm'
        ? 'mc-nav-action mc-nav-action--sm'
        : 'mc-nav-action';
@endphp

<a
    href="{{ $href }}"
    {{ $attributes->merge([
        'class' => "mc-studio-neon {$box} text-[#39ff14] transition",
    ]) }}
    @guest
        title="Entre para abrir o Studio"
    @endguest
>
    <span class="mc-nav-action-icon inline-flex shrink-0" aria-hidden="true">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="2" y="3" width="20" height="14" rx="2"/>
            <path d="M8 21h8M12 17v4"/>
        </svg>
    </span>
    Studio
</a>
