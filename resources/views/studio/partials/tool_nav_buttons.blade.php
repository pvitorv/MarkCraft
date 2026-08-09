{{-- Encurtar · Imagens · PDF · Compactar — mesma aparência que tinham no menu --}}
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
</div>
