@extends('layouts.markcraft')

@section('title', 'Apoiar')

@section('content')
<div class="mx-auto max-w-2xl px-4 py-14">
    <h1 class="mc-brand text-3xl md:text-4xl font-bold text-zinc-900">Ajude o MarkCraft a continuar</h1>

    <div class="mt-6 space-y-4 text-zinc-700 leading-relaxed">
        <p>O MarkCraft é gratuito para quem cria, vende e experimenta. Manter servidores, ferramentas e melhorias tem custo.</p>
        <p>Se esta ferramenta te ajudou e você quiser que ela continue existindo, considere uma contribuição a partir de <strong>R$ 2,00</strong> — via <strong>Pix</strong> ou <strong>cartão</strong>. Qualquer valor faz diferença.</p>
        <p>Não é obrigatório. É um “obrigado” opcional de quem acredita no projeto.</p>
        <p class="text-sm text-zinc-500">No futuro, queremos estudar uma <strong>área de membros</strong> para quem contribui — um espaço de troca entre criadores. Isso será feito com cuidado, respeitando direitos autorais: cada pessoa responde pelo material que compartilha; o MarkCraft não hospeda nem endossa conteúdo protegido sem autorização.</p>
    </div>

    <div class="mt-10 flex flex-wrap gap-3">
        @if(config('markcraft.donations.gateway_url'))
            <a href="{{ config('markcraft.donations.gateway_url') }}" class="mc-cta-pulse inline-flex rounded-md bg-teal-700 px-5 py-3 text-white font-semibold transition" target="_blank" rel="noopener">
                Contribuir a partir de R$ 2
            </a>
        @else
            <button type="button" class="inline-flex rounded-md bg-teal-700 px-5 py-3 text-white font-semibold opacity-90" disabled title="Gateway em configuração">
                Contribuir a partir de R$ 2
            </button>
            <p class="w-full text-xs text-zinc-500">Gateway de pagamento em stub — configure <code class="text-teal-800">DONATION_GATEWAY_URL</code> / Pix no .env.</p>
        @endif
        @if(config('markcraft.donations.pix_key'))
            <p class="w-full text-sm text-zinc-600">Pix: <code class="text-zinc-900">{{ config('markcraft.donations.pix_key') }}</code></p>
        @endif
    </div>
</div>
@endsection
