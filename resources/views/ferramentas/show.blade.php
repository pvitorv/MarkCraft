@extends('layouts.markcraft')

@section('title', $tool['name'])

@section('content')
<div class="mx-auto max-w-3xl px-4 py-12">
    <a href="{{ route('ferramentas.index') }}" class="text-sm text-teal-800 hover:underline">← Ferramentas</a>
    <h1 class="mc-brand mt-4 text-3xl font-bold text-zinc-900">{{ $tool['name'] }}</h1>
    <p class="mt-3 text-zinc-600">{{ $tool['blurb'] }}</p>
    <p class="mt-8 rounded-md border border-dashed border-zinc-400/50 bg-white/50 px-4 py-6 text-sm text-zinc-600">
        Em breve. Arquivos temporários serão processados e apagados após o download — sem guardar no servidor.
    </p>
</div>
@endsection
