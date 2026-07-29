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
        width: 100%;
        text-align: left;
        cursor: pointer;
    }
    .mc-glass-item:hover {
        background: rgba(20, 184, 166, 0.12);
        border-color: rgba(20, 184, 166, 0.35);
        transform: translateY(-1px);
    }
    .mc-tool-field {
        width: 100%;
        border-radius: 0.55rem;
        border: 1px solid rgba(255, 255, 255, 0.12);
        background: rgba(0, 0, 0, 0.28);
        color: #f4f4f5;
        padding: 0.55rem 0.7rem;
        font-size: 0.875rem;
    }
    .mc-tool-field:focus {
        outline: none;
        border-color: rgba(20, 184, 166, 0.55);
    }
    .mc-tool-label {
        display: block;
        font-size: 0.7rem;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #a1a1aa;
        margin-bottom: 0.3rem;
    }
    .mc-tool-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.5rem;
        background: #14b8a6;
        color: #09090b;
        font-weight: 600;
        font-size: 0.875rem;
        padding: 0.55rem 1rem;
        transition: background 0.15s ease;
    }
    .mc-tool-btn:hover { background: #2dd4bf; }
    .mc-tool-btn:disabled { opacity: 0.55; cursor: not-allowed; }
    .mc-tool-btn-ghost {
        display: inline-flex;
        align-items: center;
        border-radius: 0.5rem;
        border: 1px solid rgba(255, 255, 255, 0.14);
        color: #e4e4e7;
        font-size: 0.8rem;
        padding: 0.45rem 0.75rem;
    }
    .mc-tool-btn-ghost:hover { background: rgba(255, 255, 255, 0.06); }
</style>

{{-- Ferramentas --}}
<div
    x-show="isOpen('ferramentas')"
    x-cloak
    class="fixed inset-0 z-[600] flex items-center justify-center p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="hub-ferramentas-title"
>
    <div class="mc-glass-backdrop absolute inset-0" @click="closeHub()"></div>
    <div
        class="mc-glass-panel relative z-10 w-full max-h-[min(90vh,720px)] overflow-y-auto rounded-2xl p-5 sm:p-6"
        :class="tool ? 'max-w-xl' : 'max-w-lg'"
        @click.stop
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-3 scale-[0.98]"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
    >
        <div class="flex items-start justify-between gap-3">
            <div>
                <template x-if="!tool">
                    <div>
                        <h2 id="hub-ferramentas-title" class="mc-brand text-xl font-bold text-white">Ferramentas</h2>
                        <p class="mt-1 text-sm text-zinc-400">Roda no navegador — arquivos não sobem para o servidor (exceto encurtador).</p>
                    </div>
                </template>
                <template x-if="tool">
                    <div>
                        <button type="button" class="text-xs text-teal-300 hover:text-teal-200 mb-1" @click="backToTools()">← Todas as ferramentas</button>
                        <h2 class="mc-brand text-xl font-bold text-white" x-text="toolTitle()"></h2>
                    </div>
                </template>
            </div>
            <button type="button" class="text-zinc-400 hover:text-white text-sm px-2 py-1" @click="closeHub()" aria-label="Fechar">✕</button>
        </div>

        <p x-show="toolError" x-cloak x-text="toolError" class="mt-3 text-sm text-rose-300"></p>
        <p x-show="toolMessage" x-cloak x-text="toolMessage" class="mt-3 text-sm text-teal-300"></p>

        {{-- Lista --}}
        <div class="mt-5 grid gap-2" x-show="!tool">
            @foreach(config('markcraft.tools', []) as $slug => $tool)
                <button type="button" class="mc-glass-item rounded-xl px-4 py-3 flex items-start gap-3" @click="openTool('{{ $slug }}')">
                    <span class="mt-0.5 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-white/10 bg-teal-500/10 text-teal-300">
                        @include('partials.tool_icon', ['icon' => $tool['icon'] ?? 'link', 'size' => 18])
                    </span>
                    <span class="min-w-0">
                        <p class="text-sm font-semibold text-zinc-100">{{ $tool['name'] }}</p>
                        <p class="mt-0.5 text-xs text-zinc-400">{{ $tool['blurb'] }}</p>
                    </span>
                </button>
            @endforeach
        </div>

        {{-- Encurtador --}}
        <div class="mt-5 space-y-3" x-show="tool === 'encurtador'" x-cloak>
            <div>
                <label class="mc-tool-label" for="mc-short-url">URL</label>
                <input id="mc-short-url" type="url" class="mc-tool-field" placeholder="https://…" x-model="shorten.url">
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                <div>
                    <label class="mc-tool-label">utm_source</label>
                    <input type="text" class="mc-tool-field" x-model="shorten.utm_source" placeholder="instagram">
                </div>
                <div>
                    <label class="mc-tool-label">utm_medium</label>
                    <input type="text" class="mc-tool-field" x-model="shorten.utm_medium" placeholder="social">
                </div>
                <div>
                    <label class="mc-tool-label">utm_campaign</label>
                    <input type="text" class="mc-tool-field" x-model="shorten.utm_campaign" placeholder="lancamento">
                </div>
            </div>
            <button type="button" class="mc-tool-btn" :disabled="toolBusy || !shorten.url" @click="runShorten()">
                <span x-text="toolBusy ? 'Gerando…' : 'Encurtar'"></span>
            </button>
            <div x-show="shorten.result" x-cloak class="rounded-xl border border-white/10 bg-black/25 p-3 space-y-2">
                <p class="text-xs text-zinc-400">Link curto</p>
                <p class="text-sm text-teal-200 break-all" x-text="shorten.result?.short_url"></p>
                <button type="button" class="mc-tool-btn-ghost" @click="copyText(shorten.result.short_url)">Copiar</button>
            </div>
        </div>

        {{-- Conversor imagens --}}
        <div class="mt-5 space-y-3" x-show="tool === 'conversor-imagens'" x-cloak>
            <div>
                <label class="mc-tool-label">Arquivos</label>
                <input type="file" accept="image/*" multiple class="mc-tool-field" @change="onImageFiles($event)">
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="mc-tool-label">Formato</label>
                    <select class="mc-tool-field" x-model="imageConv.format">
                        <option value="image/jpeg">JPG</option>
                        <option value="image/png">PNG</option>
                        <option value="image/webp">WebP</option>
                    </select>
                </div>
                <div>
                    <label class="mc-tool-label">Qualidade <span x-text="Math.round(imageConv.quality * 100) + '%'"></span></label>
                    <input type="range" min="0.5" max="1" step="0.02" class="w-full" x-model.number="imageConv.quality" :disabled="imageConv.format === 'image/png'">
                </div>
            </div>
            <p class="text-xs text-zinc-500" x-show="imageConv.files.length" x-text="imageConv.files.length + ' arquivo(s) · convertidos: ' + imageConv.done"></p>
            <button type="button" class="mc-tool-btn" :disabled="toolBusy || !imageConv.files.length" @click="runImageConvert()">
                <span x-text="toolBusy ? 'Convertendo…' : 'Converter e baixar'"></span>
            </button>
        </div>

        {{-- Conversor PDF --}}
        <div class="mt-5 space-y-3" x-show="tool === 'conversor-pdf'" x-cloak>
            <div class="flex flex-wrap gap-2">
                <button type="button" class="mc-tool-btn-ghost" :class="pdfConv.mode === 'pdf2img' && 'border-teal-400/50 text-teal-200'" @click="pdfConv.mode = 'pdf2img'">PDF → imagens</button>
                <button type="button" class="mc-tool-btn-ghost" :class="pdfConv.mode === 'img2pdf' && 'border-teal-400/50 text-teal-200'" @click="pdfConv.mode = 'img2pdf'">Imagens → PDF</button>
            </div>
            <div>
                <label class="mc-tool-label">Arquivos</label>
                <input
                    type="file"
                    class="mc-tool-field"
                    multiple
                    :accept="pdfConv.mode === 'pdf2img' ? 'application/pdf,.pdf' : 'image/*'"
                    @change="onPdfConvFiles($event)"
                >
            </div>
            <div class="grid grid-cols-2 gap-2" x-show="pdfConv.mode === 'pdf2img'">
                <div>
                    <label class="mc-tool-label">Formato de saída</label>
                    <select class="mc-tool-field" x-model="pdfConv.format">
                        <option value="image/png">PNG</option>
                        <option value="image/jpeg">JPG</option>
                        <option value="image/webp">WebP</option>
                    </select>
                </div>
                <div>
                    <label class="mc-tool-label">Escala <span x-text="pdfConv.scale + 'x'"></span></label>
                    <input type="range" min="1" max="3" step="0.25" class="w-full" x-model.number="pdfConv.scale">
                </div>
            </div>
            <p class="text-xs text-zinc-500" x-show="pdfConv.files.length" x-text="pdfConv.files.length + ' arquivo(s) · itens: ' + pdfConv.done"></p>
            <button type="button" class="mc-tool-btn" :disabled="toolBusy || !pdfConv.files.length" @click="runPdfConvert()">
                <span x-text="toolBusy ? 'Processando…' : 'Converter e baixar'"></span>
            </button>
        </div>

        {{-- Compressor PDF --}}
        <div class="mt-5 space-y-3" x-show="tool === 'compressor-pdf'" x-cloak>
            <div>
                <label class="mc-tool-label">PDF</label>
                <input type="file" accept="application/pdf,.pdf" class="mc-tool-field" @change="onPdfCompressFile($event)">
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="mc-tool-label">Qualidade JPG <span x-text="Math.round(pdfCompress.quality * 100) + '%'"></span></label>
                    <input type="range" min="0.4" max="0.92" step="0.02" class="w-full" x-model.number="pdfCompress.quality">
                </div>
                <div>
                    <label class="mc-tool-label">Escala <span x-text="pdfCompress.scale + 'x'"></span></label>
                    <input type="range" min="1" max="2.5" step="0.25" class="w-full" x-model.number="pdfCompress.scale">
                </div>
            </div>
            <p class="text-xs text-zinc-500" x-show="pdfCompress.file">
                Original: <span x-text="formatBytes(pdfCompress.before)"></span>
                <span x-show="pdfCompress.after"> · Novo: <span x-text="formatBytes(pdfCompress.after)"></span></span>
            </p>
            <button type="button" class="mc-tool-btn" :disabled="toolBusy || !pdfCompress.file" @click="runPdfCompress()">
                <span x-text="toolBusy ? 'Compactando…' : 'Compactar e baixar'"></span>
            </button>
            <p class="text-[11px] text-zinc-500">Reexporta páginas como imagens — bom para PDFs “pesados” de scan/foto.</p>
        </div>
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
            <div class="flex items-start gap-3 min-w-0">
                <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-lg border border-amber-400/35 bg-amber-500/15 text-amber-300">
                    @include('partials.tool_icon', ['icon' => 'packs', 'size' => 22])
                </span>
                <div>
                    <h2 id="hub-packs-title" class="mc-brand text-xl font-bold text-white">Packs CriaSys</h2>
                    <p class="mt-1 text-sm text-zinc-400">Produtos da linha CriaSys — Blog, packs e planos. Sem anúncios genéricos.</p>
                </div>
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
            <div class="flex items-start gap-3 min-w-0">
                <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-lg border border-rose-400/35 bg-rose-500/15 text-rose-300">
                    @include('partials.tool_icon', ['icon' => 'heart', 'size' => 22])
                </span>
                <div>
                    <h2 id="hub-apoiar-title" class="mc-brand text-xl font-bold text-white">Apoiar o MarkCraft</h2>
                    <p class="mt-1 text-sm text-zinc-400">Contribuição opcional a partir de R$ {{ number_format(config('markcraft.donations.min_brl', 2), 2, ',', '.') }} — ajuda a manter o studio gratuito no ar.</p>
                </div>
            </div>
            <button type="button" class="text-zinc-400 hover:text-white text-sm px-2 py-1" @click="closeHub()" aria-label="Fechar">✕</button>
        </div>
        <div class="mt-4 space-y-3 text-sm text-zinc-300 leading-relaxed">
            <p>O MarkCraft é o studio gratuito da família CriaSys. Manter servidores e melhorias tem custo — um “obrigado” opcional ajuda. Para blog, cobrança e o editor no fluxo de conteúdo, use o <a href="{{ config('markcraft.blog.url') }}" target="_blank" rel="noopener" class="text-teal-300 hover:underline">Blog CriaSys Web</a>.</p>
            <p class="text-xs text-zinc-500">A vitrine de packs e produtos é da própria linha CriaSys — sem anúncios genéricos de terceiros.</p>
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

@include('partials.credits_modal')
