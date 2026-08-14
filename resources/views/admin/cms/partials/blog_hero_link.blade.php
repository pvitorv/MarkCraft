{{-- Link do card Blog CriaSys no hero da home --}}
<div class="rounded-lg border border-amber-400/35 bg-amber-500/10 p-4 space-y-3">
    <div>
        <p class="text-sm font-semibold text-amber-100">Link do Blog no card do hero</p>
        <p class="cms-help text-amber-100/70">Quando o Blog estiver no ar, marque o link como ativo e informe a URL. Até lá, visitantes veem o texto “em breve”.</p>
    </div>
    <label class="flex items-center gap-2 text-sm rounded-lg border border-white/10 px-3 py-2.5 bg-black/20">
        <input type="checkbox" name="cta_ready" value="1" @checked(!empty($blog['cta_ready']))>
        Link do botão ativo (Blog pronto)
    </label>
    <div>
        <label class="cms-label">URL do Blog</label>
        <input class="cms-input" type="url" name="url" value="{{ $blog['url'] ?? '' }}" placeholder="https://blog.criasysweb.com.br">
    </div>
    <div>
        <label class="cms-label">Texto do botão (quando ativo)</label>
        <input class="cms-input" name="cta" value="{{ $blog['cta'] ?? '' }}" placeholder="Conhecer o Blog CriaSys Web">
    </div>
    <div>
        <label class="cms-label">Texto enquanto ainda não está pronto</label>
        <input class="cms-input" name="cta_pending" value="{{ $blog['cta_pending'] ?? 'Conteúdo e tutoriais no Blog' }}" placeholder="Conteúdo e tutoriais no Blog">
    </div>
    <div>
        <label class="cms-label">Nota abaixo do botão (modo “em breve”)</label>
        <input class="cms-input" name="early_access_note" value="{{ $blog['early_access_note'] ?? '' }}" placeholder="Tutoriais e conteúdo gratuito no Blog">
    </div>
</div>
