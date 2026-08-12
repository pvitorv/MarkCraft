<div class="sm-panel" x-show="imageStudioSidebarTab === 'export'" x-cloak>
    <div class="sm-panel__grid">
        <button type="button" class="sm-btn sm-btn--primary" @click="imageStudioExport('png'); closeImageStudioMobileSheet()">Baixar PNG</button>
        <button type="button" class="sm-btn" @click="imageStudioExport('jpg'); closeImageStudioMobileSheet()">Baixar JPG</button>
    </div>
    <p class="sm-hint">PNG com transparência 100% no painel Fundo exporta sem fundo do canvas.</p>
</div>
