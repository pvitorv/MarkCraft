@extends('layouts.markcraft')

@section('title', 'Apoiar')

@section('content')
@php
    $donations = $cmsDonations ?? config('markcraft.donations', []);
@endphp
<div class="mx-auto max-w-2xl px-4 py-14">
    <h1 class="mc-brand text-3xl md:text-4xl font-bold text-zinc-900">{{ $donations['page_title'] ?? 'Ajude o MarkCraft a continuar' }}</h1>

    <div class="mt-6 space-y-4 text-zinc-700 leading-relaxed">
        @foreach(['page_body_1', 'page_body_2', 'page_body_3'] as $field)
            @if(!empty($donations[$field]))
                <p>{!! \App\Support\Cms::donationText($donations[$field], $donations) !!}</p>
            @endif
        @endforeach
    </div>

    <div class="mt-10 flex flex-wrap gap-3">
        @if(!empty($donations['gateway_url']))
            <a href="{{ $donations['gateway_url'] }}" class="mc-cta-pulse inline-flex rounded-md bg-teal-700 px-5 py-3 text-white font-semibold transition" target="_blank" rel="noopener">
                {{ \App\Support\Cms::donationText($donations['button_label'] ?? 'Contribuir a partir de R$ {min}', $donations) }}
            </a>
        @else
            <button type="button" class="inline-flex rounded-md bg-teal-700 px-5 py-3 text-white font-semibold opacity-90" disabled title="Gateway em configuração">
                {{ \App\Support\Cms::donationText($donations['button_label'] ?? 'Contribuir a partir de R$ {min}', $donations) }}
            </button>
            <p class="w-full text-xs text-zinc-500">Configure gateway e Pix na aba <strong>Doações</strong> do CMS.</p>
        @endif
        @if(!empty($donations['pix_key']))
            <p class="w-full text-sm text-zinc-600">Pix: <code class="text-zinc-900">{{ $donations['pix_key'] }}</code></p>
        @endif
    </div>
</div>
@endsection
