{{-- Sidebar estilo Canva: trilho de ícones + gaveta por aba --}}
@php
    $blogUrl = trim((string) ($cmsBlog['url'] ?? '#')) ?: '#';
    $blogRegister = trim((string) ($cmsBlog['register_url'] ?? $blogUrl)) ?: $blogUrl;
    $blogName = $cmsBlog['name'] ?? 'Blog CriaSys Web';
@endphp
<aside
    class="is-workspace-aside is-sidebar-shell w-full lg:w-[320px] shrink-0 flex flex-col sm:flex-row border-b border-zinc-800/80 lg:border-b-0 lg:border-r pb-3 lg:pb-0 lg:pr-1"
    :class="imageStudioExpanded ? 'max-h-full min-h-0 overflow-hidden' : 'max-h-[min(48vh,420px)] lg:max-h-[min(82vh,920px)]'"
>
    <nav class="is-sidebar-rail flex sm:flex-col gap-1 p-1.5 sm:p-2 overflow-x-auto sm:overflow-x-visible shrink-0 border-b sm:border-b-0 sm:border-r border-zinc-800/80" aria-label="Painéis do Studio">
        @php
            $tabs = [
                ['id' => 'tools', 'label' => 'Ferramentas', 'paths' => ['M4 6h16', 'M4 12h16', 'M4 18h10']],
                ['id' => 'text', 'label' => 'Texto', 'paths' => ['M4 7V5h16v2', 'M12 5v14', 'M9 19h6']],
                ['id' => 'media', 'label' => 'Mídia', 'paths' => ['M4 5h16v14H4z', 'M8 15l3-3 2 2 3-4 4 5H8z']],
                ['id' => 'bg', 'label' => 'Fundo', 'paths' => ['M12 3l8 4v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V7l8-4z']],
                ['id' => 'layers', 'label' => 'Camadas', 'paths' => ['M12 3l9 5-9 5-9-5 9-5z', 'M3 13l9 5 9-5', 'M3 17l9 5 9-5']],
                ['id' => 'slides', 'label' => 'Sequência', 'paths' => ['M4 5h16v12H4z', 'M8 21h8', 'M12 17v4']],
                ['id' => 'export', 'label' => 'Exportar', 'paths' => ['M12 3v12', 'M8 11l4 4 4-4', 'M5 21h14']],
            ];
        @endphp
        @foreach($tabs as $tab)
            <button
                type="button"
                @click="setImageStudioSidebarTab('{{ $tab['id'] }}')"
                class="is-rail-btn relative"
                :class="imageStudioSidebarTab === '{{ $tab['id'] }}' ? 'is-rail-btn--active' : ''"
                title="{{ $tab['label'] }}"
                :aria-pressed="imageStudioSidebarTab === '{{ $tab['id'] }}'"
            >
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    @foreach($tab['paths'] as $d)
                        <path d="{{ $d }}"/>
                    @endforeach
                </svg>
                <span class="is-rail-label">{{ $tab['label'] }}</span>
                @if($tab['id'] === 'layers')
                    <span
                        x-show="imageStudioActiveLayerId"
                        x-cloak
                        class="is-rail-layer-badge"
                        title="Camada em edição"
                    ></span>
                @endif
            </button>
        @endforeach
        {{-- Só Electron: pasta local do PC --}}
        <button
            type="button"
            x-show="isMarkCraftDesktopApp()"
            x-cloak
            @click="openDesktopWorkspaceModal()"
            class="is-rail-btn relative"
            :class="desktopWorkspaceModalOpen ? 'is-rail-btn--active' : ''"
            title="Abrir do PC"
            aria-label="Abrir do PC"
        >
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>
            </svg>
            <span class="is-rail-label">Abrir</span>
            <span
                x-show="desktopWorkspaceFolder"
                x-cloak
                class="is-rail-layer-badge"
                title="Biblioteca configurada"
            ></span>
        </button>
    </nav>

    <div class="is-sidebar-drawer flex-1 min-w-0 overflow-y-auto overscroll-contain space-y-3 p-2 sm:p-3">
        {{-- Ferramentas --}}
        <div x-show="imageStudioSidebarTab === 'tools'" x-cloak class="space-y-3">
            <div class="rounded-xl border border-zinc-800 bg-zinc-950/50 p-3 space-y-2">
                <p class="text-xs font-medium text-zinc-300">Ferramentas</p>
                <div class="flex flex-wrap gap-1.5">
                    <button type="button" @click="imageStudioUndo()" :disabled="!imageStudioCanUndo" class="text-[10px] px-2 py-1 rounded bg-zinc-800 hover:bg-zinc-700 disabled:opacity-40" title="Ctrl+Z">Desfazer</button>
                    <button type="button" @click="imageStudioRedo()" :disabled="!imageStudioCanRedo" class="text-[10px] px-2 py-1 rounded bg-zinc-800 hover:bg-zinc-700 disabled:opacity-40" title="Ctrl+Y">Refazer</button>
                    <button type="button" @mousedown.prevent.stop="imageStudioDeleteSelection()" class="text-[10px] px-2 py-1 rounded bg-rose-950 hover:bg-rose-900 border border-rose-800/70 text-rose-100" title="Excluir (Delete)">Excluir</button>
                    <button type="button" @click="imageStudioClearWorkspace()" class="text-[10px] px-2 py-1 rounded bg-zinc-800 hover:bg-zinc-700 border border-zinc-600" title="Limpa o canvas">Limpar área</button>
                    <button type="button" @click="setImageStudioSidebarTab('text'); imageStudioAddText()" class="text-[10px] px-2 py-1 rounded bg-violet-800 hover:bg-violet-700">+ Texto</button>
                    <button type="button" @click="imageStudioOpenElementsModal()" class="text-[10px] px-2 py-1 rounded bg-violet-900 hover:bg-violet-800 border border-violet-700">Elementos</button>
                    <button type="button" @click="openImageStudioTemplatesModal()" class="text-[10px] px-2 py-1 rounded bg-fuchsia-900 hover:bg-fuchsia-800 border border-fuchsia-700">Layouts</button>
                    <button type="button" @click="openImageStudioPacksModal()" class="text-[10px] px-2 py-1 rounded bg-amber-900 hover:bg-amber-800 border border-amber-700" title="Pacotes">Pacotes</button>
                    <label class="text-[10px] px-2 py-1 rounded bg-zinc-800 hover:bg-zinc-700 cursor-pointer">
                        + Imagem / PSD
                        <input type="file" accept="image/*,.psd,image/vnd.adobe.photoshop" @change="imageStudioUploadImage($event)" class="hidden">
                    </label>
                </div>
                <div class="flex flex-wrap gap-1.5 pt-1 border-t border-zinc-800" x-show="imageStudioBrand">
                    <button type="button" @click="imageStudioApplyBrandKit()" class="text-[10px] px-2 py-1 rounded bg-amber-900/70 hover:bg-amber-800 border border-amber-700/60 text-amber-50" title="Aplica logo, cores e nome do blog">
                        Marca do blog
                    </button>
                    <span class="text-[9px] text-zinc-500 self-center truncate" x-text="imageStudioBrand?.name"></span>
                </div>
                <div class="flex flex-wrap gap-1.5 pt-1 border-t border-zinc-800">
                    <label class="text-[10px] px-2 py-1 rounded bg-zinc-800 flex items-center gap-1 cursor-pointer">
                        <input type="checkbox" x-model="imageStudioShowFormatGuides" @change="onImageStudioFormatGuidesChange()" class="rounded"> Sangrias
                    </label>
                    <label class="text-[10px] px-2 py-1 rounded bg-zinc-800 flex items-center gap-1 cursor-pointer">
                        <input type="checkbox" x-model="imageStudioShowGrid" @change="onImageStudioGridChange()" class="rounded"> Grid
                    </label>
                    <label class="text-[10px] px-2 py-1 rounded bg-zinc-800 flex items-center gap-1 cursor-pointer">
                        <input type="checkbox" x-model="imageStudioSnapGrid" @change="onImageStudioGridChange()" class="rounded"> Snap
                    </label>
                    <select x-model.number="imageStudioGridSize" @change="onImageStudioGridChange()" class="text-[10px] px-2 py-1 rounded bg-zinc-800 border border-zinc-700">
                        <option :value="10">10px</option>
                        <option :value="20">20px</option>
                        <option :value="40">40px</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Texto --}}
        <div x-show="imageStudioSidebarTab === 'text'" x-cloak class="space-y-3">
            <div class="rounded-xl border border-violet-900/50 bg-violet-950/20 p-3 space-y-3">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-xs font-semibold text-violet-200">Texto</p>
                    <button type="button" @click="imageStudioAddText()" class="text-[10px] px-2 py-1 rounded bg-violet-700 hover:bg-violet-600 text-white">+ Adicionar</button>
                </div>
                <p class="text-[9px] text-zinc-500">Enter no campo ou no canvas = nova linha. Ícones em <button type="button" @click="imageStudioOpenElementsModal()" class="text-violet-400 hover:text-violet-200 underline">Elementos</button>.</p>
                <label class="text-[10px] text-zinc-400 block">
                    Conteúdo
                    <textarea x-model="imageStudioTextContent" @input="imageStudioOnTextControlChange()" rows="3" class="w-full mt-1 text-xs px-2 py-1.5 rounded bg-zinc-900 border border-zinc-700 resize-y" placeholder="Linha 1&#10;Linha 2"></textarea>
                </label>
                <input type="search" x-model="imageStudioFontFilter" @input="imageStudioFilterFontList()" placeholder="Buscar fonte…" class="w-full text-xs px-2 py-1.5 rounded bg-zinc-900 border border-zinc-700">
                <p class="text-[10px] text-zinc-500 flex justify-between gap-2">
                    <span class="text-emerald-400">{{ count($imageStudioCatalog['fonts'] ?? []) }} fontes</span>
                    <span class="text-violet-400 truncate" x-text="imageStudioFontMap[imageStudioTextFontSlug]?.label || imageStudioTextFontSlug || '—'"></span>
                </p>
                <div x-ref="imageStudioFontList" class="max-h-52 overflow-y-auto rounded-lg border border-zinc-700 bg-zinc-900">
                    @forelse($imageStudioCatalog['fonts'] ?? [] as $font)
                        <button
                            type="button"
                            data-font-slug="{{ $font['slug'] }}"
                            data-font-label="{{ $font['label'] ?? $font['slug'] }}"
                            data-font-group="{{ $font['group_label'] ?? $font['group'] ?? '' }}"
                            @click="imageStudioSelectFont('{{ $font['slug'] }}')"
                            class="is-font-row w-full text-left text-xs px-2 py-1.5 border-b border-zinc-800/60 hover:bg-violet-950/40 transition flex items-center justify-between gap-2 text-zinc-300"
                            :class="imageStudioTextFontSlug === '{{ $font['slug'] }}' ? 'bg-violet-900/50 text-violet-100' : ''"
                        >
                            <span class="truncate">
                                {{ $font['label'] ?? $font['slug'] }}
                                <span class="text-[9px] text-zinc-500 ml-1">· {{ $font['group_label'] ?? $font['group'] ?? '' }}</span>
                            </span>
                            @php($src = $font['source'] ?? 'system')
                            <span class="text-[8px] shrink-0 px-1 rounded {{ $src === 'google' ? 'bg-emerald-900/60 text-emerald-300' : ($src === 'icon' ? 'bg-sky-900/60 text-sky-300' : 'bg-zinc-800 text-zinc-500') }}">
                                {{ $src === 'google' ? 'Google' : ($src === 'icon' ? 'Ícone' : 'Win') }}
                            </span>
                        </button>
                    @empty
                        <p class="text-[10px] text-red-400 p-3">Catálogo PHP vazio — rode <code class="text-red-300">php artisan config:clear</code></p>
                    @endforelse
                </div>
                <button type="button" @click="loadImageStudioCatalog()" class="text-[10px] text-violet-400 hover:text-violet-200 underline">Atualizar catálogo via API</button>
                <div class="flex flex-wrap gap-1">
                    <button type="button" @click="imageStudioToggleTextBold()" class="text-xs px-2 py-1 rounded border" :class="imageStudioTextBold ? 'bg-violet-700 border-violet-500 text-white' : 'bg-zinc-800 border-zinc-700 text-zinc-400'" title="Negrito"><strong>B</strong></button>
                    <button type="button" @click="imageStudioToggleTextItalic()" class="text-xs px-2 py-1 rounded border italic" :class="imageStudioTextItalic ? 'bg-violet-700 border-violet-500 text-white' : 'bg-zinc-800 border-zinc-700 text-zinc-400'" title="Itálico">I</button>
                    <button type="button" @click="imageStudioToggleTextUnderline()" class="text-xs px-2 py-1 rounded border underline" :class="imageStudioTextUnderline ? 'bg-violet-700 border-violet-500 text-white' : 'bg-zinc-800 border-zinc-700 text-zinc-400'" title="Sublinhado">U</button>
                    <button type="button" @click="imageStudioToggleTextLinethrough()" class="text-xs px-2 py-1 rounded border line-through" :class="imageStudioTextLinethrough ? 'bg-violet-700 border-violet-500 text-white' : 'bg-zinc-800 border-zinc-700 text-zinc-400'" title="Tachado">S</button>
                    <span class="w-px bg-zinc-700 mx-0.5"></span>
                    <button type="button" @click="imageStudioSetTextAlign('left')" class="text-xs px-2 py-1 rounded" :class="imageStudioTextAlign === 'left' ? 'bg-violet-700 text-white' : 'bg-zinc-800 text-zinc-400'">⬅</button>
                    <button type="button" @click="imageStudioSetTextAlign('center')" class="text-xs px-2 py-1 rounded" :class="imageStudioTextAlign === 'center' ? 'bg-violet-700 text-white' : 'bg-zinc-800 text-zinc-400'">↔</button>
                    <button type="button" @click="imageStudioSetTextAlign('right')" class="text-xs px-2 py-1 rounded" :class="imageStudioTextAlign === 'right' ? 'bg-violet-700 text-white' : 'bg-zinc-800 text-zinc-400'">➡</button>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <label class="text-[10px] text-zinc-400 block">
                        Cor do texto
                        <input type="color" x-model="imageStudioTextFill" @input="imageStudioOnTextFillChange()" @change="imageStudioOnTextFillChange()" class="w-full mt-1 h-8 rounded bg-zinc-800 border border-zinc-700 cursor-pointer">
                    </label>
                    <label class="text-[10px] text-zinc-400 block">
                        Cor do contorno
                        <input type="color" x-model="imageStudioTextStroke" @input="imageStudioOnTextStrokeChange()" @change="imageStudioOnTextStrokeChange()" class="w-full mt-1 h-8 rounded bg-zinc-800 border border-zinc-700 cursor-pointer">
                    </label>
                </div>
                <label class="text-[10px] text-zinc-400 block">
                    Tamanho <span class="text-zinc-500 tabular-nums" x-text="imageStudioTextSize + 'px'"></span>
                    <input type="range" min="12" max="320" step="1" x-model.number="imageStudioTextSize" @pointerdown="imageStudioBeginControlDrag($event)" @pointerup="imageStudioEndControlDrag()" @pointercancel="imageStudioEndControlDrag()" @change="imageStudioEndControlDrag()" @input="imageStudioOnTextControlChange()" class="w-full mt-1 accent-violet-500 is-control-range">
                </label>
                <div class="flex flex-wrap items-end gap-2">
                    <label class="text-[10px] text-zinc-400 block flex-1 min-w-[10rem]">
                        Espessura contorno
                        <span class="text-zinc-500 tabular-nums" x-text="imageStudioTextStrokeWidth <= 0 ? ' (sem contorno)' : ' (' + imageStudioTextStrokeWidth + 'px)'"></span>
                        <input type="range" min="0" max="24" step="1" x-model.number="imageStudioTextStrokeWidth" @pointerdown="imageStudioBeginControlDrag($event)" @pointerup="imageStudioEndControlDrag()" @pointercancel="imageStudioEndControlDrag()" @change="imageStudioEndControlDrag()" @input="imageStudioOnTextControlChange()" class="w-full mt-1 accent-violet-500 is-control-range">
                    </label>
                    <button type="button" @click="imageStudioRemoveTextOutline()" class="text-[10px] px-2 py-1.5 rounded bg-zinc-800 hover:bg-zinc-700 text-zinc-300 shrink-0">Sem contorno</button>
                </div>
                <label class="text-[10px] text-zinc-400 block">
                    Espaçamento letras
                    <input type="range" min="-50" max="400" step="5" x-model.number="imageStudioTextCharSpacing" @pointerdown="imageStudioBeginControlDrag($event)" @pointerup="imageStudioEndControlDrag()" @pointercancel="imageStudioEndControlDrag()" @change="imageStudioEndControlDrag()" @input="imageStudioOnTextControlChange()" class="w-full mt-1 accent-violet-500 is-control-range">
                </label>
                <label class="text-[10px] text-zinc-400 flex items-center gap-2">
                    <input type="checkbox" x-model="imageStudioTextShadow" @change="imageStudioOnTextControlChange()" class="rounded"> Sombra
                </label>
                <div x-show="imageStudioTextShadow" class="grid grid-cols-2 gap-2">
                    <input type="color" x-model="imageStudioTextShadowColor" @input="imageStudioOnTextControlChange()" class="w-full h-8 rounded bg-zinc-800 border border-zinc-700 cursor-pointer">
                    <input type="range" min="0" max="40" x-model.number="imageStudioTextShadowBlur" @pointerdown="imageStudioBeginControlDrag($event)" @pointerup="imageStudioEndControlDrag()" @pointercancel="imageStudioEndControlDrag()" @change="imageStudioEndControlDrag()" @input="imageStudioOnTextControlChange()" class="w-full accent-violet-500 is-control-range" title="Desfoque sombra">
                </div>
            </div>
        </div>

        {{-- Mídia + rembg --}}
        <div x-show="imageStudioSidebarTab === 'media'" x-cloak class="space-y-3">
            <div class="rounded-xl border border-zinc-800 bg-zinc-950/50 p-3 space-y-2">
                <p class="text-xs font-medium text-zinc-300">Mídia</p>
                <label class="text-[10px] px-2 py-2 rounded bg-zinc-800 hover:bg-zinc-700 cursor-pointer inline-flex w-full justify-center">
                    + Enviar imagem
                    <input type="file" accept="image/*,.psd,image/vnd.adobe.photoshop" @change="imageStudioUploadImage($event)" class="hidden">
                </label>
            </div>

            <div class="is-rembg-card rounded-xl border border-emerald-500/50 bg-emerald-950/25 p-3 space-y-2">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-xs font-semibold text-emerald-200">Remover fundo</p>
                    <span class="is-rembg-badge">GRÁTIS</span>
                </div>
                <p class="text-[10px] text-emerald-100/80 leading-snug">Motor rembg no servidor — sem Canva Pro.</p>
                <div class="flex flex-col gap-1.5">
                    <label class="is-rembg-btn text-[10px] px-2 py-2 rounded cursor-pointer inline-flex items-center justify-center gap-1.5" :class="imageStudioBgRemoving ? 'opacity-70 pointer-events-none' : ''">
                        <span x-show="!imageStudioBgRemoving">Remover fundo (arquivo)</span>
                        <span x-show="imageStudioBgRemoving" x-cloak class="inline-flex items-center gap-1.5">
                            <svg class="h-3 w-3 animate-spin shrink-0" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Processando…
                        </span>
                        <input type="file" accept="image/*" @change="imageStudioRemoveBackground($event)" class="hidden" :disabled="imageStudioBgRemoving">
                    </label>
                    <button type="button" @pointerdown.prevent.stop="imageStudioRemoveBgFromSelection()" :disabled="imageStudioBgRemoving" class="is-rembg-btn text-[10px] px-2 py-2 rounded disabled:opacity-40 inline-flex items-center justify-center gap-1.5" title="Remove fundo da imagem selecionada">
                        <span x-show="!imageStudioBgRemoving">Remover fundo da seleção</span>
                        <span x-show="imageStudioBgRemoving" x-cloak class="inline-flex items-center gap-1.5">
                            <svg class="h-3 w-3 animate-spin shrink-0" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Processando…
                        </span>
                    </button>
                </div>
                <p class="text-[9px] text-zinc-500 w-full" x-show="imageStudioBgRemovalLabel" x-text="'Motor: ' + imageStudioBgRemovalLabel"></p>
                <p class="text-[9px] text-amber-400/90 w-full" x-show="!imageStudioBgRemoval">
                    Remoção indisponível. Com rembg: pip install rembg pillow onnxruntime
                </p>
            </div>

            <template x-if="imageStudioSelectedObject?.type === 'image'">
                <div class="rounded-xl border border-zinc-800 bg-zinc-950/50 p-3 space-y-2">
                    <p class="text-[10px] text-violet-400 font-medium">Filtros da imagem</p>
                    <label class="text-[10px] text-zinc-400 block">Brilho<input type="range" min="0" max="100" x-model.number="imageStudioFilters.brightness" @pointerdown="imageStudioBeginControlDrag($event)" @pointerup="imageStudioEndControlDrag()" @pointercancel="imageStudioEndControlDrag()" @change="imageStudioEndControlDrag()" @input="imageStudioApplyFilters()" class="w-full mt-1 is-control-range"></label>
                    <label class="text-[10px] text-zinc-400 block">Contraste<input type="range" min="0" max="100" x-model.number="imageStudioFilters.contrast" @pointerdown="imageStudioBeginControlDrag($event)" @pointerup="imageStudioEndControlDrag()" @pointercancel="imageStudioEndControlDrag()" @change="imageStudioEndControlDrag()" @input="imageStudioApplyFilters()" class="w-full mt-1 is-control-range"></label>
                    <label class="text-[10px] text-zinc-400 block">Saturação<input type="range" min="0" max="100" x-model.number="imageStudioFilters.saturation" @pointerdown="imageStudioBeginControlDrag($event)" @pointerup="imageStudioEndControlDrag()" @pointercancel="imageStudioEndControlDrag()" @change="imageStudioEndControlDrag()" @input="imageStudioApplyFilters()" class="w-full mt-1 is-control-range"></label>
                    <label class="text-[10px] text-zinc-400 block">Desfoque<input type="range" min="0" max="100" x-model.number="imageStudioFilters.blur" @pointerdown="imageStudioBeginControlDrag($event)" @pointerup="imageStudioEndControlDrag()" @pointercancel="imageStudioEndControlDrag()" @change="imageStudioEndControlDrag()" @input="imageStudioApplyFilters()" class="w-full mt-1 is-control-range"></label>
                    <label class="text-[10px] text-zinc-400 block">P&B<input type="range" min="0" max="100" x-model.number="imageStudioFilters.grayscale" @pointerdown="imageStudioBeginControlDrag($event)" @pointerup="imageStudioEndControlDrag()" @pointercancel="imageStudioEndControlDrag()" @change="imageStudioEndControlDrag()" @input="imageStudioApplyFilters()" class="w-full mt-1 is-control-range"></label>
                    <button type="button" @click="imageStudioClearFilters()" class="text-[10px] px-2 py-1 rounded bg-zinc-800 text-zinc-400 hover:text-white">Limpar filtros</button>
                </div>
            </template>
        </div>

        {{-- Fundo --}}
        <div x-show="imageStudioSidebarTab === 'bg'" x-cloak class="space-y-3">
            <div class="rounded-xl border border-zinc-800 bg-zinc-950/50 p-3 space-y-2">
                <p class="text-xs font-medium text-zinc-300">Fundo do canvas</p>
                <label class="text-xs text-zinc-400 block">
                    Cor de fundo
                    <input type="color" x-model="imageStudioBgColor" @input="onImageStudioBgChange()" class="w-full mt-1 h-9 rounded bg-zinc-800 border border-zinc-700 cursor-pointer">
                </label>
                <label class="text-xs text-zinc-400 block">
                    Transparência
                    <input type="range" min="0" max="100" x-model.number="imageStudioBgTransparency" @pointerdown="imageStudioBeginControlDrag($event)" @pointerup="imageStudioEndControlDrag()" @pointercancel="imageStudioEndControlDrag()" @change="imageStudioEndControlDrag()" @input="onImageStudioBgChange()" class="w-full mt-2 is-control-range">
                    <span class="text-[10px] text-zinc-500" x-text="imageStudioBgTransparency + '%'"></span>
                </label>
                <div class="pt-2 border-t border-zinc-800 space-y-2" x-show="(slides || []).length > 0">
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-[10px] font-medium text-zinc-300">Slide por trás</p>
                        <label class="text-[10px] text-zinc-400 inline-flex items-center gap-1.5 cursor-pointer">
                            <input type="checkbox" x-model="imageStudioUnderlayEnabled" @change="onImageStudioUnderlayChange()" class="rounded">
                            Ativo
                        </label>
                    </div>
                    <label class="text-[10px] text-zinc-400 block" x-show="imageStudioUnderlayEnabled">
                        Slide visível
                        <select x-model.number="imageStudioUnderlaySlideIndex" @change="onImageStudioUnderlayChange()" class="w-full mt-1 rounded bg-zinc-800 border border-zinc-700 px-2 py-1.5 text-sm">
                            <option :value="-1">Slide selecionado no editor</option>
                            <template x-for="(slide, idx) in slides" :key="'is-underlay-' + slide.id">
                                <option :value="idx" x-text="'Slide ' + (idx + 1) + (slide.video_path ? ' (vídeo)' : slide.image_url ? ' (imagem)' : '')"></option>
                            </template>
                        </select>
                    </label>
                </div>
            </div>
        </div>

        {{-- Camadas + objeto --}}
        <div x-show="imageStudioSidebarTab === 'layers'" x-cloak class="space-y-3">
            @include('studio.partials.image_studio_sidebar_panels', ['mode' => 'layers'])
        </div>

        {{-- Sequência: PPT / carrossel redes / carrossel web --}}
        <div x-show="imageStudioSidebarTab === 'slides'" x-cloak class="space-y-3">
            <div class="rounded-xl border border-violet-900/40 bg-violet-950/20 p-3 space-y-3">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-xs font-medium text-violet-200">Sequência / kit</p>
                    <span class="text-[10px] text-zinc-500 tabular-nums" x-text="(imageStudioDeckPageIndex + 1) + ' / ' + imageStudioDeckPages.length"></span>
                </div>
                <p class="text-[10px] text-zinc-500 leading-snug">
                    O mesmo deck serve para PowerPoint, carrossel de redes e frames de site. Monte as páginas e exporte ZIP, PDF ou PPTX.
                </p>

                <div class="space-y-1.5">
                    <p class="text-[10px] font-medium text-zinc-400">Tipo de kit</p>
                    <div class="flex flex-col gap-1">
                        <template x-for="kind in imageStudioDeckKindDefs()" :key="'deck-kind-' + kind.id">
                            <button
                                type="button"
                                class="text-left text-[10px] px-2 py-1.5 rounded border transition"
                                :class="imageStudioDeckKind === kind.id
                                    ? 'bg-violet-800 border-violet-500 text-violet-50'
                                    : 'bg-zinc-900 border-zinc-700 text-zinc-300 hover:bg-zinc-800'"
                                @click="setImageStudioDeckKind(kind.id)"
                            >
                                <span class="font-semibold" x-text="kind.label"></span>
                                <span class="block text-[9px] opacity-70" x-text="kind.hint"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <p class="text-[10px] font-medium text-zinc-400">Formato do frame</p>
                    <div class="flex flex-wrap gap-1">
                        <template x-for="preset in (imageStudioDeckKindMeta()?.presets || [])" :key="'deck-preset-' + preset.slug">
                            <button
                                type="button"
                                class="text-[10px] px-2 py-1 rounded bg-zinc-800 hover:bg-violet-800"
                                :class="imageStudioPreset === preset.slug ? 'ring-1 ring-violet-400' : ''"
                                @click="switchImageStudioPreset(preset.slug)"
                                x-text="preset.label"
                            ></button>
                        </template>
                    </div>
                </div>

                <div class="studio-deck-row">
                    <template x-for="(page, idx) in imageStudioDeckPages" :key="page.id">
                        <button
                            type="button"
                            class="studio-deck-chip"
                            :class="idx === imageStudioDeckPageIndex ? 'is-active' : ''"
                            :disabled="imageStudioDeckBusy"
                            @click="selectImageStudioDeckPage(idx)"
                            x-text="page.name || imageStudioDeckPageLabel(idx)"
                        ></button>
                    </template>
                </div>

                <div class="flex flex-wrap gap-1.5">
                    <button type="button" class="text-[10px] px-2 py-1 rounded bg-zinc-800 hover:bg-violet-800" :disabled="imageStudioDeckBusy" @click="imageStudioDeckAddPage()" x-text="'+ ' + imageStudioDeckUnitLabel()"></button>
                    <button type="button" class="text-[10px] px-2 py-1 rounded bg-zinc-800 hover:bg-violet-800" :disabled="imageStudioDeckBusy" @click="imageStudioDeckDuplicatePage()">Duplicar</button>
                    <button type="button" class="text-[10px] px-2 py-1 rounded bg-zinc-800 hover:bg-rose-900" :disabled="imageStudioDeckBusy || imageStudioDeckPages.length <= 1" @click="imageStudioDeckDeletePage()">Excluir</button>
                    <button type="button" class="text-[10px] px-2 py-1 rounded bg-zinc-800 hover:bg-zinc-700" :disabled="imageStudioDeckBusy || imageStudioDeckPageIndex <= 0" @click="imageStudioDeckMovePage(-1)">↑</button>
                    <button type="button" class="text-[10px] px-2 py-1 rounded bg-zinc-800 hover:bg-zinc-700" :disabled="imageStudioDeckBusy || imageStudioDeckPageIndex >= imageStudioDeckPages.length - 1" @click="imageStudioDeckMovePage(1)">↓</button>
                </div>

                <div class="pt-1 border-t border-violet-900/40 space-y-2">
                    <p class="text-[10px] font-medium text-zinc-400">Exportar kit</p>
                    <button
                        type="button"
                        class="w-full text-[10px] px-2 py-1.5 rounded bg-teal-800 hover:bg-teal-700"
                        :disabled="imageStudioDeckBusy"
                        @click="imageStudioExport('zip')"
                    >
                        Baixar ZIP (PNG sequência)
                    </button>
                    <button
                        type="button"
                        class="w-full text-[10px] px-2 py-1.5 rounded bg-zinc-800 hover:bg-zinc-700"
                        :disabled="imageStudioDeckBusy"
                        @click="imageStudioExport('pdf')"
                    >
                        Baixar PDF multipágina
                    </button>
                    <button
                        type="button"
                        class="w-full text-[10px] px-2 py-1.5 rounded bg-violet-800 hover:bg-violet-700"
                        :disabled="imageStudioDeckBusy"
                        @click="imageStudioExport('pptx')"
                    >
                        Baixar PowerPoint (.pptx)
                    </button>
                </div>
            </div>
        </div>

        {{-- Export + ponte Blog --}}
        <div x-show="imageStudioSidebarTab === 'export'" x-cloak class="space-y-3">
            @include('studio.partials.image_studio_sidebar_panels', ['mode' => 'export'])
            @unless(!empty($markcraftDesktop))
            <div class="rounded-xl border border-teal-500/30 bg-teal-950/20 p-3 space-y-2">
                <p class="text-xs font-medium text-teal-200">Próximo passo · {{ $blogName }}</p>
                <p class="text-[10px] text-zinc-400 leading-snug">{{ $cmsStudio['aside_blog_blurb'] ?? 'Monte a arte aqui e continue no painel do Blog — posts, afiliados e Image Studio no mesmo fluxo.' }}</p>
                <a
                    href="{{ $blogUrl }}"
                    class="is-blog-bridge-btn flex w-full items-center justify-center rounded-lg px-3 py-2.5 text-xs font-semibold"
                    @if($blogUrl !== '#' && !str_starts_with($blogUrl, '#')) target="_blank" rel="noopener" @endif
                >
                    Usar no {{ $blogName }}
                </a>
                @if($blogRegister !== $blogUrl)
                    <a
                        href="{{ $blogRegister }}"
                        class="flex w-full items-center justify-center rounded-lg border border-teal-500/35 px-3 py-2 text-[11px] font-medium text-teal-100 hover:bg-teal-500/10"
                        @if($blogRegister !== '#' && !str_starts_with($blogRegister, '#')) target="_blank" rel="noopener" @endif
                    >
                        Criar conta no Blog
                    </a>
                @endif
            </div>
            @endunless
            @if(!empty($markcraftDesktop))
            <div class="rounded-xl border border-emerald-500/30 bg-emerald-950/20 p-3 space-y-2">
                <p class="text-xs font-medium text-emerald-200">Modo desktop</p>
                <p class="text-[10px] text-zinc-400 leading-snug">
                    Botão <strong class="text-emerald-300">Abrir</strong>: escolha uma pasta no PC e puxe arte, projeto ou PSD para o Studio sem sair do app.
                </p>
                <button type="button" class="w-full text-[10px] py-1.5 rounded-lg bg-emerald-900/60 hover:bg-emerald-800 border border-emerald-700/50" @click="openDesktopWorkspaceModal()" x-show="isMarkCraftDesktopApp()" x-cloak>
                    Abrir do PC
                </button>
            </div>
            @endif
        </div>
    </div>
</aside>
