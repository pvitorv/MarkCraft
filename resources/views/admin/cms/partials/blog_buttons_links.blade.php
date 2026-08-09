{{-- Botões e links do Blog — hero, ponte #blog-criasys, card funil --}}
<div id="cms-blog-links" class="rounded-lg border-2 border-teal-400/50 bg-teal-500/10 p-4 space-y-4">
    <div>
        <p class="text-base font-bold text-teal-50">Botões e links do Blog</p>
        <p class="cms-help text-teal-100/80 mt-1">Controla <strong>todos</strong> os botões do Blog na home: card do hero, seção “Do MarkCraft para o Blog”, card “Família CriaSys”. Preencha as URLs, marque <strong>Links ativos</strong> se a página de vendas já estiver no ar e clique em <strong>Salvar links dos botões</strong>.</p>
    </div>

    <label class="flex items-center gap-2 text-sm rounded-lg border border-white/15 px-3 py-2.5 bg-black/25 font-medium text-white">
        <input type="checkbox" name="blog_cta_ready" value="1" @checked(!empty($blog['cta_ready']))>
        Links do Blog ativos (página de vendas no ar)
    </label>

    <div class="grid gap-4 lg:grid-cols-2">
        <div class="rounded-lg border border-white/10 bg-black/20 p-3 space-y-2">
            <p class="text-xs font-bold uppercase tracking-wide text-amber-200">Botão principal — vendas / conhecer</p>
            <p class="cms-help mb-0">Hero, ponte e funil (quando links ativos)</p>
            <label class="cms-label">Texto do botão</label>
            <input class="cms-input" name="blog_cta" value="{{ $blog['cta'] ?? '' }}" placeholder="Conhecer o Blog CriaSys Web">
            <label class="cms-label">URL (página de vendas)</label>
            <input class="cms-input" type="url" name="blog_url" value="{{ $blog['url'] ?? '' }}" placeholder="https://blog.criasysweb.com.br">
        </div>

        <div class="rounded-lg border border-white/10 bg-black/20 p-3 space-y-2">
            <p class="text-xs font-bold uppercase tracking-wide text-violet-200">Enquanto links estão inativos</p>
            <p class="cms-help mb-0">Visitante vê isto no lugar do botão principal</p>
            <label class="cms-label">Texto “em breve”</label>
            <input class="cms-input" name="blog_cta_pending" value="{{ $blog['cta_pending'] ?? 'Página de vendas em breve' }}">
            <label class="cms-label">Nota no card do hero (opcional)</label>
            <input class="cms-input" name="blog_early_access_note" value="{{ $blog['early_access_note'] ?? '' }}">
        </div>

        <div class="rounded-lg border border-white/10 bg-black/20 p-3 space-y-2">
            <p class="text-xs font-bold uppercase tracking-wide text-violet-200">Botão cadastro / teste grátis</p>
            <p class="cms-help mb-0">Ponte e funil (só quando links ativos)</p>
            <label class="cms-label">Texto do botão</label>
            <input class="cms-input" name="blog_register_cta" value="{{ $blog['register_cta'] ?? 'Começar teste grátis' }}">
            <label class="cms-label">URL de cadastro</label>
            <input class="cms-input" type="url" name="blog_register_url" value="{{ $blog['register_url'] ?? '' }}" placeholder="https://blog.criasysweb.com.br/cadastro">
        </div>

        <div class="rounded-lg border border-white/10 bg-black/20 p-3 space-y-2">
            <p class="text-xs font-bold uppercase tracking-wide text-zinc-300">Botões MarkCraft (card funil)</p>
            <p class="cms-help mb-0">Deixe URL vazia para usar rota padrão do site</p>
            <label class="cms-label">Continuar no Studio — texto</label>
            <input class="cms-input" name="blog_continue_studio_cta" value="{{ $blog['continue_studio_cta'] ?? 'Continuar no Studio' }}">
            <label class="cms-label">Continuar no Studio — URL (opcional)</label>
            <input class="cms-input" type="url" name="blog_studio_url" value="{{ $blog['studio_url'] ?? '' }}" placeholder="/studio">
            <label class="cms-label">Criar conta — texto</label>
            <input class="cms-input" name="blog_create_account_cta" value="{{ $blog['create_account_cta'] ?? 'Criar conta no MarkCraft' }}">
            <label class="cms-label">Criar conta — URL (opcional)</label>
            <input class="cms-input" type="url" name="blog_markcraft_register_url" value="{{ $blog['markcraft_register_url'] ?? '' }}" placeholder="/register">
        </div>
    </div>
</div>
