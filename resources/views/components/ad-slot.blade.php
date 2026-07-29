@props([
    'slotId',
    'class' => '',
])

@php
    $slot = config('ads.slots.'.$slotId, []);
    $enabled = ($slot['enabled'] ?? false) && (config('ads.enabled') || ($slot['type'] ?? '') === 'own' || true);
    $label = $slot['label'] ?? $slotId;
@endphp

@if($enabled)
    <aside
        data-ad-slot="{{ $slotId }}"
        {{ $attributes->merge(['class' => 'ad-slot border border-dashed border-zinc-600/60 bg-zinc-900/40 text-zinc-500 text-center text-xs px-3 py-4 '.$class]) }}
        aria-label="Espaço publicitário: {{ $label }}"
    >
        <p class="font-medium text-zinc-400">Seu anúncio aqui</p>
        <p class="mt-1 text-[10px] opacity-70">{{ $slotId }} · {{ $label }}</p>
    </aside>
@endif
