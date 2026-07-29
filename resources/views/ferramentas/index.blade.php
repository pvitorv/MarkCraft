@extends('layouts.markcraft')

@section('title', 'Ferramentas')

@section('content')
<div class="mx-auto max-w-6xl px-4 py-12">
    <x-ad-slot slot-id="ad_app_top" class="mb-6" />

    <h1 class="mc-brand text-3xl font-bold text-zinc-900">Ferramentas</h1>
    <p class="mt-2 text-zinc-600">Utilitários para marketing e designers aventureiros. No MVP, páginas stub — em breve de verdade.</p>

    <div class="mt-10 grid gap-6 sm:grid-cols-2">
        @foreach($tools as $slug => $tool)
            <a href="{{ route('ferramentas.show', $slug) }}" class="block border-b border-zinc-800/15 pb-5 hover:border-teal-600 transition">
                <p class="font-semibold text-zinc-900">{{ $tool['name'] }}</p>
                <p class="mt-1 text-sm text-zinc-600">{{ $tool['blurb'] }}</p>
                <p class="mt-2 text-xs uppercase tracking-wide text-teal-800">{{ $tool['status'] }}</p>
            </a>
            @if($loop->index === 1)
                <div class="sm:col-span-2">
                    <x-ad-slot slot-id="ad_app_between_tools" />
                </div>
            @endif
        @endforeach
    </div>
</div>
@endsection
