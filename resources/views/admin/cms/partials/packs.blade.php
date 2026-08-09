@php
    $packsHub = $cms['packs_hub'] ?? config('markcraft.packs_hub', []);
    $packRows = array_values($cms['affiliate_packs'] ?? config('markcraft.affiliate_packs', []));
    if ($packRows === []) {
        $packRows = [['tag' => '', 'title' => '', 'blurb' => '', 'affiliate_url' => '']];
    }
@endphp

<div class="space-y-4">
    <div class="cms-card space-y-4">
        <div>
            <h1 class="mc-brand text-lg font-bold text-white">Packs CriaSys</h1>
            <p class="cms-help">Modal <strong>Packs</strong> na home (hub) — título do modal e cada oferta com link afiliado. Prévia: abra a home e clique em <strong>Packs CriaSys</strong>.</p>
            <p class="cms-help mt-1"><a href="{{ route('home') }}?hub=packs" target="_blank" class="text-amber-300 underline">Abrir modal na home ↗</a></p>
        </div>

        <form method="POST" action="{{ route('admin.cms.update') }}" class="space-y-6" id="cms-packs">
            @csrf
            <input type="hidden" name="section" value="packs">

            <div id="cms-packs-hub" class="rounded-lg border-2 border-amber-400/50 bg-amber-500/10 p-4 space-y-3">
                <p class="text-base font-bold text-amber-50">Cabeçalho do modal</p>
                <div>
                    <label class="cms-label">Título</label>
                    <input class="cms-input" name="packs_hub_title" value="{{ $packsHub['title'] ?? 'Packs CriaSys' }}">
                </div>
                <div>
                    <label class="cms-label">Subtítulo</label>
                    <textarea class="cms-input" name="packs_hub_subtitle" rows="2">{{ $packsHub['subtitle'] ?? '' }}</textarea>
                </div>
                <div>
                    <label class="cms-label">Texto do link em cada card</label>
                    <input class="cms-input" name="packs_hub_link_label" value="{{ $packsHub['link_label'] ?? 'Ver oferta →' }}">
                </div>
            </div>

            <div class="space-y-3">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <p class="text-sm font-bold text-white">Ofertas / packs (link afiliado em cada card)</p>
                    <a href="{{ route('admin.cms.packs.add-row') }}" class="cms-btn-ghost text-xs">+ Adicionar pack</a>
                </div>

                @foreach($packRows as $i => $pack)
                    <div class="rounded-xl border border-white/10 bg-black/20 p-4 space-y-2">
                        <p class="text-xs font-semibold text-zinc-400">Pack {{ $i + 1 }}</p>
                        <div class="grid sm:grid-cols-2 gap-2">
                            <div>
                                <label class="cms-label">Tag (ex.: Plataforma)</label>
                                <input class="cms-input" name="packs[{{ $i }}][tag]" value="{{ $pack['tag'] ?? '' }}">
                            </div>
                            <div>
                                <label class="cms-label">Título</label>
                                <input class="cms-input" name="packs[{{ $i }}][title]" value="{{ $pack['title'] ?? '' }}">
                            </div>
                        </div>
                        <div>
                            <label class="cms-label">Descrição</label>
                            <textarea class="cms-input" name="packs[{{ $i }}][blurb]" rows="2">{{ $pack['blurb'] ?? '' }}</textarea>
                        </div>
                        <div>
                            <label class="cms-label">Link da oferta (URL afiliado / vendas)</label>
                            <input class="cms-input" type="url" name="packs[{{ $i }}][affiliate_url]" value="{{ $pack['affiliate_url'] ?? '' }}" placeholder="https://…">
                        </div>
                    </div>
                @endforeach
            </div>

            <button type="submit" class="cms-btn">Salvar packs</button>
        </form>
    </div>
</div>
