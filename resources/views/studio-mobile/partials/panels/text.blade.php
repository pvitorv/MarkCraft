<div class="sm-panel" x-show="imageStudioSidebarTab === 'text'" x-cloak>
    <button type="button" class="sm-btn sm-btn--primary sm-btn--block" @click="setImageStudioSidebarTab('text'); imageStudioAddText()">+ Adicionar texto</button>
    <label class="sm-field">
        Conteúdo
        <textarea
            x-ref="imageStudioTextContentEl"
            x-model="imageStudioTextContent"
            @focus="ensureImageStudioTextObjectActive()"
            @input="imageStudioOnTextControlChange()"
            rows="3"
            class="sm-textarea"
            placeholder="Seu texto"
        ></textarea>
    </label>

    <label class="sm-field">
        Fonte
        <select
            class="sm-select"
            x-model="imageStudioTextFontSlug"
            @focus="ensureImageStudioTextObjectActive()"
            @change="imageStudioSelectFont(imageStudioTextFontSlug)"
        >
            @php
                $mobileFonts = collect($imageStudioCatalog['fonts'] ?? [])
                    ->groupBy(fn ($f) => $f['group_label'] ?? $f['group'] ?? 'Outras');
            @endphp
            @forelse($mobileFonts as $groupLabel => $groupFonts)
                <optgroup label="{{ $groupLabel }}">
                    @foreach($groupFonts as $font)
                        <option value="{{ $font['slug'] }}">{{ $font['label'] ?? $font['slug'] }}</option>
                    @endforeach
                </optgroup>
            @empty
                <option value="">Sem fontes no catálogo</option>
            @endforelse
        </select>
    </label>

    <div class="sm-panel__row">
        <button type="button" class="sm-btn" @click="imageStudioToggleTextBold()" :class="imageStudioTextBold ? 'sm-btn--active' : ''"><strong>B</strong></button>
        <button type="button" class="sm-btn italic" @click="imageStudioToggleTextItalic()" :class="imageStudioTextItalic ? 'sm-btn--active' : ''">I</button>
        <button type="button" class="sm-btn underline" @click="imageStudioToggleTextUnderline()" :class="imageStudioTextUnderline ? 'sm-btn--active' : ''">U</button>
        <button type="button" class="sm-btn line-through" @click="imageStudioToggleTextLinethrough()" :class="imageStudioTextLinethrough ? 'sm-btn--active' : ''">S</button>
        <button type="button" class="sm-btn" @click="imageStudioSetTextAlign('left')">⬅</button>
        <button type="button" class="sm-btn" @click="imageStudioSetTextAlign('center')">↔</button>
        <button type="button" class="sm-btn" @click="imageStudioSetTextAlign('right')">➡</button>
    </div>
    <div class="sm-panel__row sm-panel__row--2">
        <label class="sm-field">
            Cor
            <input type="color" x-model="imageStudioTextFill" @focus="ensureImageStudioTextObjectActive()" @input="imageStudioOnTextFillChange()" class="sm-color">
        </label>
        <label class="sm-field">
            Contorno
            <input type="color" x-model="imageStudioTextStroke" @focus="ensureImageStudioTextObjectActive()" @input="imageStudioOnTextStrokeChange()" class="sm-color">
        </label>
    </div>
    <label class="sm-field">
        Contorno <span x-text="(imageStudioTextStrokeWidth || 0) + 'px'"></span>
        <input type="range" min="0" max="24" step="1" x-model.number="imageStudioTextStrokeWidth" @focus="ensureImageStudioTextObjectActive()" @input="imageStudioOnTextControlChange()" class="sm-range">
    </label>
    <button type="button" class="sm-btn sm-btn--block" @click="imageStudioRemoveTextOutline()" x-show="(imageStudioTextStrokeWidth || 0) > 0">Sem contorno</button>
    <label class="sm-field">
        Tamanho <span x-text="imageStudioTextSize + 'px'"></span>
        <input type="range" min="12" max="320" step="1" x-model.number="imageStudioTextSize" @focus="ensureImageStudioTextObjectActive()" @input="imageStudioOnTextControlChange()" class="sm-range">
    </label>
</div>
