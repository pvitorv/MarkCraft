<div class="sm-panel" x-show="imageStudioSidebarTab === 'text'" x-cloak>
    <button type="button" class="sm-btn sm-btn--primary sm-btn--block" @click="setImageStudioSidebarTab('text'); imageStudioAddText()">+ Adicionar texto</button>
    <div class="sm-panel__row">
        <button type="button" class="sm-btn" @click="imageStudioToggleTextBold()" :class="imageStudioTextBold ? 'sm-btn--active' : ''"><strong>B</strong></button>
        <button type="button" class="sm-btn italic" @click="imageStudioToggleTextItalic()" :class="imageStudioTextItalic ? 'sm-btn--active' : ''">I</button>
        <button type="button" class="sm-btn" @click="imageStudioSetTextAlign('left')">⬅</button>
        <button type="button" class="sm-btn" @click="imageStudioSetTextAlign('center')">↔</button>
        <button type="button" class="sm-btn" @click="imageStudioSetTextAlign('right')">➡</button>
    </div>
    <label class="sm-field">
        Cor
        <input type="color" x-model="imageStudioTextFill" @input="imageStudioOnTextFillChange()" class="sm-color">
    </label>
    <label class="sm-field">
        Tamanho <span x-text="imageStudioTextSize + 'px'"></span>
        <input type="range" min="12" max="160" step="1" x-model.number="imageStudioTextSize" @input="imageStudioOnTextControlChange()" class="sm-range">
    </label>
</div>
