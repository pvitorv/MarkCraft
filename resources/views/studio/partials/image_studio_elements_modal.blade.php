{{-- Modal Elementos — split com scroll real (sidebar + grid) --}}
<div
    x-show="imageStudioElementsModalOpen"
    x-cloak
    class="studio-modal studio-modal--elements"
    role="dialog"
    aria-modal="true"
    aria-labelledby="studio-elements-title"
    @keydown.escape.window="imageStudioCloseElementsModal()"
>
    <div class="studio-modal-backdrop" @click="imageStudioCloseElementsModal()"></div>

    <div class="studio-elements-panel" @click.stop>
        <aside class="studio-elements-aside">
            <div class="studio-elements-aside-head">
                <h2 id="studio-elements-title">Elementos</h2>
                <p>Biblioteca do MarkCraft</p>
            </div>
            <nav class="studio-elements-nav" aria-label="Categorias de elementos">
                <template x-for="section in imageStudioElementNavSections()" :key="'nav-sec-' + section.title">
                    <div class="studio-elements-nav-section">
                        <p class="studio-elements-nav-title" x-text="section.title"></p>
                        <ul>
                            <template x-for="item in section.items" :key="'nav-' + item.id">
                                <li>
                                    <button
                                        type="button"
                                        class="studio-elements-nav-btn"
                                        :class="(imageStudioElementFilterGroup || '') === item.id ? 'is-active' : ''"
                                        @click="imageStudioElementFilterGroup = item.id"
                                    >
                                        <span class="studio-elements-nav-label" x-text="item.label"></span>
                                        <span class="studio-elements-nav-count" x-text="item.count"></span>
                                    </button>
                                </li>
                            </template>
                        </ul>
                    </div>
                </template>
            </nav>
        </aside>

        <section class="studio-elements-main">
            <header class="studio-elements-main-head">
                <div class="studio-elements-main-titles">
                    <h3 x-text="imageStudioElementActiveNavLabel()"></h3>
                    <p>
                        <span x-text="imageStudioElementsFilteredCount()"></span>
                        itens · clique para adicionar ao canvas
                    </p>
                </div>
                <div class="studio-elements-main-actions">
                    <input
                        type="search"
                        x-model="imageStudioElementFilter"
                        x-ref="imageStudioElementsSearch"
                        placeholder="Buscar nesta categoria…"
                        class="studio-modal-search"
                    >
                    <button type="button" class="studio-btn" @click="imageStudioCloseElementsModal()">Fechar</button>
                </div>
            </header>

            <div class="studio-elements-main-body">
                <template x-if="!filterImageStudioElements().length">
                    <p class="studio-modal-empty">
                        Nada nesta vista. Limpe a busca ou escolha outra categoria à esquerda.
                    </p>
                </template>

                <div class="studio-element-grid" x-show="filterImageStudioElements().length">
                    <template x-for="el in filterImageStudioElements()" :key="'el-' + (el.slug || el.name)">
                        <button
                            type="button"
                            class="studio-element-tile"
                            @click="imageStudioAddElementFromModal(el)"
                            :title="el.name || el.slug"
                        >
                            <span class="studio-element-preview-wrap">
                                <template x-if="imageStudioElementIsSvg(el)">
                                    <img
                                        :src="el.icon_url"
                                        :alt="el.name || ''"
                                        class="studio-element-preview-img"
                                        loading="lazy"
                                    >
                                </template>
                                <template x-if="!imageStudioElementIsSvg(el)">
                                    <span
                                        class="studio-element-preview"
                                        :class="el.type === 'icon_glyph' ? imageStudioIconGlyphClass(el.font) : ''"
                                        x-text="imageStudioElementPreview(el)"
                                    ></span>
                                </template>
                            </span>
                            <span class="studio-element-name" x-text="el.name || el.slug"></span>
                        </button>
                    </template>
                </div>
            </div>
        </section>
    </div>
</div>
