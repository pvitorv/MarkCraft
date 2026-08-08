{{-- Mini-copy persuasiva MarkCraft → Blog CriaSys Web (abaixo do hub) --}}
@php
    $blog = $cmsBlog ?? config('markcraft.blog', []);
    $blogName = $blog['name'] ?? 'Blog CriaSys Web';
    $url = trim((string) ($blog['url'] ?? '#'));
    if ($url === '') {
        $url = '#blog-criasys';
    }
    $registerUrl = trim((string) ($blog['register_url'] ?? ''));
    if ($registerUrl === '') {
        $registerUrl = $url;
    }
@endphp

<section class="mc-bridge-section mx-auto max-w-6xl px-4 pb-14 pt-2" id="blog-criasys" aria-labelledby="mc-bridge-title">
    <div class="mc-bridge-grid">
        <div class="min-w-0">
            <p class="text-[10px] uppercase tracking-[0.16em] text-violet-300/85">Do MarkCraft para o {{ $blogName }}</p>
            <h2 id="mc-bridge-title" class="mc-brand mt-2 text-2xl sm:text-3xl font-bold text-white leading-tight">
                Você já tem o editor. No Blog, ele vira operação completa.
            </h2>

            <div class="mt-4 space-y-3.5 text-sm sm:text-[0.95rem] text-zinc-400 leading-relaxed max-w-xl">
                <p>
                    No MarkCraft o caminho é direto: abrir o studio, montar a arte, exportar e limpar.
                    É a porta de entrada da família CriaSys — o mesmo DNA visual que o Image Studio do Blog usa por dentro.
                </p>
                <p>
                    No <strong class="font-semibold text-zinc-100">{{ $blogName }}</strong> você não fica só no arquivo baixado.
                    Cria a conta, ganha <span class="text-zinc-300">/@seu-nome</span>, escreve em blocos, publica, ranqueia com SEO
                    e monetiza com AdSense na lateral e afiliado dentro do post — <strong class="text-zinc-200">100% seus</strong>,
                    sem split de anúncios. A plataforma cobra só pela assinatura do sistema.
                </p>
                <p>
                    O diferencial está no hub <strong class="text-zinc-200">Ferramentas</strong> do painel:
                    abuse do nosso Image Studio, do encurtador de links, do conversor de imagens,
                    do PDF/HTML e do construtor de Landing Pages — tudo no mesmo lugar, sem WordPress,
                    sem plugin e sem sair da plataforma. Cada módulo alimenta o próximo passo do crescimento.
                </p>
            </div>

            <div class="mt-6 flex flex-col sm:flex-row flex-wrap gap-3">
                <a
                    href="{{ $url }}"
                    class="mc-cta mc-cta-blog inline-flex justify-center rounded-md px-5 py-2.5 text-sm font-semibold"
                    @if(!str_starts_with($url, '#')) target="_blank" rel="noopener" @endif
                >
                    Conhecer o {{ $blogName }} →
                </a>
                <a
                    href="{{ $registerUrl }}"
                    class="inline-flex justify-center rounded-md border border-violet-400/40 bg-violet-500/10 px-5 py-2.5 text-sm font-semibold text-violet-100 hover:bg-violet-500/20 transition"
                    @if(!str_starts_with($registerUrl, '#')) target="_blank" rel="noopener" @endif
                >
                    Começar teste grátis
                </a>
            </div>
            <p class="mt-3 text-[11px] text-zinc-500 max-w-md">
                Trial Pro Studio sem cartão no cadastro · depois Essencial, Pro Studio ou passe · cartão ou Pix.
                Depoimentos entram só com feedback real do acesso antecipado.
            </p>
        </div>

        <aside class="mc-bridge-art" aria-hidden="true">
            <svg viewBox="0 0 360 300" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto max-w-[340px] mx-auto">
                <defs>
                    <linearGradient id="mcBridgeGlow" x1="0" y1="0" x2="1" y2="1">
                        <stop offset="0%" stop-color="#f43f5e" stop-opacity="0.55"/>
                        <stop offset="55%" stop-color="#a855f7" stop-opacity="0.45"/>
                        <stop offset="100%" stop-color="#22d3ee" stop-opacity="0.35"/>
                    </linearGradient>
                    <linearGradient id="mcBridgeStroke" x1="0" y1="0" x2="1" y2="0">
                        <stop offset="0%" stop-color="#39ff14"/>
                        <stop offset="50%" stop-color="#a855f7"/>
                        <stop offset="100%" stop-color="#22d3ee"/>
                    </linearGradient>
                </defs>
                <ellipse cx="180" cy="150" rx="150" ry="110" fill="url(#mcBridgeGlow)" opacity="0.22"/>
                <path d="M70 78 C120 78, 140 150, 180 150 C220 150, 240 222, 290 222"
                      stroke="url(#mcBridgeStroke)" stroke-width="2.5" stroke-linecap="round" stroke-dasharray="6 7" opacity="0.85"/>
                <g transform="translate(28,36)">
                    <rect x="0" y="0" width="88" height="72" rx="10" fill="#0c1118" stroke="#39ff14" stroke-width="1.5" opacity="0.95"/>
                    <rect x="12" y="12" width="64" height="40" rx="4" fill="#121820" stroke="rgba(57,255,20,0.35)"/>
                    <rect x="18" y="18" width="36" height="5" rx="1.5" fill="#39ff14" opacity="0.75"/>
                    <rect x="18" y="28" width="24" height="3" rx="1" fill="#fff" opacity="0.25"/>
                    <text x="44" y="66" text-anchor="middle" fill="#39ff14" font-size="9" font-family="system-ui,sans-serif" font-weight="700">MarkCraft</text>
                </g>
                <g transform="translate(136,112)">
                    <rect x="0" y="0" width="88" height="72" rx="10" fill="#0c1118" stroke="#a855f7" stroke-width="1.5"/>
                    <path d="M18 16 h52 v40 H18 z" stroke="#c084fc" stroke-width="1.2" fill="rgba(168,85,247,0.12)"/>
                    <path d="M26 28 h36 M26 36 h28 M26 44 h32" stroke="#e9d5ff" stroke-width="1.5" stroke-linecap="round" opacity="0.7"/>
                    <text x="44" y="66" text-anchor="middle" fill="#e9d5ff" font-size="9" font-family="system-ui,sans-serif" font-weight="700">Blog</text>
                </g>
                <g transform="translate(244,184)">
                    <rect x="0" y="0" width="88" height="72" rx="10" fill="#0c1118" stroke="#22d3ee" stroke-width="1.5"/>
                    <circle cx="44" cy="30" r="14" stroke="#67e8f9" stroke-width="1.4" fill="rgba(34,211,238,0.1)"/>
                    <path d="M44 22 v16 M38 30 h12" stroke="#a5f3fc" stroke-width="1.6" stroke-linecap="round"/>
                    <text x="44" y="66" text-anchor="middle" fill="#a5f3fc" font-size="9" font-family="system-ui,sans-serif" font-weight="700">Receita</text>
                </g>
            </svg>
            <ol class="mc-bridge-steps mt-3 grid grid-cols-3 gap-2 text-center text-[10px] sm:text-[11px] text-zinc-500">
                <li><span class="block text-[#39ff14]/90 font-semibold">1. Editar</span>artes no studio</li>
                <li><span class="block text-violet-300 font-semibold">2. Publicar</span>blog + capa</li>
                <li><span class="block text-cyan-300 font-semibold">3. Monetizar</span>LP · ads · afiliado</li>
            </ol>
        </aside>
    </div>

    {{-- Hub de ferramentas do Blog — grid com ícones SVG --}}
    <div class="mt-10 sm:mt-12">
        <p class="text-[10px] uppercase tracking-[0.16em] text-zinc-500">Hub Ferramentas · painel do Blog</p>
        <h3 class="mc-brand mt-1.5 text-lg sm:text-xl font-bold text-white">Cinco módulos. Um painel. Sem sair da plataforma.</h3>
        <p class="mt-2 max-w-2xl text-sm text-zinc-400 leading-relaxed">
            No plano Pro Studio (ou trial/cortesia) o hub reúne Image Studio, Encurtador, Conversor, PDF/HTML e Landing Pages.
            Fora das abas, o arsenal ainda inclui assistente de texto (IA), AdSense na lateral e bloco de produto/afiliado no post.
        </p>

        <ul class="mc-bridge-tools mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            <li class="mc-bridge-tool">
                <span class="mc-bridge-tool-icon" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                </span>
                <div>
                    <p class="mc-bridge-tool-title">Image Studio</p>
                    <p class="mc-bridge-tool-text">Canvas no painel: capas, stories, feed, YouTube, TikTok. Layouts, pacotes, tipografia, formas, crop, filtros, export e remoção de fundo (rembg). A ferramenta-estrela — sem Canva externo.</p>
                </div>
            </li>
            <li class="mc-bridge-tool">
                <span class="mc-bridge-tool-icon" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                </span>
                <div>
                    <p class="mc-bridge-tool-title">Encurtador</p>
                    <p class="mc-bridge-tool-text">URLs curtas no próprio blog (<span class="text-zinc-300">/@seu-blog/l/código</span>), com título, liga/desliga e contador de cliques. Ideal para afiliados, bio e campanhas.</p>
                </div>
            </li>
            <li class="mc-bridge-tool">
                <span class="mc-bridge-tool-icon" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M7 4v16M17 4v16M3 8h4M17 8h4M3 16h4M17 16h4M7 12h10"/></svg>
                </span>
                <div>
                    <p class="mc-bridge-tool-title">Conversor de imagens</p>
                    <p class="mc-bridge-tool-text">PNG ↔ JPG ↔ WebP (qualidade e largura), favicon 32×32 e gravação direta nas mídias do blog. Sobe, ajusta e usa no post — sem site externo.</p>
                </div>
            </li>
            <li class="mc-bridge-tool">
                <span class="mc-bridge-tool-icon" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M8 13h8M8 17h6"/></svg>
                </span>
                <div>
                    <p class="mc-bridge-tool-title">PDF / HTML</p>
                    <p class="mc-bridge-tool-text">HTML → PDF (A4/Carta), PDF → HTML (texto) e exportar artigo do blog em PDF a partir dos blocos. Material de apoio, e-book leve ou backup legível.</p>
                </div>
            </li>
            <li class="mc-bridge-tool sm:col-span-2 lg:col-span-1">
                <span class="mc-bridge-tool-icon" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                </span>
                <div>
                    <p class="mc-bridge-tool-title">Landing Pages</p>
                    <p class="mc-bridge-tool-text">Páginas de captura no tema do blog, com views, cliques no CTA, leads e biblioteca de imagens. Integra com o Image Studio — e no fluxo afiliado gera LP de divulgação do CriaSys.</p>
                </div>
            </li>
        </ul>

        <div class="mc-bridge-extras mt-5 grid gap-2 sm:grid-cols-3 text-xs sm:text-sm text-zinc-400">
            <p><span class="text-zinc-200 font-semibold">Assistente de texto (IA)</span> — melhora títulos, bios e SEO no fluxo (sua chave).</p>
            <p><span class="text-zinc-200 font-semibold">AdSense na lateral</span> — cola o ca-pub; receita 100% do blogueiro.</p>
            <p><span class="text-zinc-200 font-semibold">Bloco produto/afiliado</span> — vitrine dentro do artigo, também 100% sua.</p>
        </div>
    </div>
</section>
