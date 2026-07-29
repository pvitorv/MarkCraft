@extends('layouts.markcraft')

@section('title', 'Packs & afiliados')

@section('content')
<div class="mx-auto max-w-6xl px-4 py-12">
    <h1 class="mc-brand text-3xl font-bold text-zinc-900">Packs & afiliados</h1>
    <p class="mt-2 max-w-2xl text-zinc-600">Compre packs (fora do MarkCraft), importe PSD/PNG no Studio, edite e baixe. Links afiliados — stub no MVP.</p>

    <div class="mt-10 space-y-8">
        @foreach($packs as $pack)
            <div class="border-b border-zinc-800/15 pb-6">
                <p class="text-[10px] uppercase tracking-wide text-teal-800">{{ $pack['tag'] }}</p>
                <p class="mt-1 font-semibold text-zinc-900 text-lg">{{ $pack['title'] }}</p>
                <p class="mt-1 text-sm text-zinc-600">{{ $pack['blurb'] }}</p>
                <a href="{{ $pack['affiliate_url'] }}" class="mt-3 inline-block text-sm font-medium text-teal-800 hover:underline">Ver oferta →</a>
            </div>
            @if($loop->first)
                <x-ad-slot slot-id="ad_packs_inline" />
            @endif
        @endforeach
    </div>
</div>
@endsection
