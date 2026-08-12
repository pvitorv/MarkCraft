{{-- Canvas mobile — markup próprio (não usa is-canvas-dropzone / aside desktop) --}}
<section
    class="sm-canvas"
    :class="{ 'sm-canvas--drag': imageStudioFileDragOver }"
    x-ref="imageStudioCanvasWrap"
    @dragenter.prevent="imageStudioOnFileDragEnter($event)"
    @dragover.prevent="imageStudioOnFileDragOver($event)"
    @dragleave="imageStudioOnFileDragLeave($event)"
    @drop.prevent="imageStudioOnFileDrop($event)"
>
    <div x-show="imageStudioFileDragOver" x-cloak class="sm-canvas__drop-hint" aria-hidden="true">
        <p>Solte a imagem aqui</p>
    </div>

    <div x-show="imageStudioBgRemoving" x-cloak class="sm-canvas__loading" role="status" aria-live="polite" aria-busy="true">
        <svg class="sm-canvas__spinner" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle class="opacity-20" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
            <path class="opacity-95" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <p>Removendo fundo…</p>
    </div>

    <div class="sm-canvas__viewport" :style="imageStudioCanvasViewportStyle()">
        <div x-ref="imageStudioCanvasScaler" class="sm-canvas__artboard" :style="imageStudioCanvasScalerStyle()">
            <canvas x-ref="imageStudioCanvas"></canvas>
        </div>
    </div>
</section>
