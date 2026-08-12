<div class="sm-panel" x-show="imageStudioSidebarTab === 'tools'" x-cloak>
    <div class="sm-panel__grid">
        <button type="button" class="sm-btn" @click="imageStudioUndo()" :disabled="!imageStudioCanUndo">Desfazer</button>
        <button type="button" class="sm-btn" @click="imageStudioRedo()" :disabled="!imageStudioCanRedo">Refazer</button>
        <label class="sm-btn sm-btn--primary">
            + Imagem
            <input type="file" accept="image/*,.psd,image/vnd.adobe.photoshop" @change="imageStudioUploadImage($event)" class="hidden">
        </label>
        <button type="button" class="sm-btn" @click="openImageStudioTemplatesModal(); closeImageStudioMobileSheet()">Layouts</button>
        <button type="button" class="sm-btn" @click="openImageStudioPacksModal(); closeImageStudioMobileSheet()">Pacotes</button>
        <button type="button" class="sm-btn" @click="imageStudioOpenElementsModal(); closeImageStudioMobileSheet()">Elementos</button>
        <button type="button" class="sm-btn" @click="openImageStudioDimensionsModal(); closeImageStudioMobileSheet()">Formatos</button>
        <button type="button" class="sm-btn sm-btn--danger" @mousedown.prevent.stop="imageStudioDeleteSelection()">Excluir seleção</button>
    </div>
</div>
