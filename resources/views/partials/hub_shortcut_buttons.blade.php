@props([
    'variant' => 'grid', // grid | nav | chip
])

@if($variant === 'grid')
    <div {{ $attributes->merge(['class' => 'grid grid-cols-2 gap-3']) }}>
        <button
            type="button"
            @click="openHub('packs')"
            class="mc-shortcut group flex flex-col items-start gap-3 rounded-xl border border-white/10 bg-white/[0.03] p-4 text-left hover:border-amber-400/45"
        >
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-lg border border-amber-400/30 bg-amber-500/15 text-amber-300">
                @include('partials.tool_icon', ['icon' => 'packs', 'size' => 22])
            </span>
            <span>
                <span class="block text-sm font-semibold text-zinc-100 group-hover:text-white">Packs</span>
                <span class="mt-1 block text-xs text-zinc-500 leading-snug">Templates e ofertas afiliadas para importar no Studio.</span>
            </span>
        </button>
        <button
            type="button"
            @click="openHub('apoiar')"
            class="mc-shortcut group flex flex-col items-start gap-3 rounded-xl border border-white/10 bg-white/[0.03] p-4 text-left hover:border-rose-400/45"
        >
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-lg border border-rose-400/30 bg-rose-500/15 text-rose-300">
                @include('partials.tool_icon', ['icon' => 'heart', 'size' => 22])
            </span>
            <span>
                <span class="block text-sm font-semibold text-zinc-100 group-hover:text-white">Apoiar</span>
                <span class="mt-1 block text-xs text-zinc-500 leading-snug">Contribuição opcional a partir de R$ 2 — Pix ou cartão.</span>
            </span>
        </button>
    </div>
@else
    {{-- nav + chip: mesma caixa do botão Studio (mc-nav-action) --}}
    <div {{ $attributes->merge(['class' => 'flex flex-wrap items-center gap-1.5']) }}>
        <button
            type="button"
            @click="openHub('packs')"
            class="mc-nav-action border border-amber-400/25 bg-amber-500/10 text-amber-100 hover:bg-amber-500/15"
            title="Packs"
        >
            <span class="mc-nav-action-icon inline-flex shrink-0 text-amber-300">
                @include('partials.tool_icon', ['icon' => 'packs', 'size' => 16])
            </span>
            Packs
        </button>
        <button
            type="button"
            @click="openHub('apoiar')"
            class="mc-nav-action border border-rose-400/25 bg-rose-500/10 text-rose-100 hover:bg-rose-500/15"
            title="Apoiar"
        >
            <span class="mc-nav-action-icon inline-flex shrink-0 text-rose-300">
                @include('partials.tool_icon', ['icon' => 'heart', 'size' => 16])
            </span>
            Apoiar
        </button>
    </div>
@endif
