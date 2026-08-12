{{-- Elementos: modal MarkCraft (sidebar). Formatos / Layouts / Pacotes abaixo. --}}
@include('studio.partials.image_studio_elements_modal')

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
                    redes, panfletos e impressão
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
                                    <span class="studio-pack-preview-bar" :style="'background:' + (t.preview?.accent || '#0d9488')"></span>
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
                                    <span class="studio-pack-preview-bar" :style="'background:' + (item.preview?.accent || '#0d9488')"></span>
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

{{-- Desktop: galeria da pasta no modal (miniaturas + duplo clique) --}}
<div
    x-show="isMarkCraftDesktopApp() && desktopWorkspaceModalOpen"
    x-cloak
    class="studio-modal studio-modal--glass"
    role="dialog"
    aria-modal="true"
    aria-labelledby="studio-workspace-title"
    @keydown.escape.window="desktopWorkspaceModalOpen && closeDesktopWorkspaceModal()"
>
    <div class="studio-modal-backdrop studio-modal-backdrop--glass" @click="closeDesktopWorkspaceModal()"></div>
    <div class="studio-modal-panel studio-modal-panel--glass studio-modal-panel--gallery" @click.stop>
        <header class="studio-modal-header">
            <div>
                <h2 id="studio-workspace-title">Biblioteca no PC</h2>
                <p class="studio-modal-sub">Veja pastas e miniaturas aqui dentro. Duplo clique na arte/PSD/projeto manda para a prancheta.</p>
            </div>
            <button type="button" class="studio-btn" @click="closeDesktopWorkspaceModal()">Fechar</button>
        </header>

        <div class="studio-gallery-toolbar">
            <button type="button" class="studio-btn studio-btn-primary" @click="pickDesktopWorkspaceFolder()" :disabled="desktopWorkspaceBusy">Escolher pasta</button>
            <button type="button" class="studio-btn" @click="desktopWorkspaceGoUp()" :disabled="!desktopWorkspaceFolder || desktopWorkspaceCwd === desktopWorkspaceFolder || desktopWorkspaceBusy">Voltar</button>
            <div class="studio-workspace-crumbs" x-show="desktopWorkspaceFolder" x-cloak>
                <template x-for="crumb in desktopWorkspaceBreadcrumb()" :key="crumb.path">
                    <button type="button" class="studio-workspace-crumb" @click="browseDesktopWorkspace(crumb.path)" x-text="crumb.name"></button>
                </template>
            </div>
            <input type="search" x-model="desktopWorkspaceFilter" placeholder="Filtrar…" class="studio-modal-search studio-gallery-search">
        </div>

        <p class="studio-gallery-hint" x-show="desktopWorkspaceFolder" x-cloak x-text="desktopWorkspaceCwd"></p>
        <p class="studio-workspace-warn" x-show="isMarkCraftDesktopApp() && !hasDesktopWorkspaceApi()" x-cloak>
            Desktop desatualizado — instale o Setup 1.0.2.
        </p>

        <div class="studio-modal-body studio-gallery-grid">
            <p x-show="desktopWorkspaceBusy" class="studio-modal-empty">Carregando miniaturas…</p>
            <template x-if="!desktopWorkspaceBusy && !desktopWorkspaceFolder">
                <p class="studio-modal-empty">Clique em <strong>Escolher pasta</strong> e selecione a pasta das suas artes.</p>
            </template>
            <template x-if="!desktopWorkspaceBusy && desktopWorkspaceFolder && desktopWorkspaceGalleryItems().length === 0">
                <p class="studio-modal-empty">Pasta vazia.</p>
            </template>
            <template x-for="item in desktopWorkspaceGalleryItems()" :key="item.path">
                <button
                    type="button"
                    class="studio-gallery-card"
                    :class="{
                        'studio-gallery-card--folder': item.kind === 'folder' || item.type === 'dir',
                        'studio-gallery-card--psd': item.kind === 'psd',
                        'studio-gallery-card--project': item.kind === 'project',
                    }"
                    @click="(item.kind === 'folder' || item.type === 'dir') && browseDesktopWorkspace(item.path)"
                    @dblclick.prevent="onDesktopWorkspaceActivate(item)"
                    :title="item.path"
                >
                    <div class="studio-gallery-thumb">
                        <img x-show="item.thumb" x-cloak :src="item.thumb" alt="" loading="lazy">
                        <span
                            x-show="!item.thumb"
                            x-cloak
                            class="studio-gallery-badge"
                            x-text="(item.kind === 'folder' || item.type === 'dir') ? 'PASTA' : (item.kind === 'psd' ? 'PSD' : (item.kind === 'project' ? 'JSON' : ((item.ext || '').replace('.','').toUpperCase() || 'FILE')))"
                        ></span>
                    </div>
                    <span class="studio-gallery-name" x-text="item.name"></span>
                    <span class="studio-gallery-meta" x-text="(item.kind === 'folder' || item.type === 'dir') ? ((item.childCount || 0) + ' itens') : ((item.ext || '').replace('.','').toUpperCase() || item.kind || '')"></span>
                </button>
            </template>
        </div>
    </div>
</div>

{{-- Modal: atalhos --}}
<div
    x-show="imageStudioShortcutsModalOpen"
    x-cloak
    class="studio-modal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="studio-shortcuts-title"
    @keydown.escape.window="imageStudioShortcutsModalOpen && closeImageStudioShortcutsModal()"
>
    <div class="studio-modal-backdrop" @click="closeImageStudioShortcutsModal()"></div>
    <div class="studio-modal-panel" @click.stop>
        <header class="studio-modal-header">
            <div>
                <h2 id="studio-shortcuts-title">Atalhos</h2>
                <p class="studio-modal-sub">Teclado e seleção</p>
            </div>
            <button type="button" class="studio-btn" @click="closeImageStudioShortcutsModal()">Fechar</button>
        </header>
        <div class="studio-modal-body space-y-4 text-sm">
            <section>
                <h3 class="text-xs font-semibold uppercase tracking-wide text-zinc-500 mb-2">Edição</h3>
                <dl class="studio-shortcuts-grid">
                    <div><dt>Ctrl+Z</dt><dd>Desfazer</dd></div>
                    <div><dt>Ctrl+Y</dt><dd>Refazer</dd></div>
                    <div><dt>Ctrl+C / X / V</dt><dd>Copiar / recortar / colar</dd></div>
                    <div><dt>Ctrl+J</dt><dd>Duplicar</dd></div>
                    <div><dt>Delete</dt><dd>Excluir</dd></div>
                    <div><dt>Esc</dt><dd>Limpar seleção</dd></div>
                </dl>
            </section>
            <section>
                <h3 class="text-xs font-semibold uppercase tracking-wide text-zinc-500 mb-2">Camadas</h3>
                <dl class="studio-shortcuts-grid">
                    <div><dt>Ctrl+A</dt><dd>Selecionar tudo</dd></div>
                    <div><dt>Ctrl+D</dt><dd>Desselecionar</dd></div>
                    <div><dt>Ctrl+clique</dt><dd>Multi-selecionar</dd></div>
                    <div><dt>Ctrl+G</dt><dd>Agrupar</dd></div>
                    <div><dt>Ctrl+Shift+G</dt><dd>Desagrupar</dd></div>
                    <div><dt>Ctrl+[ / ]</dt><dd>Trás / frente</dd></div>
                    <div><dt>Ctrl+Shift+[ / ]</dt><dd>Fundo / topo</dd></div>
                </dl>
            </section>
            <section>
                <h3 class="text-xs font-semibold uppercase tracking-wide text-zinc-500 mb-2">Canvas</h3>
                <dl class="studio-shortcuts-grid">
                    <div><dt>Setas</dt><dd>Mover 1 px</dd></div>
                    <div><dt>Shift+setas</dt><dd>Mover 10 px</dd></div>
                    <div><dt>[ / ]</dt><dd>Rotacionar</dd></div>
                    <div><dt>Ctrl+0</dt><dd>Ajustar</dd></div>
                    <div><dt>Ctrl+1</dt><dd>Zoom 100%</dd></div>
                    <div><dt>Ctrl+ / −</dt><dd>Zoom</dd></div>
                    <div><dt>Duplo clique</dt><dd>Editar texto</dd></div>
                </dl>
            </section>
        </div>
    </div>
</div>
