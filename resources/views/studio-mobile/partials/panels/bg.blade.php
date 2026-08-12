<div class="sm-panel" x-show="imageStudioSidebarTab === 'bg'" x-cloak>
    <div class="sm-panel__section sm-panel__section--rembg">
        <p class="sm-panel__title">Remover fundo</p>
        <label class="sm-btn sm-btn--rembg sm-btn--block" :class="imageStudioBgRemoving ? 'opacity-60 pointer-events-none' : ''">
            <span x-show="!imageStudioBgRemoving">Enviar imagem (rembg)</span>
            <span x-show="imageStudioBgRemoving" x-cloak>Processando…</span>
            <input type="file" accept="image/*" @change="imageStudioRemoveBackground($event)" class="hidden" :disabled="imageStudioBgRemoving">
        </label>
        <button type="button" class="sm-btn sm-btn--rembg sm-btn--block" @click="imageStudioRemoveBgFromSelection()" :disabled="imageStudioBgRemoving">
            Remover fundo da seleção
        </button>
        <p class="sm-hint" x-show="imageStudioBgRemovalLabel" x-text="'Motor: ' + imageStudioBgRemovalLabel"></p>
    </div>

    <div class="sm-panel__section">
        <p class="sm-panel__title">Fundo do canvas</p>
        <label class="sm-field">
            Cor
            <input type="color" x-model="imageStudioBgColor" @input="onImageStudioBgChange()" class="sm-color">
        </label>
        <label class="sm-field">
            Transparência <span x-text="imageStudioBgTransparency + '%'"></span>
            <input type="range" min="0" max="100" x-model.number="imageStudioBgTransparency" @input="onImageStudioBgChange()" class="sm-range">
        </label>
        <p class="sm-hint">100% = PNG sem fundo do canvas (xadrez na tela).</p>
    </div>
</div>
