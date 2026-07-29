@props([
    'variant' => 'grid', // grid | nav | chip
])

@php
    $tools = config('markcraft.tools', []);
@endphp

@if($variant === 'grid')
    <div {{ $attributes->merge(['class' => 'grid grid-cols-2 sm:grid-cols-4 gap-3']) }}>
        @foreach($tools as $slug => $tool)
            <button
                type="button"
                @click="openTool('{{ $slug }}')"
                class="mc-shortcut group flex flex-col items-start gap-3 rounded-xl border border-white/10 bg-white/[0.03] p-4 text-left hover:border-teal-400/40"
            >
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-white/10 bg-teal-500/10 text-teal-300">
                    @include('partials.tool_icon', ['icon' => $tool['icon'] ?? 'link'])
                </span>
                <span>
                    <span class="block text-sm font-semibold text-zinc-100 group-hover:text-white">{{ $tool['name'] }}</span>
                    <span class="mt-1 block text-xs text-zinc-500 leading-snug">{{ $tool['blurb'] }}</span>
                </span>
            </button>
        @endforeach
    </div>
@elseif($variant === 'nav')
    <div {{ $attributes->merge(['class' => 'flex flex-wrap items-center gap-1']) }}>
        @foreach($tools as $slug => $tool)
            <button
                type="button"
                @click="openTool('{{ $slug }}')"
                class="inline-flex items-center gap-1.5 px-2 py-1 rounded hover:bg-zinc-800 text-zinc-300 hover:text-white"
                title="{{ $tool['name'] }}"
            >
                <span class="inline-flex h-4 w-4 shrink-0 text-teal-300/90">
                    @include('partials.tool_icon', ['icon' => $tool['icon'] ?? 'link', 'size' => 16])
                </span>
                <span class="hidden lg:inline">{{ $tool['short'] ?? $tool['name'] }}</span>
            </button>
        @endforeach
    </div>
@else
    <div {{ $attributes->merge(['class' => 'flex flex-wrap gap-2']) }}>
        @foreach($tools as $slug => $tool)
            <button
                type="button"
                @click="openTool('{{ $slug }}')"
                class="mc-shortcut inline-flex items-center gap-2 rounded-lg border border-white/10 bg-white/[0.03] px-3.5 py-2.5 text-sm font-medium text-zinc-100"
                title="{{ $tool['blurb'] }}"
            >
                <span class="inline-flex h-5 w-5 text-teal-300">
                    @include('partials.tool_icon', ['icon' => $tool['icon'] ?? 'link', 'size' => 18])
                </span>
                {{ $tool['short'] ?? $tool['name'] }}
            </button>
        @endforeach
    </div>
@endif
