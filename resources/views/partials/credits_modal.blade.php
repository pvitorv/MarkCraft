{{-- Modal Créditos / licenças — Studio + Home (exige markCraftHub no ancestral) --}}
@php
    $bgDriver = config('image_studio.background_removal.driver', 'rembg');
@endphp

<div
    x-show="creditsOpen"
    x-cloak
    class="fixed inset-0 z-[620] flex items-center justify-center p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="mc-credits-title"
    @keydown.escape.window="creditsOpen && closeCredits()"
>
    <div class="absolute inset-0 bg-black/70 backdrop-blur-[2px]" @click="closeCredits()" aria-hidden="true"></div>

    <div
        class="mc-credits-panel relative z-10 flex w-full max-w-lg max-h-[min(88vh,720px)] flex-col overflow-hidden rounded-xl border border-zinc-700 bg-[#0f0f12] shadow-2xl"
        @click.stop
    >
        <header class="flex shrink-0 items-start justify-between gap-3 border-b border-zinc-800 px-4 py-3.5 sm:px-5">
            <div class="min-w-0">
                <h2 id="mc-credits-title" class="mc-brand text-base font-bold text-white">Créditos e licenças</h2>
                <p class="mt-0.5 text-[11px] text-zinc-500">Atribuições de fontes, ícones e tecnologias do MarkCraft</p>
            </div>
            <button type="button" class="rounded-md border border-zinc-700 bg-zinc-900 px-2.5 py-1 text-xs text-zinc-300 hover:bg-zinc-800" @click="closeCredits()">
                Fechar
            </button>
        </header>

        <div class="min-h-0 flex-1 space-y-5 overflow-y-auto overscroll-contain px-4 py-4 text-sm text-zinc-300 sm:px-5">
            <section>
                <h3 class="text-[10px] font-semibold uppercase tracking-[0.12em] text-teal-300/90">Ícones e elementos</h3>
                <ul class="mt-2 space-y-2 text-xs leading-relaxed text-zinc-400">
                    <li>
                        <strong class="text-zinc-200">Bootstrap Icons</strong> — MIT ·
                        <a href="https://icons.getbootstrap.com/" target="_blank" rel="noopener" class="text-teal-300 hover:underline">icons.getbootstrap.com</a>
                    </li>
                    <li>
                        <strong class="text-zinc-200">Font Awesome Free</strong> — CC BY 4.0 · crédito: Font Awesome ·
                        <a href="https://fontawesome.com/" target="_blank" rel="noopener" class="text-teal-300 hover:underline">fontawesome.com</a>
                    </li>
                    <li>
                        <strong class="text-zinc-200">Material Symbols</strong> — Apache 2.0 · Google Fonts / Icons
                    </li>
                    <li>
                        <strong class="text-zinc-200">Emojis</strong> — caracteres Unicode; a aparência depende do sistema do dispositivo.
                    </li>
                    <li>
                        <strong class="text-zinc-200">Formas e stickers</strong> — catálogo geométrico / adesivos do MarkCraft (CriaSys).
                    </li>
                </ul>
            </section>

            <section>
                <h3 class="text-[10px] font-semibold uppercase tracking-[0.12em] text-teal-300/90">Tipografia</h3>
                <p class="mt-2 text-xs leading-relaxed text-zinc-400">
                    Famílias do catálogo via <strong class="text-zinc-200">Google Fonts</strong> (licenças OFL/Apache conforme a fonte)
                    e fontes de sistema quando disponíveis no Windows. Ver
                    <a href="https://fonts.google.com/" target="_blank" rel="noopener" class="text-teal-300 hover:underline">fonts.google.com</a>.
                </p>
            </section>

            <section>
                <h3 class="text-[10px] font-semibold uppercase tracking-[0.12em] text-teal-300/90">Tecnologias</h3>
                <ul class="mt-2 space-y-1.5 text-xs leading-relaxed text-zinc-400">
                    <li><strong class="text-zinc-200">Fabric.js</strong> — MIT · canvas do Image Studio</li>
                    <li><strong class="text-zinc-200">Alpine.js</strong> — MIT · interatividade da UI</li>
                    <li><strong class="text-zinc-200">Laravel / Vite</strong> — stack da aplicação</li>
                    <li>
                        <strong class="text-zinc-200">Remoção de fundo</strong> —
                        @if($bgDriver === 'imgly')
                            driver atual: <span class="text-amber-200">imgly</span> (@imgly/background-removal, licença AGPL — uso sujeito aos termos do pacote).
                        @elseif($bgDriver === 'off')
                            recurso desligado neste ambiente.
                        @else
                            driver padrão: <span class="text-emerald-300">rembg</span> (open source no servidor).
                            O pacote IMG.LY só carrega se o driver for explicitamente <code class="text-zinc-500">imgly</code>.
                        @endif
                    </li>
                </ul>
            </section>

            <section>
                <h3 class="text-[10px] font-semibold uppercase tracking-[0.12em] text-teal-300/90">Marcas de terceiros</h3>
                <p class="mt-2 text-xs leading-relaxed text-zinc-400">
                    Nomes e logotipos de redes sociais e produtos de terceiros pertencem aos respectivos titulares.
                    No MarkCraft aparecem só para identificação de formatos e ícones — sem endosso.
                </p>
            </section>

            <section>
                <h3 class="text-[10px] font-semibold uppercase tracking-[0.12em] text-teal-300/90">Conteúdo do usuário</h3>
                <p class="mt-2 text-xs leading-relaxed text-zinc-400">
                    Quem cria, envia ou publica artes é responsável pelo material.
                    Por padrão as artes não ficam guardadas no servidor — baixe e limpe o workspace.
                </p>
            </section>

            <p class="text-[11px] text-zinc-600 border-t border-zinc-800 pt-3">
                Lista completa: <code class="text-zinc-500">docs/CREDITS.md</code> no repositório · MarkCraft · família CriaSys
            </p>
        </div>
    </div>
</div>
