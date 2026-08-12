<div class="sm-panel sm-panel--layers" x-show="imageStudioSidebarTab === 'layers'" x-cloak>
    <div class="sm-group-bar">
        <button
            type="button"
            class="sm-btn"
            :class="imageStudioMobileMultiSelect ? 'sm-btn--active' : ''"
            @click="imageStudioToggleMobileMultiSelect()"
        >
            <span x-text="imageStudioMobileMultiSelect ? 'Selecionar várias · ON' : 'Selecionar várias'"></span>
        </button>
        <button
            type="button"
            class="sm-btn sm-btn--primary"
            @mousedown.prevent.stop="imageStudioGroupSelection()"
            :disabled="!imageStudioCanGroup && imageStudioGroupBagCount < 2"
        >Agrupar</button>
        <button
            type="button"
            class="sm-btn"
            @mousedown.prevent.stop="imageStudioUngroupSelection()"
            :disabled="!imageStudioCanUngroup"
        >Separar</button>
    </div>
    <p class="sm-hint" x-show="imageStudioMobileMultiSelect || imageStudioGroupBagCount >= 2" x-cloak>
        <span x-show="imageStudioMobileMultiSelect">Toque nas camadas para somar/tirar · </span>
        <span x-text="imageStudioGroupBagCount + ' na seleção'"></span>
    </p>

    <template x-if="!imageStudioLayers.length">
        <p class="sm-hint">Nenhuma camada ainda. Use <strong>Criar</strong> ou <strong>Texto</strong> para começar.</p>
    </template>

    <div class="sm-layers" x-ref="imageStudioLayersList" x-show="imageStudioLayers.length">
        <template x-for="layer in imageStudioLayers" :key="layer.id">
            <div
                class="sm-layer"
                :class="{
                    'sm-layer--active': layer.active || layer.id === imageStudioActiveLayerId,
                    'sm-layer--picked': imageStudioLayerInGroupBag(layer),
                }"
            >
                <button
                    type="button"
                    class="sm-layer__pick"
                    x-show="imageStudioMobileMultiSelect"
                    x-cloak
                    @click="imageStudioSelectLayer(layer, $event)"
                    :aria-pressed="imageStudioLayerInGroupBag(layer)"
                    x-text="imageStudioLayerInGroupBag(layer) ? '✓' : '+'"
                    title="Somar à seleção"
                ></button>
                <button
                    type="button"
                    class="sm-layer__name"
                    @click="imageStudioSelectLayer(layer, $event)"
                    x-text="layer.name"
                ></button>
                <button
                    type="button"
                    class="sm-layer__icon"
                    @click="imageStudioLayerAction(layer, 'visibility')"
                    :title="layer.visible ? 'Ocultar' : 'Mostrar'"
                    x-text="layer.visible ? '👁' : '🚫'"
                ></button>
                <button
                    type="button"
                    class="sm-layer__icon"
                    @click="imageStudioLayerAction(layer, 'up')"
                    title="Trazer para frente"
                >↑</button>
                <button
                    type="button"
                    class="sm-layer__icon"
                    @click="imageStudioLayerAction(layer, 'down')"
                    title="Enviar para trás"
                >↓</button>
                <button
                    type="button"
                    class="sm-layer__icon sm-layer__icon--danger"
                    @mousedown.prevent.stop="imageStudioDeleteLayer(layer)"
                    title="Excluir"
                >×</button>
            </div>
        </template>
    </div>

    <div class="sm-inspector" x-show="imageStudioSelectedObject" x-cloak>
        <div class="sm-inspector__head">
            <p class="sm-panel__title" style="margin:0">Editando</p>
            <span
                class="sm-inspector__badge"
                x-text="imageStudioActiveLayerName || imageStudioSelectedObject?.type || 'objeto'"
            ></span>
        </div>

        <div class="sm-panel__row">
            <button type="button" class="sm-btn" @mousedown.prevent.stop="imageStudioDuplicateSelection()">Duplicar</button>
            <button
                type="button"
                class="sm-btn sm-btn--primary"
                @mousedown.prevent.stop="imageStudioGroupSelection()"
                :disabled="!imageStudioCanGroup && imageStudioGroupBagCount < 2"
            >Agrupar</button>
            <button
                type="button"
                class="sm-btn"
                @mousedown.prevent.stop="imageStudioUngroupSelection()"
                :disabled="!imageStudioCanUngroup"
            >Separar</button>
            <button type="button" class="sm-btn" @mousedown.prevent.stop="imageStudioFlipSelection('x')">Espelhar H</button>
            <button type="button" class="sm-btn" @mousedown.prevent.stop="imageStudioFlipSelection('y')">Espelhar V</button>
            <button type="button" class="sm-btn sm-btn--danger" @mousedown.prevent.stop="imageStudioDeleteSelection()">Excluir</button>
        </div>

        <div class="sm-inspector__block">
            <p class="sm-inspector__label">Escala <span x-text="imageStudioObjectScale + '%'"></span></p>
            <div class="sm-panel__row">
                <button type="button" class="sm-btn" @click="imageStudioNudgeObjectScale(-10)">−10%</button>
                <button type="button" class="sm-btn" @click="imageStudioSetObjectScale(100)">100%</button>
                <button type="button" class="sm-btn" @click="imageStudioNudgeObjectScale(10)">+10%</button>
            </div>
            <input
                type="range"
                min="5"
                max="600"
                step="1"
                class="sm-range"
                :value="imageStudioObjectScale"
                @pointerdown="imageStudioBeginControlDrag($event)"
                @pointerup="imageStudioEndControlDrag()"
                @pointercancel="imageStudioEndControlDrag()"
                @change="imageStudioEndControlDrag()"
                @input="imageStudioSetObjectScale(Number($event.target.value))"
            >
        </div>

        <div class="sm-inspector__block">
            <p class="sm-inspector__label">Rotação <span x-text="imageStudioObjectAngle + '°'"></span></p>
            <div class="sm-panel__row">
                <button type="button" class="sm-btn" @click="imageStudioNudgeObjectAngle(-90)">↺ 90°</button>
                <button type="button" class="sm-btn" @click="imageStudioSetObjectAngle(0)">0°</button>
                <button type="button" class="sm-btn" @click="imageStudioNudgeObjectAngle(90)">↻ 90°</button>
            </div>
            <input
                type="range"
                min="0"
                max="359"
                step="1"
                class="sm-range"
                :value="imageStudioObjectAngle"
                @pointerdown="imageStudioBeginControlDrag($event)"
                @pointerup="imageStudioEndControlDrag()"
                @pointercancel="imageStudioEndControlDrag()"
                @change="imageStudioEndControlDrag()"
                @input="imageStudioSetObjectAngle(Number($event.target.value))"
            >
        </div>

        <div class="sm-inspector__block">
            <p class="sm-inspector__label">
                Opacidade
                <span x-text="Math.round((imageStudioSelectedObject?.opacity ?? 1) * 100) + '%'"></span>
            </p>
            <input
                type="range"
                min="0"
                max="100"
                class="sm-range"
                :value="Math.round((imageStudioSelectedObject?.opacity ?? 1) * 100)"
                @pointerdown="imageStudioBeginControlDrag($event)"
                @pointerup="imageStudioEndControlDrag()"
                @pointercancel="imageStudioEndControlDrag()"
                @change="imageStudioEndControlDrag()"
                @input="imageStudioObjectOpacity($event.target.value)"
            >
        </div>

        <div class="sm-inspector__block">
            <p class="sm-inspector__label">Alinhar no canvas</p>
            <div class="sm-panel__row">
                <button type="button" class="sm-btn" @click="imageStudioAlignObject('left')">⬅</button>
                <button type="button" class="sm-btn" @click="imageStudioAlignObject('center-h')">↔</button>
                <button type="button" class="sm-btn" @click="imageStudioAlignObject('right')">➡</button>
                <button type="button" class="sm-btn" @click="imageStudioAlignObject('top')">⬆</button>
                <button type="button" class="sm-btn" @click="imageStudioAlignObject('center-v')">↕</button>
                <button type="button" class="sm-btn" @click="imageStudioAlignObject('bottom')">⬇</button>
            </div>
        </div>

        <template x-if="imageStudioSelectedObject?.type === 'text'">
            <div class="sm-inspector__block">
                <button
                    type="button"
                    class="sm-btn sm-btn--primary sm-btn--block"
                    @click="openImageStudioMobileSheet('text')"
                >Editar texto (fonte, cor, tamanho)</button>
            </div>
        </template>

        <template x-if="imageStudioSelectedObject?.type === 'image'">
            <div class="sm-inspector__block sm-panel__section--rembg">
                <button
                    type="button"
                    class="sm-btn sm-btn--rembg sm-btn--block"
                    @pointerdown.prevent.stop="imageStudioRemoveBgFromSelection()"
                    :disabled="imageStudioBgRemoving"
                >
                    <span x-show="!imageStudioBgRemoving">Remover fundo desta imagem</span>
                    <span x-show="imageStudioBgRemoving" x-cloak>Processando…</span>
                </button>
                <template x-if="!imageStudioCropping">
                    <button type="button" class="sm-btn sm-btn--block" @click="imageStudioStartCrop()">Recortar</button>
                </template>
                <template x-if="imageStudioCropping">
                    <div class="sm-panel__row">
                        <button type="button" class="sm-btn sm-btn--primary" @click="imageStudioApplyCrop()">Aplicar corte</button>
                        <button type="button" class="sm-btn" @click="imageStudioCancelCrop()">Cancelar</button>
                    </div>
                </template>
            </div>
        </template>

        <template x-if="imageStudioSelectedObject && imageStudioSelectedObject.type !== 'text' && imageStudioSelectedObject.type !== 'image'">
            <div class="sm-inspector__block">
                <p class="sm-inspector__label">Cor da forma</p>
                <label class="sm-field">
                    Preenchimento
                    <input type="color" x-model="imageStudioShapeFill" @input="imageStudioOnShapeFillChange()" class="sm-color">
                </label>
                <label class="sm-field">
                    Contorno
                    <input type="color" x-model="imageStudioShapeStroke" @input="imageStudioOnShapeStrokeChange()" class="sm-color">
                </label>
            </div>
        </template>
    </div>

    <p class="sm-hint" x-show="imageStudioLayers.length && !imageStudioSelectedObject" x-cloak>
        Toque numa camada acima para editar escala, rotação e alinhamento.
    </p>
</div>
