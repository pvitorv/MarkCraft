{{-- Encurtar · Imagens · PDF · Compactar · Atalhos --}}
@php
    $tools = config('markcraft.tools', []);
@endphp
<div class="flex flex-wrap items-center gap-1.5 xl:gap-2 text-sm text-zinc-300 shrink-0">
    @foreach($tools as $slug => $tool)
        <button
            type="button"
            @click="window.dispatchEvent(new CustomEvent('mc-open-tool', { detail: '{{ $slug }}' }))"
            class="inline-flex items-center gap-1.5 px-2 py-1.5 rounded-md hover:bg-white/5 text-zinc-300 hover:text-white"
            title="{{ $tool['name'] }}"
        >
            <span class="inline-flex h-4 w-4 text-teal-300">
                @include('partials.tool_icon', ['icon' => $tool['icon'] ?? 'link', 'size' => 16])
            </span>
            <span class="hidden xl:inline">{{ $tool['short'] ?? $tool['name'] }}</span>
        </button>
    @endforeach
    <button
        type="button"
        @click="openImageStudioShortcutsModal()"
        class="inline-flex items-center gap-1.5 px-2 py-1.5 rounded-md hover:bg-white/5 text-zinc-300 hover:text-white"
        title="Atalhos do teclado"
    >
        <span class="inline-flex h-4 w-4 text-violet-300" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="6" width="20" height="12" rx="2"/>
                <path d="M6 10h.01M10 10h.01M14 10h.01M18 10h.01M8 14h8"/>
            </svg>
        </span>
        <span class="hidden xl:inline">Atalhos</span>
    </button>
</div>
