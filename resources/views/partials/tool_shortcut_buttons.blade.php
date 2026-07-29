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
                class="mc-shortcut mc-shortcut-neon-blue group flex flex-col items-start gap-3.5 rounded-xl p-4 sm:p-5 text-left min-h-[8.5rem]"
            >
                <span class="mc-hub-icon inline-flex h-11 w-11 items-center justify-center rounded-lg border shrink-0">
                    @include('partials.tool_icon', ['icon' => $tool['icon'] ?? 'link', 'size' => 22])
                </span>
                <span class="min-w-0">
                    <span class="mc-shortcut-label block text-sm sm:text-[0.95rem] font-semibold leading-snug">{{ $tool['name'] }}</span>
                    <span class="mc-shortcut-hint mt-1.5 block text-xs leading-snug">{{ $tool['blurb'] }}</span>
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
                <span class="inline-flex h-4 w-4 shrink-0 text-sky-300/90">
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
                class="mc-shortcut mc-shortcut-neon-blue inline-flex items-center gap-2 rounded-lg px-3.5 py-2.5 text-sm font-medium"
                title="{{ $tool['blurb'] }}"
            >
                <span class="mc-hub-icon inline-flex h-5 w-5 items-center justify-center">
                    @include('partials.tool_icon', ['icon' => $tool['icon'] ?? 'link', 'size' => 18])
                </span>
                <span class="mc-shortcut-label">{{ $tool['short'] ?? $tool['name'] }}</span>
            </button>
        @endforeach
    </div>
@endif
