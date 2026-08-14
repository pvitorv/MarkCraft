@props([
    'variant' => 'grid', // grid | nav | chip
    'showPacks' => true,
])

@if($variant === 'grid')
    <div {{ $attributes->merge(['class' => ($showPacks ? 'grid grid-cols-2 gap-3' : 'grid grid-cols-1 sm:grid-cols-2 gap-3')]) }}>
        @if($showPacks)
        <button
            type="button"
            @click="openHub('packs')"
            class="mc-shortcut mc-shortcut-neon-sun group flex flex-col items-start gap-3 rounded-xl p-4 text-left"
        >
            <span class="mc-hub-icon inline-flex h-11 w-11 items-center justify-center rounded-lg border">
                @include('partials.tool_icon', ['icon' => 'packs', 'size' => 22])
            </span>
            <span>
                <span class="mc-shortcut-label block text-sm font-semibold">Packs</span>
                <span class="mc-shortcut-hint mt-1 block text-xs leading-snug">Ofertas e recursos extras da linha CriaSys.</span>
            </span>
        </button>
        @endif
        <button
            type="button"
            @click="openHub('apoiar')"
            class="mc-shortcut mc-shortcut-neon-shock group flex flex-col items-start gap-3 rounded-xl p-4 text-left"
        >
            <span class="mc-hub-icon inline-flex h-11 w-11 items-center justify-center rounded-lg border">
                @include('partials.tool_icon', ['icon' => 'heart', 'size' => 22])
            </span>
            <span>
                <span class="mc-shortcut-label block text-sm font-semibold">Apoiar</span>
                <span class="mc-shortcut-hint mt-1 block text-xs leading-snug">Contribuição opcional a partir de R$ 2 — Pix ou cartão.</span>
            </span>
        </button>
    </div>
@else
    {{-- nav + chip: mesma caixa do botão Studio (mc-nav-action) --}}
    <div {{ $attributes->merge(['class' => 'flex flex-wrap items-center gap-1.5']) }}>
        @if($showPacks)
        <button
            type="button"
            @click="openHub('packs')"
            class="mc-nav-action mc-nav-neon-sun"
            title="Packs"
        >
            <span class="mc-nav-action-icon inline-flex shrink-0">
                @include('partials.tool_icon', ['icon' => 'packs', 'size' => 16])
            </span>
            Packs
        </button>
        @endif
        <button
            type="button"
            @click="openHub('apoiar')"
            class="mc-nav-action mc-nav-neon-shock"
            title="Apoiar"
        >
            <span class="mc-nav-action-icon inline-flex shrink-0">
                @include('partials.tool_icon', ['icon' => 'heart', 'size' => 16])
            </span>
            Apoiar
        </button>
    </div>
@endif
