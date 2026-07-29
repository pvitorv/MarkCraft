<div
    :class="imageStudioExpanded ? 'fixed inset-0 z-[550] flex items-stretch justify-center bg-zinc-950/95 p-2 sm:p-3' : ''"
    @keydown.escape.window="imageStudioExpanded && !imageStudioElementsModalOpen && !imageStudioDimensionsModalOpen && !imageStudioTemplatesModalOpen && !imageStudioPacksModalOpen && closeImageStudioExpanded()"
>
    <div
        class="is-workspace-row flex flex-col lg:flex-row gap-3 min-h-0 w-full min-w-0 overflow-x-hidden"
        :class="imageStudioExpanded ? 'w-full max-w-[1600px] h-full max-h-[100dvh] rounded-xl border border-zinc-700 bg-zinc-950 p-2 sm:p-3 shadow-2xl overflow-hidden' : ''"
    >
        {{-- Barra lateral esquerda (abas) --}}
        @include('studio.partials.image_studio_aside')

        {{-- Área principal: toolbar mínima + canvas --}}
        <div class="flex-1 min-w-0 flex flex-col gap-2 min-h-0">
            {{-- Barra principal: formatos + zoom + tamanho (uma linha limpa) --}}
            <div class="studio-canvas-toolbar">
                <div class="studio-toolbar-block studio-toolbar-presets">
                    <span class="studio-toolbar-label">Formato</span>
                    <div class="studio-preset-chips">
                        <button
                            type="button"
                            class="studio-chip studio-chip-more"
                            @click="openImageStudioDimensionsModal()"
                            title="Capa, story, quadrado, redes, panfletos, banners e mais"
                        >
                            <strong>Todos os formatos</strong>
                            <small>Blog · redes · impressão</small>
                        </button>
                        <button
                            type="button"
                            class="studio-chip studio-chip-more"
                            @click="openImageStudioPacksModal()"
                            title="Templates de páginas, logomarcas e mockups com camadas editáveis"
                        >
                            <strong>Pacotes</strong>
                            <small>Web · marca · mockups</small>
                        </button>
                    </div>
                </div>

                <div class="studio-toolbar-block studio-toolbar-size">
                    <span class="studio-toolbar-label">Tamanho</span>
                    <div class="studio-size-row">
                        <label>
                            L
                            <input type="number" min="100" max="8000" step="1" x-model.number="imageStudioCustomWidth">
                        </label>
                        <span class="studio-times">×</span>
                        <label>
                            A
                            <input type="number" min="100" max="8000" step="1" x-model.number="imageStudioCustomHeight">
                        </label>
                        <button type="button" class="studio-btn studio-btn-primary" @click="applyImageStudioCustomDimensions()">Aplicar</button>
                        <span class="studio-aspect" x-text="imageStudioCanvasAspectLabel()"></span>
                    </div>
                </div>

                <div class="studio-toolbar-block studio-toolbar-zoom">
                    <span class="studio-toolbar-label">Zoom</span>
                    <div class="studio-zoom-row">
                        <button type="button" @click="imageStudioZoomOut()" title="Diminuir">−</button>
                        <input type="range" min="8" max="400" step="1" x-model.number="imageStudioZoom" @input="imageStudioSetZoomPercent(imageStudioZoom)">
                        <button type="button" @click="imageStudioZoomIn()" title="Aumentar">+</button>
                        <span class="tabular-nums" x-text="imageStudioZoom + '%'"></span>
                        <button type="button" class="studio-btn" @click="imageStudioZoomReset()">100%</button>
                        <button type="button" class="studio-btn" @click="fitImageStudioCanvas()">Ajustar</button>
                        <button
                            type="button"
                            class="studio-btn"
                            :class="imageStudioExpanded ? 'studio-btn-warn' : ''"
                            @click="toggleImageStudioExpanded()"
                            :title="imageStudioExpanded ? 'Recolher (Esc)' : 'Expandir'"
                            x-text="imageStudioExpanded ? 'Recolher' : 'Expandir'"
                        ></button>
                    </div>
                </div>
            </div>

            <div
                class="is-canvas-dropzone rounded-xl border border-zinc-700 bg-zinc-950 p-4 min-h-[320px] flex justify-center items-start relative flex-1 min-h-0"
                :class="{
                    'max-h-none': imageStudioExpanded,
                    'max-h-[min(85vh,920px)]': !imageStudioExpanded,
                    'is-canvas-dropzone--active': imageStudioFileDragOver,
                }"
                x-ref="imageStudioCanvasWrap"
                @dragenter.prevent="imageStudioOnFileDragEnter($event)"
                @dragover.prevent="imageStudioOnFileDragOver($event)"
                @dragleave="imageStudioOnFileDragLeave($event)"
                @drop.prevent="imageStudioOnFileDrop($event)"
            >
                <div
                    x-show="imageStudioFileDragOver"
                    x-cloak
                    class="pointer-events-none absolute inset-0 z-40 flex items-center justify-center rounded-xl bg-emerald-950/55"
                    aria-hidden="true"
                >
                    <div class="rounded-xl border border-emerald-400/70 bg-zinc-950/90 px-5 py-4 text-center shadow-xl">
                        <p class="text-sm font-semibold text-emerald-200">Solte a imagem aqui</p>
                        <p class="mt-1 text-[11px] text-zinc-400">PNG · JPG · WebP · GIF · SVG</p>
                    </div>
                </div>
                <div
                    x-show="imageStudioBgRemoving"
                    x-cloak
                    class="absolute inset-0 z-50 flex items-center justify-center rounded-xl bg-zinc-950/85 backdrop-blur-[2px]"
                    role="status"
                    aria-live="polite"
                    aria-busy="true"
                >
                    <div class="flex flex-col items-center gap-3 px-6 py-5 rounded-xl border border-emerald-700/50 bg-zinc-900/95 shadow-xl max-w-xs text-center">
                        <svg class="h-11 w-11 animate-spin text-emerald-400" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <circle class="opacity-20" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                            <path class="opacity-95" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-white">Removendo fundo com IA…</p>
                            <p class="text-[11px] text-zinc-400 mt-1">Isso costuma levar de 2 a 5 segundos. Não feche esta aba.</p>
                        </div>
                    </div>
                </div>
                <div class="inline-block shrink-0 overflow-hidden" :style="imageStudioCanvasViewportStyle()">
                    <div
                        x-ref="imageStudioCanvasScaler"
                        class="relative shadow-2xl shadow-black/40 ring-2 ring-violet-500/40 inline-block"
                        :style="imageStudioCanvasScalerStyle()"
                    >
                        <img
                            x-show="imageStudioUnderlayEnabled && getImageStudioUnderlayImageUrl()"
                            x-cloak
                            :src="getImageStudioUnderlayImageUrl()"
                            alt=""
                            class="absolute inset-0 w-full h-full object-cover pointer-events-none select-none"
                        >
                        <video
                            x-show="imageStudioUnderlayEnabled && getImageStudioUnderlayVideoUrl()"
                            x-cloak
                            :src="getImageStudioUnderlayVideoUrl()"
                            muted
                            playsinline
                            preload="metadata"
                            class="absolute inset-0 w-full h-full object-cover pointer-events-none select-none"
                            @loadeddata="$event.target.currentTime = Math.min(0.5, ($event.target.duration || 1) * 0.05)"
                        ></video>
                        <canvas x-ref="imageStudioCanvas" class="relative block"></canvas>
                    </div>
                </div>

                {{-- Menu de contexto (botão direito no canvas) --}}
                <div
                    x-show="imageStudioContextMenu.open"
                    x-cloak
                    class="studio-context-menu"
                    :style="`left:${imageStudioContextMenu.x}px;top:${imageStudioContextMenu.y}px`"
                    @click.stop
                    role="menu"
                >
                    <button type="button" role="menuitem" @click="imageStudioUndo(); closeImageStudioContextMenu()" :disabled="!imageStudioCanUndo">↶ Desfazer</button>
                    <button type="button" role="menuitem" @click="imageStudioRedo(); closeImageStudioContextMenu()" :disabled="!imageStudioCanRedo">↷ Refazer</button>
                    <hr>
                    <template x-if="imageStudioContextMenu.hasSelection">
                        <div class="studio-context-group">
                            <button type="button" role="menuitem" @click="imageStudioDuplicateSelection(); closeImageStudioContextMenu()">⧉ Duplicar</button>
                            <button type="button" role="menuitem" @click="imageStudioFlipSelection('x'); closeImageStudioContextMenu()">⇋ Espelhar H</button>
                            <button type="button" role="menuitem" @click="imageStudioFlipSelection('y'); closeImageStudioContextMenu()">⇅ Espelhar V</button>
                            <button type="button" role="menuitem" class="is-danger" @mousedown.prevent.stop="imageStudioDeleteSelection()">🗑 Excluir elemento</button>
                        </div>
                    </template>
                    <hr>
                    <button type="button" role="menuitem" class="is-warn" @click="closeImageStudioContextMenu(); imageStudioClearWorkspace()">⌫ Limpar área</button>
                </div>
            </div>
        </div>
    </div>
</div>

@include('studio.partials.image_studio_modals')
