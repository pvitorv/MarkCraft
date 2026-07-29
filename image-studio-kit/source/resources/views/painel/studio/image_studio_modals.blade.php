{{-- Modais do Image Studio (Elementos + Formatos). O kit não trouxe este markup. --}}

{{-- Modal: Elementos (formas, ícones, emojis, adesivos) --}}
<div
    x-show="imageStudioElementsModalOpen"
    x-cloak
    class="studio-modal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="studio-elements-title"
    @keydown.escape.window="imageStudioCloseElementsModal()"
>
    <div class="studio-modal-backdrop" @click="imageStudioCloseElementsModal()"></div>
    <div class="studio-modal-panel studio-modal-panel--wide" @click.stop>
        <header class="studio-modal-header">
            <div>
                <h2 id="studio-elements-title">Elementos</h2>
                <p class="studio-modal-sub">
                    Formas, ícones, emojis e adesivos —
                    <span x-text="imageStudioElementsFilteredCount()"></span> itens
                </p>
            </div>
            <button type="button" class="studio-btn" @click="imageStudioCloseElementsModal()">Fechar</button>
        </header>

        <div class="studio-modal-toolbar">
            <input
                type="search"
                x-model="imageStudioElementFilter"
                placeholder="Buscar elemento…"
                class="studio-modal-search"
                autofocus
            >
            <div class="studio-modal-chips">
                <template x-for="g in imageStudioElementQuickGroups()" :key="'eg-' + g.id">
                    <button
                        type="button"
                        class="studio-chip"
                        :class="imageStudioElementFilterGroup === g.id ? 'is-active' : ''"
                        @click="imageStudioElementFilterGroup = g.id"
                        x-text="g.label"
                    ></button>
                </template>
            </div>
        </div>

        <div class="studio-modal-body">
            <template x-if="imageStudioElementGroupList().length === 0">
                <p class="studio-modal-empty">Nada encontrado com esse filtro.</p>
            </template>
            <template x-for="group in imageStudioElementGroupList()" :key="'egroup-' + group.name">
                <section class="studio-modal-section">
                    <h3 x-text="group.name"></h3>
                    <div class="studio-element-grid">
                        <template x-for="el in group.items" :key="el.slug">
                            <button
                                type="button"
                                class="studio-element-tile"
                                @click="imageStudioAddElementFromModal(el)"
                                :title="el.name"
                            >
                                <span
                                    class="studio-element-preview"
                                    :class="el.type === 'icon_glyph' ? imageStudioIconGlyphClass(el.font) : ''"
                                    x-text="el.icon || el.char || '★'"
                                ></span>
                                <span class="studio-element-name" x-text="el.name"></span>
                            </button>
                        </template>
                    </div>
                </section>
            </template>
        </div>
    </div>
</div>

{{-- Modal: todos os formatos (redes, panfletos, banners, impressão) --}}
<div
    x-show="imageStudioDimensionsModalOpen"
    x-cloak
    class="studio-modal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="studio-formats-title"
    @keydown.escape.window="closeImageStudioDimensionsModal()"
>
    <div class="studio-modal-backdrop" @click="closeImageStudioDimensionsModal()"></div>
    <div class="studio-modal-panel studio-modal-panel--wide" @click.stop>
        <header class="studio-modal-header">
            <div>
                <h2 id="studio-formats-title">Todos os formatos</h2>
                <p class="studio-modal-sub">
                    Clique em qualquer formato para aplicar o tamanho no canvas —
                    Blog (capa, story, quadrado), redes, panfletos e impressão
                    (<span x-text="Object.values(imageStudioReferencePresetGroups()).reduce((n, g) => n + g.length, 0)"></span> opções)
                </p>
            </div>
            <button type="button" class="studio-btn" @click="closeImageStudioDimensionsModal()">Fechar</button>
        </header>

        <div class="studio-modal-toolbar">
            <input
                type="search"
                x-model="imageStudioDimensionsFilter"
                placeholder="Buscar por nome, rede ou tamanho (ex.: Instagram, A4, 1080)…"
                class="studio-modal-search"
            >
        </div>

        <div class="studio-modal-body">
            <template x-if="Object.keys(imageStudioReferencePresetGroups()).length === 0">
                <p class="studio-modal-empty">Nenhum formato encontrado.</p>
            </template>
            <template x-for="(presets, groupName) in imageStudioReferencePresetGroups()" :key="'fg-' + groupName">
                <section class="studio-modal-section">
                    <h3>
                        <span x-text="groupName"></span>
                        <small x-text="presets.length + ' formatos'"></small>
                    </h3>
                    <div class="studio-format-grid">
                        <template x-for="p in presets" :key="p.slug">
                            <button
                                type="button"
                                class="studio-format-tile"
                                :class="imageStudioPreset === p.slug ? 'is-active' : ''"
                                @click="pickImageStudioReferenceDimensions(p)"
                            >
                                <span class="studio-format-icon" x-text="p.icon || '▭'"></span>
                                <span class="studio-format-meta">
                                    <strong x-text="p.name"></strong>
                                    <small x-text="p.width + '×' + p.height + (p.aspect ? ' · ' + p.aspect : '')"></small>
                                </span>
                            </button>
                        </template>
                    </div>
                </section>
            </template>
        </div>
    </div>
</div>

{{-- Modal: layouts / templates prontos --}}
<div
    x-show="imageStudioTemplatesModalOpen"
    x-cloak
    class="studio-modal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="studio-templates-title"
    @keydown.escape.window="closeImageStudioTemplatesModal()"
>
    <div class="studio-modal-backdrop" @click="closeImageStudioTemplatesModal()"></div>
    <div class="studio-modal-panel studio-modal-panel--wide" @click.stop>
        <header class="studio-modal-header">
            <div>
                <h2 id="studio-templates-title">Layouts</h2>
                <p class="studio-modal-sub">
                    Templates prontos para redes e formatos
                    (<span x-text="(imageStudioTemplates || []).length"></span>)
                </p>
            </div>
            <button type="button" class="studio-btn" @click="closeImageStudioTemplatesModal()">Fechar</button>
        </header>

        <div class="studio-modal-toolbar">
            <input
                type="search"
                x-model="imageStudioTemplatesFilter"
                placeholder="Buscar (Instagram, YouTube, story, capa…)…"
                class="studio-modal-search"
            >
        </div>

        <div class="studio-modal-body">
            <template x-if="Object.keys(imageStudioTemplateGroups()).length === 0">
                <p class="studio-modal-empty">Nenhum layout. Dê Ctrl+F5 se acabou de atualizar.</p>
            </template>
            <template x-for="(items, groupName) in imageStudioTemplateGroups()" :key="'tg-' + groupName">
                <section class="studio-modal-section">
                    <h3>
                        <span x-text="groupName"></span>
                        <small x-text="items.length + ' layouts'"></small>
                    </h3>
                    <div class="studio-pack-grid">
                        <template x-for="t in items" :key="t.slug">
                            <button type="button" class="studio-pack-tile" @click="imageStudioApplyTemplate(t)">
                                <div class="studio-pack-preview" :style="'background:' + (t.preview?.bg || t.background?.color || '#171512')">
                                    <span class="studio-pack-preview-bar" :style="'background:' + (t.preview?.accent || '#c4a574')"></span>
                                    <span class="studio-pack-preview-line" :style="'background:' + (t.preview?.ink || '#f3efe7')"></span>
                                    <span class="studio-pack-preview-line studio-pack-preview-line--short" :style="'background:' + (t.preview?.ink || '#f3efe7')"></span>
                                </div>
                                <div class="studio-pack-meta">
                                    <strong x-text="t.name"></strong>
                                    <small x-text="t.description || ''"></small>
                                </div>
                            </button>
                        </template>
                    </div>
                </section>
            </template>
        </div>
    </div>
</div>

{{-- Modal: pacotes (editorial, marca, mockups) — tiles com preview visual --}}
<div
    x-show="imageStudioPacksModalOpen"
    x-cloak
    class="studio-modal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="studio-packs-title"
    @keydown.escape.window="closeImageStudioPacksModal()"
>
    <div class="studio-modal-backdrop" @click="closeImageStudioPacksModal()"></div>
    <div class="studio-modal-panel studio-modal-panel--wide" @click.stop>
        <header class="studio-modal-header">
            <div>
                <h2 id="studio-packs-title">Pacotes</h2>
                <p class="studio-modal-sub">
                    Editorial, identidade e mockups — clique para aplicar no canvas e editar
                    (<span x-text="imageStudioPacksItemCount()"></span>)
                </p>
            </div>
            <button type="button" class="studio-btn" @click="closeImageStudioPacksModal()">Fechar</button>
        </header>

        <div class="studio-modal-toolbar studio-modal-toolbar--packs">
            <input
                type="search"
                x-model="imageStudioPacksFilter"
                placeholder="Buscar (capa, logo, phone, newsletter)…"
                class="studio-modal-search"
            >
            <div class="studio-modal-chips">
                <button type="button" class="studio-chip" :class="!imageStudioPacksCategory ? 'is-active' : ''" @click="imageStudioPacksCategory = ''">Todos</button>
                <template x-for="cat in (imageStudioPackCategories || [])" :key="'pc-' + cat.id">
                    <button type="button" class="studio-chip" :class="imageStudioPacksCategory === cat.id ? 'is-active' : ''" @click="imageStudioPacksCategory = cat.id" x-text="cat.name"></button>
                </template>
            </div>
        </div>

        <div class="studio-modal-body">
            <template x-if="imageStudioFilteredPacks().length === 0">
                <p class="studio-modal-empty">Nenhum pacote encontrado. Dê Ctrl+F5 se acabou de atualizar.</p>
            </template>
            <template x-for="pack in imageStudioFilteredPacks()" :key="'pack-' + pack.id">
                <section class="studio-modal-section">
                    <h3>
                        <span x-text="pack.name"></span>
                        <small x-text="(pack.items?.length || 0) + ' layouts'"></small>
                    </h3>
                    <p class="studio-modal-sub" style="margin:0 0 .85rem;" x-show="pack.description" x-text="pack.description"></p>
                    <div class="studio-pack-grid">
                        <template x-for="item in pack.items" :key="pack.id + '-' + item.slug">
                            <button type="button" class="studio-pack-tile" @click="imageStudioApplyPackItem(item)">
                                <div
                                    class="studio-pack-preview"
                                    :style="'background:' + (item.preview?.bg || '#171512')"
                                >
                                    <span class="studio-pack-preview-bar" :style="'background:' + (item.preview?.accent || '#c4a574')"></span>
                                    <span class="studio-pack-preview-line" :style="'background:' + (item.preview?.ink || '#f3efe7')"></span>
                                    <span class="studio-pack-preview-line studio-pack-preview-line--short" :style="'background:' + (item.preview?.ink || '#f3efe7')"></span>
                                </div>
                                <div class="studio-pack-meta">
                                    <strong x-text="item.name"></strong>
                                    <small x-text="item.description || ''"></small>
                                </div>
                            </button>
                        </template>
                    </div>
                </section>
            </template>
        </div>
    </div>
</div>
