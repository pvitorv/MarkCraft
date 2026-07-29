{{-- Modais glass: Ferramentas · Packs · Apoiar (exige x-data="markCraftHub" no ancestral) --}}
<style>
    .mc-glass-backdrop {
        background: rgba(4, 8, 12, 0.45);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }
    .mc-glass-panel {
        background: rgba(255, 255, 255, 0.06);
        border: 1px solid rgba(255, 255, 255, 0.14);
        box-shadow: 0 24px 80px rgba(0, 0, 0, 0.45);
        backdrop-filter: blur(22px) saturate(1.35);
        -webkit-backdrop-filter: blur(22px) saturate(1.35);
    }
    .mc-glass-item {
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid rgba(255, 255, 255, 0.08);
        transition: background 0.15s ease, border-color 0.15s ease, transform 0.15s ease;
    }
    .mc-glass-item:hover {
        background: rgba(20, 184, 166, 0.12);
        border-color: rgba(20, 184, 166, 0.35);
        transform: translateY(-1px);
    }
</style>

{{-- Ferramentas --}}
<div
    x-show="isOpen('ferramentas')"
    x-cloak
    class="fixed inset-0 z-[600] flex items-center justify-center p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="hub-ferramentas-title"
    @keydown.escape.window="isOpen('ferramentas') && closeHub()"
>
    <div class="mc-glass-backdrop absolute inset-0" @click="closeHub()"></div>
    <div class="mc-glass-panel relative z-10 w-full max-w-lg max-h-[min(86vh,640px)] overflow-y-auto rounded-2xl p-5 sm:p-6" @click.stop
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-3 scale-[0.98]"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h2 id="hub-ferramentas-title" class="mc-brand text-xl font-bold text-white">Ferramentas</h2>
                <p class="mt-1 text-sm text-zinc-400">Atalhos do hub CriaSys — em breve no navegador.</p>
            </div>
            <button type="button" class="text-zinc-400 hover:text-white text-sm px-2 py-1" @click="closeHub()" aria-label="Fechar">✕</button>
        </div>
        <div class="mt-5 grid gap-2">
            @foreach(config('markcraft.tools', []) as $slug => $tool)
                <div class="mc-glass-item rounded-xl px-4 py-3 text-left">
                    <p class="text-sm font-semibold text-zinc-100">{{ $tool['name'] }}</p>
                    <p class="mt-0.5 text-xs text-zinc-400">{{ $tool['blurb'] }}</p>
                    <p class="mt-2 text-[10px] uppercase tracking-wider text-teal-300/80">{{ $tool['status'] }}</p>
                </div>
            @endforeach
        </div>
        @guest
            <p class="mt-5 text-xs text-zinc-500">
                Conta grátis desbloqueia o Studio —
                <a href="{{ route('register') }}" class="text-teal-300 hover:underline">criar conta</a>
            </p>
        @endguest
    </div>
</div>

{{-- Packs --}}
<div
    x-show="isOpen('packs')"
    x-cloak
    class="fixed inset-0 z-[600] flex items-center justify-center p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="hub-packs-title"
>
    <div class="mc-glass-backdrop absolute inset-0" @click="closeHub()"></div>
    <div class="mc-glass-panel relative z-10 w-full max-w-lg max-h-[min(86vh,640px)] overflow-y-auto rounded-2xl p-5 sm:p-6" @click.stop
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-3 scale-[0.98]"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h2 id="hub-packs-title" class="mc-brand text-xl font-bold text-white">Packs</h2>
                <p class="mt-1 text-sm text-zinc-400">Compre fora, importe PSD/PNG no Studio, edite e baixe.</p>
            </div>
            <button type="button" class="text-zinc-400 hover:text-white text-sm px-2 py-1" @click="closeHub()" aria-label="Fechar">✕</button>
        </div>
        <div class="mt-5 grid gap-2">
            @foreach(config('markcraft.affiliate_packs', []) as $pack)
                <a
                    href="{{ $pack['affiliate_url'] ?: '#' }}"
                    @if(($pack['affiliate_url'] ?? '#') !== '#') target="_blank" rel="noopener" @endif
                    class="mc-glass-item rounded-xl px-4 py-3 text-left block"
                >
                    <p class="text-[10px] uppercase tracking-wider text-amber-300/80">{{ $pack['tag'] }}</p>
                    <p class="mt-1 text-sm font-semibold text-zinc-100">{{ $pack['title'] }}</p>
                    <p class="mt-0.5 text-xs text-zinc-400">{{ $pack['blurb'] }}</p>
                    <p class="mt-2 text-xs text-teal-300">Ver oferta →</p>
                </a>
            @endforeach
        </div>
    </div>
</div>

{{-- Apoiar --}}
<div
    x-show="isOpen('apoiar')"
    x-cloak
    class="fixed inset-0 z-[600] flex items-center justify-center p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="hub-apoiar-title"
>
    <div class="mc-glass-backdrop absolute inset-0" @click="closeHub()"></div>
    <div class="mc-glass-panel relative z-10 w-full max-w-md max-h-[min(86vh,640px)] overflow-y-auto rounded-2xl p-5 sm:p-6" @click.stop
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-3 scale-[0.98]"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h2 id="hub-apoiar-title" class="mc-brand text-xl font-bold text-white">Apoiar o MarkCraft</h2>
                <p class="mt-1 text-sm text-zinc-400">Contribuição opcional a partir de R$ {{ number_format(config('markcraft.donations.min_brl', 2), 2, ',', '.') }}.</p>
            </div>
            <button type="button" class="text-zinc-400 hover:text-white text-sm px-2 py-1" @click="closeHub()" aria-label="Fechar">✕</button>
        </div>
        <div class="mt-4 space-y-3 text-sm text-zinc-300 leading-relaxed">
            <p>O MarkCraft é gratuito. Manter servidores e melhorias tem custo — um “obrigado” opcional ajuda o projeto a continuar.</p>
            <p class="text-xs text-zinc-500">No futuro, uma área de membros para quem contribui poderá existir, com cuidado com direitos autorais.</p>
        </div>
        <div class="mt-6 flex flex-wrap gap-2">
            @if(config('markcraft.donations.gateway_url'))
                <a href="{{ config('markcraft.donations.gateway_url') }}" target="_blank" rel="noopener"
                   class="inline-flex rounded-md bg-teal-500 px-4 py-2.5 text-sm font-semibold text-zinc-950 hover:bg-teal-400 transition">
                    Contribuir a partir de R$ 2
                </a>
            @else
                <button type="button" disabled class="inline-flex rounded-md bg-teal-500/50 px-4 py-2.5 text-sm font-semibold text-zinc-950 cursor-not-allowed">
                    Contribuir a partir de R$ 2
                </button>
                <p class="w-full text-[11px] text-zinc-500">Gateway em configuração — defina DONATION_GATEWAY_URL no .env.</p>
            @endif
            @if(config('markcraft.donations.pix_key'))
                <p class="w-full text-xs text-zinc-400">Pix: <code class="text-teal-200">{{ config('markcraft.donations.pix_key') }}</code></p>
            @endif
        </div>
    </div>
</div>
