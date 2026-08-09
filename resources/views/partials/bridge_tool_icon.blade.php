@props(['icon' => 'image', 'size' => 22])

@php
    $s = (int) $size;
@endphp

@switch($icon)
    @case('link')
        <svg width="{{ $s }}" height="{{ $s }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
        @break
    @case('convert')
        <svg width="{{ $s }}" height="{{ $s }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M7 4v16M17 4v16M3 8h4M17 8h4M3 16h4M17 16h4M7 12h10"/></svg>
        @break
    @case('pdf')
        <svg width="{{ $s }}" height="{{ $s }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M8 13h8M8 17h6"/></svg>
        @break
    @case('landing')
        <svg width="{{ $s }}" height="{{ $s }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
        @break
    @default
        <svg width="{{ $s }}" height="{{ $s }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
@endswitch
