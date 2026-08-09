@php
    $donations = $cms['donations'] ?? config('markcraft.donations', []);
    $minBrl = $donations['min_brl'] ?? 2;
@endphp

<div class="space-y-4">
    <div class="cms-card space-y-4">
        <div>
            <h1 class="mc-brand text-lg font-bold text-white">Doações · Apoiar</h1>
            <p class="cms-help">Modal <strong>Apoiar</strong> na home e página <a href="{{ route('apoiar') }}" target="_blank" class="text-rose-300 underline">/apoiar</a>. Configure Pix, gateway e textos.</p>
            <p class="cms-help mt-1"><a href="{{ route('home') }}?hub=apoiar" target="_blank" class="text-rose-300 underline">Abrir modal na home ↗</a></p>
        </div>

        <form method="POST" action="{{ route('admin.cms.update') }}" class="space-y-6" id="cms-donations">
            @csrf
            <input type="hidden" name="section" value="donations">

            <div class="rounded-lg border-2 border-rose-400/50 bg-rose-500/10 p-4 space-y-3">
                <p class="text-base font-bold text-rose-50">Pagamento</p>
                <div class="grid sm:grid-cols-3 gap-3">
                    <div>
                        <label class="cms-label">Valor mínimo (R$)</label>
                        <input class="cms-input" type="number" step="0.01" min="0" name="donation_min_brl" value="{{ $minBrl }}">
                    </div>
                    <div>
                        <label class="cms-label">Chave Pix</label>
                        <input class="cms-input" name="donation_pix_key" value="{{ $donations['pix_key'] ?? '' }}" placeholder="e-mail, CPF ou aleatória">
                    </div>
                    <div>
                        <label class="cms-label">URL do gateway (cartão / checkout)</label>
                        <input class="cms-input" type="url" name="donation_gateway_url" value="{{ $donations['gateway_url'] ?? '' }}" placeholder="https://…">
                    </div>
                </div>
                <div>
                    <label class="cms-label">Texto do botão principal (<code class="text-zinc-500">{min}</code> = valor mínimo)</label>
                    <input class="cms-input" name="donation_button_label" value="{{ $donations['button_label'] ?? 'Contribuir a partir de R$ {min}' }}">
                </div>
            </div>

            <div class="rounded-lg border border-white/10 p-4 space-y-3">
                <p class="text-sm font-bold text-white">Modal na home</p>
                <div>
                    <label class="cms-label">Título</label>
                    <input class="cms-input" name="donation_modal_title" value="{{ $donations['modal_title'] ?? 'Apoiar o MarkCraft' }}">
                </div>
                <div>
                    <label class="cms-label">Intro (<code class="text-zinc-500">{min}</code> opcional)</label>
                    <textarea class="cms-input" name="donation_modal_intro" rows="2">{{ $donations['modal_intro'] ?? '' }}</textarea>
                </div>
                <div>
                    <label class="cms-label">Texto principal</label>
                    <textarea class="cms-input" name="donation_modal_body" rows="3">{{ $donations['modal_body'] ?? '' }}</textarea>
                </div>
                <div>
                    <label class="cms-label">Nota de rodapé</label>
                    <input class="cms-input" name="donation_modal_note" value="{{ $donations['modal_note'] ?? '' }}">
                </div>
            </div>

            <div class="rounded-lg border border-white/10 p-4 space-y-3">
                <p class="text-sm font-bold text-white">Página /apoiar</p>
                <div>
                    <label class="cms-label">Título da página</label>
                    <input class="cms-input" name="donation_page_title" value="{{ $donations['page_title'] ?? 'Ajude o MarkCraft a continuar' }}">
                </div>
                @foreach(['page_body_1', 'page_body_2', 'page_body_3'] as $field)
                    <div>
                        <label class="cms-label">Parágrafo {{ substr($field, -1) }}</label>
                        <textarea class="cms-input" name="donation_{{ $field }}" rows="2">{{ $donations[$field] ?? '' }}</textarea>
                    </div>
                @endforeach
            </div>

            <button type="submit" class="cms-btn">Salvar doações</button>
        </form>
    </div>
</div>
