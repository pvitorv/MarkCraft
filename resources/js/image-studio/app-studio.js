import Alpine from 'alpinejs';
import sort from '@alpinejs/sort';
import axios from 'axios';
import { imageStudioMethods, normalizeTemplatesList } from './imageStudio';
import { preloadIconFontCdns, preloadStarterGoogleFonts } from './imageStudioTextFonts';
import { DEFAULT_SHAPE_FILL, DEFAULT_SHAPE_STROKE, resolveInsertFill } from './imageStudioShapes';
import markCraftHub from '../markCraftHub';
import '../bootstrap';

Alpine.plugin(sort);

const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
const catalogUrl = document.querySelector('meta[name="studio-catalog-url"]')?.getAttribute('content')
    || '/api/image-studio/catalog';

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
if (csrf) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = csrf;
}

window.axios = axios;

function downloadBlob(blob, filename) {
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    a.remove();
    setTimeout(() => URL.revokeObjectURL(url), 2000);
}

function markCraftStudioMethods() {
    const base = imageStudioMethods();

    return {
        ...base,

        projectId: null,
        message: null,
        error: null,
        studioDraftKey: 'markcraft-studio-draft-v3',
        slides: [],
        selectedSlide: null,
        imageStudioUnderlayEnabled: false,
        imageStudioPreset: 'ig_feed_square',
        imageStudioCustomWidth: 1080,
        imageStudioCustomHeight: 1080,
        imageStudioShapeFill: DEFAULT_SHAPE_FILL,
        imageStudioShapeStroke: DEFAULT_SHAPE_STROKE,

        desktopWorkspaceModalOpen: false,
        desktopWorkspaceFolder: null,
        desktopWorkspaceCwd: null,
        desktopWorkspaceItems: [],
        desktopWorkspaceBusy: false,
        desktopWorkspaceFilter: '',

        isMarkCraftDesktopApp() {
            return !!(window.markcraftDesktop?.isDesktop);
        },

        hasDesktopWorkspaceApi() {
            const d = window.markcraftDesktop;
            return !!(
                d?.isDesktop
                && typeof d.workspacePick === 'function'
                && typeof d.workspaceBrowse === 'function'
                && typeof d.workspaceRead === 'function'
            );
        },

        desktopWorkspaceApiMissingMessage() {
            return 'Reinicie o Desktop (cd desktop && npm start) ou Setup ≥1.0.3. O app antigo trava ao abrir PSD pela biblioteca.';
        },

        /**
         * workspaceRead → ArrayBuffer. Prefere bytes crus (API v4).
         * Fallback base64 via fetch (sem atob+loop que congela PSD grande).
         */
        async desktopReadToArrayBuffer(read) {
            if (!read) {
                throw new Error('Leitura vazia');
            }
            const raw = read.bytes;
            if (raw != null) {
                if (raw instanceof ArrayBuffer) {
                    return raw;
                }
                if (ArrayBuffer.isView(raw)) {
                    return raw.buffer.slice(raw.byteOffset, raw.byteOffset + raw.byteLength);
                }
                if (raw.type === 'Buffer' && Array.isArray(raw.data)) {
                    return Uint8Array.from(raw.data).buffer;
                }
                if (Array.isArray(raw)) {
                    return Uint8Array.from(raw).buffer;
                }
            }
            if (read.base64) {
                const res = await fetch(`data:application/octet-stream;base64,${read.base64}`);

                return res.arrayBuffer();
            }
            throw new Error('Arquivo sem bytes — atualize o Desktop (API v4)');
        },

        async browseDesktopWorkspace(dir) {
            if (!this.hasDesktopWorkspaceApi()) {
                this.error = this.desktopWorkspaceApiMissingMessage();
                return;
            }
            this.desktopWorkspaceBusy = true;
            try {
                const res = await window.markcraftDesktop.workspaceBrowse({
                    dir: dir || this.desktopWorkspaceCwd || undefined,
                });
                this.desktopWorkspaceFolder = res?.folder || null;
                this.desktopWorkspaceCwd = res?.cwd || res?.folder || null;
                this.desktopWorkspaceItems = Array.isArray(res?.items) ? res.items : [];
                this.error = null;
            } catch (e) {
                this.error = e?.message || 'Não foi possível ler a pasta';
            } finally {
                this.desktopWorkspaceBusy = false;
            }
        },

        async openDesktopWorkspaceModal() {
            if (!this.isMarkCraftDesktopApp()) {
                this.error = 'Só no MarkCraft Desktop.';
                return;
            }
            this.desktopWorkspaceModalOpen = true;
            if (!this.hasDesktopWorkspaceApi()) {
                this.error = this.desktopWorkspaceApiMissingMessage();
                return;
            }
            await this.browseDesktopWorkspace();
            if (!this.desktopWorkspaceFolder) {
                await this.pickDesktopWorkspaceFolder();
            }
        },

        closeDesktopWorkspaceModal() {
            this.desktopWorkspaceModalOpen = false;
        },

        async pickDesktopWorkspaceFolder() {
            if (!this.hasDesktopWorkspaceApi()) {
                this.error = this.desktopWorkspaceApiMissingMessage();
                return;
            }
            this.desktopWorkspaceBusy = true;
            try {
                const picked = await window.markcraftDesktop.workspacePick();
                if (picked?.canceled) {
                    return;
                }
                this.desktopWorkspaceFolder = picked?.folder || null;
                this.desktopWorkspaceCwd = this.desktopWorkspaceFolder;
                await this.browseDesktopWorkspace(this.desktopWorkspaceFolder);
                this.message = this.desktopWorkspaceFolder
                    ? `Biblioteca: ${this.desktopWorkspaceFolder}`
                    : null;
            } catch (e) {
                this.error = e?.message || 'Não foi possível escolher a pasta';
            } finally {
                this.desktopWorkspaceBusy = false;
            }
        },

        desktopWorkspaceBreadcrumb() {
            const root = this.desktopWorkspaceFolder;
            const cwd = this.desktopWorkspaceCwd || root;
            if (!root || !cwd) {
                return [];
            }
            const sep = root.includes('/') && !root.includes('\\') ? '/' : '\\';
            const rel = String(cwd).slice(String(root).length).replace(/^[\\/]+/, '');
            const parts = rel ? rel.split(/[\\/]+/).filter(Boolean) : [];
            const crumbs = [{ name: 'Raiz', path: root }];
            let cur = root;
            parts.forEach((part) => {
                cur = (cur.endsWith('/') || cur.endsWith('\\')) ? (cur + part) : (cur + sep + part);
                crumbs.push({ name: part, path: cur });
            });

            return crumbs;
        },

        desktopWorkspaceGoUp() {
            const crumbs = this.desktopWorkspaceBreadcrumb();
            if (crumbs.length < 2) {
                return;
            }
            this.browseDesktopWorkspace(crumbs[crumbs.length - 2].path);
        },

        desktopWorkspaceGalleryItems() {
            const q = String(this.desktopWorkspaceFilter || '').trim().toLowerCase();
            const list = this.desktopWorkspaceItems || [];
            if (!q) {
                return list;
            }

            return list.filter((i) => String(i.name || '').toLowerCase().includes(q));
        },

        async onDesktopWorkspaceActivate(item) {
            if (!item || this._desktopOpeningFile || this.desktopWorkspaceBusy) {
                return;
            }
            if (item.type === 'dir' || item.kind === 'folder') {
                await this.browseDesktopWorkspace(item.path);

                return;
            }
            await this.openDesktopWorkspaceFileIntoStudio(item);
        },

        async ensureDesktopStudioReady() {
            if (!this.imageStudioEngine?.canvas) {
                await this.initImageStudio?.();
            }
        },

        async openDesktopWorkspaceFileIntoStudio(file) {
            if (!this.hasDesktopWorkspaceApi() || !file?.path) {
                this.error = this.desktopWorkspaceApiMissingMessage();
                return;
            }
            if (this._desktopOpeningFile) {
                return;
            }
            this._desktopOpeningFile = true;
            this.desktopWorkspaceBusy = true;
            try {
                await this.ensureDesktopStudioReady();
                this.message = `Abrindo ${file.name || 'arquivo'}…`;
                this.error = '';
                const read = await window.markcraftDesktop.workspaceRead(file.path);
                const ext = String(read.ext || file.ext || '').toLowerCase();

                // Fecha o modal antes do parse pesado para o UI não parecer travado
                this.closeDesktopWorkspaceModal();
                await this.$nextTick?.();
                await new Promise((r) => setTimeout(r, 40));

                if (ext === '.psd') {
                    this.message = `Lendo PSD: ${read.name}…`;
                    const buffer = await this.desktopReadToArrayBuffer(read);
                    const result = await this.imageStudioEngine.importPsdFromArrayBuffer(buffer, {
                        replaceWorkspace: true,
                        backgroundColor: this.imageStudioBgColor || '#ffffff',
                    });
                    this.imageStudioCustomWidth = result.width;
                    this.imageStudioCustomHeight = result.height;
                    this.imageStudioPreset = 'custom';
                    this.refreshImageStudioLayers?.();
                    this.$nextTick?.(() => this.fitImageStudioCanvas?.());
                    this.scheduleImageStudioSave?.();
                    this.message = `PSD na prancheta: ${read.name} · ${result.layers} camada(s)`;
                    return;
                }

                if (['.png', '.jpg', '.jpeg', '.webp', '.gif', '.svg'].includes(ext)) {
                    let dataUrl = read.dataUrl;
                    if (!dataUrl) {
                        const buffer = await this.desktopReadToArrayBuffer(read);
                        const blob = new Blob([buffer], { type: read.mime || 'application/octet-stream' });
                        dataUrl = URL.createObjectURL(blob);
                    }
                    await this.imageStudioEngine.addImageFromUrl(dataUrl, read.name);
                    this.refreshImageStudioLayers?.();
                    this.message = `Arte na prancheta: ${read.name}`;
                    return;
                }

                if (ext === '.json') {
                    const buffer = await this.desktopReadToArrayBuffer(read);
                    const text = new TextDecoder('utf-8').decode(new Uint8Array(buffer));
                    const json = JSON.parse(text);
                    const canvasJson = json.canvas || json;
                    if (json.width && json.height) {
                        this.imageStudioCustomWidth = json.width;
                        this.imageStudioCustomHeight = json.height;
                    }
                    if (Array.isArray(json.deck_pages) && json.deck_pages.length) {
                        this.imageStudioDeckPages = json.deck_pages;
                        this.imageStudioDeckPageIndex = Math.max(
                            0,
                            Math.min(json.deck_page_index ?? 0, json.deck_pages.length - 1),
                        );
                    }
                    await this.imageStudioEngine.loadFromJSON(canvasJson);
                    this.refreshImageStudioLayers?.();
                    this.$nextTick?.(() => this.fitImageStudioCanvas?.());
                    this.message = `Projeto na prancheta: ${read.name}`;
                    return;
                }

                this.message = `${read.name} — tipo não suportado na prancheta.`;
            } catch (e) {
                this.error = e?.message || 'Falha ao abrir no Studio';
                this.message = '';
            } finally {
                this._desktopOpeningFile = false;
                this.desktopWorkspaceBusy = false;
            }
        },

        selectedThumbnailPlatform: null,
        thumbnailSettingsByPlatform: {},
        thumbnailPreviewUrl: null,
        thumbnailPreviewUrls: {},
        switchTab() {},
        applyThumbnailSettingsPatch() {},
        upsertProjectLibraryAsset() {},
        async loadProjectLibraryAssets() {},

        // —— Elementos: navegação organizada (MarkCraft) ——
        imageStudioElementMatchesNav(el, navId) {
            const group = String(el.group || '');
            const type = String(el.type || '');
            if (navId === 'all' || navId === '') return true;
            if (navId === 'formas') return group.startsWith('formas') || group === 'linhas';
            if (navId === 'icones') {
                return type === 'icon_glyph' || type === 'svg_icon' || group === 'icones' || group.startsWith('bs_');
            }
            if (navId === 'icones_fa') return type === 'icon_glyph' && String(el.font || '').startsWith('fa_');
            if (navId === 'icones_material') return type === 'icon_glyph' && el.font === 'material_symbols';
            if (navId === 'icones_bootstrap') return type === 'svg_icon' || group.startsWith('bs_');
            if (navId.startsWith('formas_') || navId === 'linhas' || navId === 'emojis' || navId === 'blobs' || navId === 'adesivos') {
                return group === navId;
            }
            if (navId.startsWith('bs_')) return group === navId;
            return group === navId;
        },

        filterImageStudioElements() {
            const list = this.normalizeImageStudioElementList(this.imageStudioElements);
            const q = (this.imageStudioElementFilter || '').trim().toLowerCase();
            const groupFilter = (this.imageStudioElementFilterGroup || '').trim();
            return list.filter((el) => {
                if (!el || typeof el !== 'object') return false;
                if (groupFilter && !this.imageStudioElementMatchesNav(el, groupFilter)) return false;
                if (q) {
                    const hay = `${el.name || ''} ${el.group || ''} ${el.char || ''} ${el.icon || ''} ${el.icon_group || ''} ${el.slug || ''}`.toLowerCase();
                    if (!hay.includes(q)) return false;
                }
                return true;
            });
        },

        imageStudioElementNavSections() {
            const all = this.normalizeImageStudioElementList(this.imageStudioElements);
            const countFor = (navId) => all.filter((el) => this.imageStudioElementMatchesNav(el, navId)).length;
            return [
                {
                    title: 'Biblioteca',
                    items: [{ id: '', label: 'Todos os elementos', count: all.length }],
                },
                {
                    title: 'Formas',
                    items: [
                        { id: 'formas', label: 'Todas as formas', count: countFor('formas') },
                        { id: 'formas_basicas', label: 'Retângulos & quadrados', count: countFor('formas_basicas') },
                        { id: 'formas_vazadas', label: 'Vazadas (contorno)', count: countFor('formas_vazadas') },
                        { id: 'formas_irregulares', label: 'Irregulares', count: countFor('formas_irregulares') },
                        { id: 'formas_ovais', label: 'Círculos & ovais', count: countFor('formas_ovais') },
                        { id: 'formas_poligonos', label: 'Polígonos & estrelas', count: countFor('formas_poligonos') },
                        { id: 'formas_3d', label: 'Formas 3D', count: countFor('formas_3d') },
                        { id: 'formas_molduras', label: 'Molduras & bordas', count: countFor('formas_molduras') },
                        { id: 'linhas', label: 'Linhas & setas', count: countFor('linhas') },
                        { id: 'formas_extras', label: 'Decorativas', count: countFor('formas_extras') },
                    ].filter((i) => i.count > 0 || i.id === 'formas'),
                },
                {
                    title: 'Expressão',
                    items: [
                        { id: 'emojis', label: 'Emojis', count: countFor('emojis') },
                        { id: 'blobs', label: 'Blobs & slimes', count: countFor('blobs') },
                        { id: 'adesivos', label: 'Adesivos', count: countFor('adesivos') },
                    ].filter((i) => i.count > 0),
                },
                {
                    title: 'Ícones',
                    items: [
                        { id: 'icones', label: 'Todos os ícones', count: countFor('icones') },
                        { id: 'icones_fa', label: 'Font Awesome', count: countFor('icones_fa') },
                        { id: 'icones_material', label: 'Material Symbols', count: countFor('icones_material') },
                        { id: 'icones_bootstrap', label: 'Bootstrap Icons', count: countFor('icones_bootstrap') },
                        { id: 'bs_social', label: 'Social & marcas', count: countFor('bs_social') },
                        { id: 'bs_media', label: 'Mídia & áudio', count: countFor('bs_media') },
                        { id: 'bs_ui', label: 'Interface', count: countFor('bs_ui') },
                        { id: 'bs_commerce', label: 'Comércio', count: countFor('bs_commerce') },
                        { id: 'bs_creative', label: 'Criativo', count: countFor('bs_creative') },
                    ].filter((i) => i.count > 0 || i.id === 'icones'),
                },
            ].filter((section) => section.items.length > 0);
        },

        imageStudioElementActiveNavLabel() {
            for (const section of this.imageStudioElementNavSections()) {
                const hit = section.items.find((i) => i.id === (this.imageStudioElementFilterGroup || ''));
                if (hit) return hit.label;
            }
            return 'Todos os elementos';
        },

        imageStudioElementIsSvg(el) {
            return el?.type === 'svg_icon' && !!el?.icon_url;
        },

        imageStudioElementPreview(el) {
            if (!el) return '◆';
            if (el.type === 'icon_glyph' || el.type === 'emoji' || el.type === 'sticker') {
                return el.char || el.icon || '★';
            }
            return el.icon || el.char || '◆';
        },

        imageStudioOpenElementsModal() {
            this.imageStudioElementFilter = '';
            if (!this._imageStudioElementsModalShown) {
                this.imageStudioElementFilterGroup = 'formas';
                this._imageStudioElementsModalShown = true;
            }
            this.imageStudioElementsModalOpen = true;
            this.$nextTick(() => this.$refs.imageStudioElementsSearch?.focus());
        },

        async loadImageStudioCatalog() {
            this.seedImageStudioPresetsFromEmbedded();
            try {
                const { data } = await axios.get(catalogUrl);
                if (data.presets?.length) {
                    this.imageStudioPresets = data.presets;
                }
                this.imageStudioGroups = data.groups || {};
                this.imageStudioExportFormats = data.export_formats || [];
                this.imageStudioTemplates = normalizeTemplatesList(data.templates || []);
                this.imageStudioPacks = data.packs || [];
                this.imageStudioPackCategories = data.pack_categories || [];
                this.imageStudioBrand = data.brand || null;
                this.imageStudioElements = this.normalizeImageStudioElementList(data.elements);
                this.imageStudioElementGroups = data.element_groups || {};
                this.imageStudioFonts = data.fonts?.length ? data.fonts : (this.imageStudioFonts || []);
                this.imageStudioFontGroups = data.font_groups || {};
                this.imageStudioIconGlyphs = data.icon_glyphs || [];
                this.buildImageStudioFontMap();
                preloadIconFontCdns(data.icon_fonts || []);
                preloadStarterGoogleFonts(this.imageStudioFonts);
                this.imageStudioBgRemovalDriver = data.background_removal_driver || this.imageStudioBgRemovalDriver || 'rembg';
                this.imageStudioBgRemovalLabel = data.background_removal_label || this.imageStudioBgRemovalLabel || '';
                const removeUrl = document.querySelector('meta[name="studio-remove-bg-url"]')?.getAttribute('content') || '';
                this.imageStudioBgRemoval = this.imageStudioBgRemovalDriver === 'imgly'
                    || !!data.background_removal_available
                    || (this.imageStudioBgRemovalDriver === 'rembg' && !!removeUrl);
                if (this.imageStudioEngine) {
                    this.imageStudioEngine.bgRemovalDriver = this.imageStudioBgRemovalDriver;
                    this.imageStudioEngine.bgRemovalUrl = removeUrl || null;
                }
                this.imageStudioPresetPlatformMap = data.preset_platform_map || {};
                if (data.primary_formats?.length) {
                    this.imageStudioPrimaryFormatDefs = data.primary_formats;
                }
                if (data.group_order?.length) {
                    this.imageStudioGroupOrder = data.group_order;
                }
                if (data.defaults?.width) {
                    this.imageStudioCustomWidth = data.defaults.width;
                }
                if (data.defaults?.height) {
                    this.imageStudioCustomHeight = data.defaults.height;
                }
                if (data.defaults?.preset) {
                    this.imageStudioPreset = data.defaults.preset;
                }
                this.imageStudioUnderlayEnabled = false;
                this.syncImageStudioElementsCatalog();
            } catch (e) {
                this.seedImageStudioFromEmbedded?.();
                this.error = e.response?.data?.message || 'Catálogo remoto indisponível — usando dados embutidos.';
            }
        },

        async loadImageStudioDesign() {
            const urlPreset = document
                .querySelector('meta[name="studio-initial-preset"]')
                ?.getAttribute('content')
                ?.trim();
            const preferUrlPreset = !!urlPreset;

            const p = this.resolveImageStudioPresetMeta?.(preferUrlPreset ? urlPreset : undefined)
                || this.resolveImageStudioPresetMeta?.()
                || { width: 1080, height: 1080 };
            let w = preferUrlPreset
                ? (p.width || 1080)
                : (this.imageStudioCustomWidth || p.width || 1080);
            let h = preferUrlPreset
                ? (p.height || 1080)
                : (this.imageStudioCustomHeight || p.height || 1080);
            let canvasJson = null;

            try {
                const raw = localStorage.getItem(this.studioDraftKey)
                    || localStorage.getItem('markcraft-studio-draft-v2');
                if (raw) {
                    const draft = JSON.parse(raw);
                    // Atalho da home (?preset=) manda no formato; canvas limpo nesse tamanho.
                    if (preferUrlPreset) {
                        this.imageStudioPreset = urlPreset;
                        canvasJson = null;
                        this.imageStudioDeckPages = [{
                            id: `slide-${Date.now()}`,
                            name: 'Slide 1',
                            canvas: null,
                        }];
                        this.imageStudioDeckPageIndex = 0;
                    } else {
                        if (draft?.width && draft?.height) {
                            w = draft.width;
                            h = draft.height;
                        }
                        if (draft?.preset) {
                            this.imageStudioPreset = draft.preset;
                        }
                        if (Array.isArray(draft?.deck_pages) && draft.deck_pages.length) {
                            this.imageStudioDeckPages = draft.deck_pages;
                            this.imageStudioDeckPageIndex = Math.max(
                                0,
                                Math.min(draft.deck_page_index ?? 0, draft.deck_pages.length - 1),
                            );
                            canvasJson = this.imageStudioDeckPages[this.imageStudioDeckPageIndex]?.canvas || null;
                        } else {
                            canvasJson = draft?.canvas || null;
                            this.imageStudioDeckPages = [{
                                id: `slide-${Date.now()}`,
                                name: 'Slide 1',
                                canvas: canvasJson,
                            }];
                            this.imageStudioDeckPageIndex = 0;
                        }
                        if (draft?.deck_kind) {
                            this.imageStudioDeckKind = draft.deck_kind;
                        }
                    }
                } else if (preferUrlPreset) {
                    this.imageStudioPreset = urlPreset;
                }
            } catch {
                /* draft inválido */
            }

            this.ensureImageStudioDeck?.();
            this.imageStudioCustomWidth = w;
            this.imageStudioCustomHeight = h;
            this.imageStudioEngine.init(w, h, this.imageStudioBgColor);
            if (canvasJson) {
                try {
                    await this.imageStudioEngine.loadFromJSON(canvasJson);
                } catch {
                    this.imageStudioEngine.setBackgroundColor(this.imageStudioBgColor, this.imageStudioBgTransparency);
                }
            } else {
                this.imageStudioEngine.setBackgroundColor(this.imageStudioBgColor, this.imageStudioBgTransparency);
            }
        },

        async saveImageStudioDesign() {
            if (!this.imageStudioEngine?.canvas) {
                return;
            }
            this.imageStudioSaving = true;
            try {
                this.flushImageStudioDeckPage?.();
                this.ensureImageStudioDeck?.();
                const current = this.imageStudioDeckPages[this.imageStudioDeckPageIndex]?.canvas
                    || this.imageStudioEngine.toJSON();
                if (current) {
                    current.width = this.imageStudioEngine.designWidth;
                    current.height = this.imageStudioEngine.designHeight;
                }
                const payload = {
                    preset: this.imageStudioPreset || 'custom',
                    width: this.imageStudioEngine.designWidth,
                    height: this.imageStudioEngine.designHeight,
                    canvas: current,
                    deck_pages: this.imageStudioDeckPages,
                    deck_page_index: this.imageStudioDeckPageIndex,
                    deck_kind: this.imageStudioDeckKind || 'presentation',
                    saved_at: Date.now(),
                };
                const raw = JSON.stringify(payload);
                // localStorage ~5MB; PSD 2500px com várias camadas estoura fácil
                const maxChars = 4.2 * 1024 * 1024;
                if (raw.length > maxChars) {
                    try {
                        localStorage.removeItem(this.studioDraftKey);
                    } catch {
                        /* ignore */
                    }
                    const note = ' Rascunho local não gravado (arte grande demais para o navegador) — exporte PNG/PSD para não perder.';
                    if (this.message && String(this.message).includes('PSD importado')) {
                        if (!String(this.message).includes('Rascunho local')) {
                            this.message = `${this.message}${note}`;
                        }
                    } else {
                        this.message = `Arte grande demais para rascunho automático.${note}`;
                    }
                    this.error = null;

                    return;
                }
                localStorage.setItem(this.studioDraftKey, raw);
            } catch (e) {
                const quota = e?.name === 'QuotaExceededError'
                    || e?.code === 22
                    || /quota/i.test(String(e?.message || ''));
                if (quota) {
                    try {
                        localStorage.removeItem(this.studioDraftKey);
                    } catch {
                        /* ignore */
                    }
                    const note = 'Rascunho local não gravado (limite do navegador). Exporte o arquivo para não perder o trabalho.';
                    if (this.message && String(this.message).includes('PSD importado')) {
                        if (!String(this.message).includes('Rascunho local')) {
                            this.message = `${this.message} ${note}`;
                        }
                        this.error = null;
                    } else {
                        this.error = note;
                    }
                } else {
                    this.error = e.message || 'Erro ao guardar rascunho local';
                }
            } finally {
                this.imageStudioSaving = false;
            }
        },

        async imageStudioExport(format) {
            if (!this.imageStudioEngine) {
                return;
            }
            try {
                this.imageStudioDeckBusy = true;
                this.flushImageStudioDeckPage?.();
                const baseOpts = this.buildImageStudioExportOptions();
                const opts = { ...baseOpts };

                if (format === 'pptx' || format === 'pdf' || format === 'zip' || format === 'png_zip') {
                    const pages = this.imageStudioDeckPages?.length || 1;
                    if (pages > 1 || format === 'pptx' || format === 'zip' || format === 'png_zip') {
                        this.message = pages > 1
                            ? `Gerando kit com ${pages} frames…`
                            : (format === 'pptx' ? 'Gerando PowerPoint…' : 'Gerando kit…');
                        if (format === 'pptx' || format === 'zip' || format === 'png_zip') {
                            opts.pagePngDataUrls = await this.collectImageStudioDeckExportUrls('png');
                            opts.zipPrefix = this.imageStudioDeckZipPrefix?.() || 'frame';
                        } else if (pages > 1) {
                            opts.pageJpegDataUrls = await this.collectImageStudioDeckExportUrls('jpg');
                        }
                    }
                }

                const blob = await this.imageStudioEngine.exportBlob(format, 0.92, opts);
                if (!blob) {
                    return;
                }
                const ext = format === 'jpeg' ? 'jpg' : (format === 'png_zip' ? 'zip' : format);
                const kind = this.imageStudioDeckKind || 'kit';
                const filename = `markcraft-${kind}-${this.imageStudioPreset || 'arte'}.${ext}`;
                const categoryMap = {
                    social: 'Instagram',
                    web: 'Blog',
                    presentation: 'Apresentacao',
                };
                const category = categoryMap[this.imageStudioDeckKind] || 'Outros';

                if (window.markcraftDesktop?.saveExport) {
                    const buffer = await blob.arrayBuffer();
                    const saved = await window.markcraftDesktop.saveExport({
                        filename,
                        category,
                        data: Array.from(new Uint8Array(buffer)),
                    });
                    this.imageStudioLastExport = { filename, format: ext, local: true, path: saved?.path };
                    this.message = saved?.path
                        ? `Salvo em: ${saved.path}`
                        : `Baixado: ${filename}`;
                } else {
                    downloadBlob(blob, filename);
                    this.imageStudioLastExport = { filename, format: ext, local: true };
                    this.message = `Baixado: ${filename} — o servidor não guarda sua arte.`;
                }
            } catch (e) {
                this.error = e.response?.data?.message || e.message || 'Erro ao exportar';
            } finally {
                this.imageStudioDeckBusy = false;
            }
        },

        async imageStudioPushThumbnail() {
            this.message = 'No MarkCraft a arte é baixada para o seu PC.';
        },

        async imageStudioPushLibrary() {
            this.message = 'Use Baixar — não há biblioteca no servidor.';
        },

        async imageStudioClearWorkspace() {
            if (!confirm('Limpar a prancheta por completo? Remove elementos, fundo e rascunho local desta aba. Baixe antes se quiser manter.')) {
                return;
            }
            localStorage.removeItem(this.studioDraftKey);
            this.imageStudioCropping = false;
            this.imageStudioEngine?.cancelCropMode?.(false);

            // Prancheta limpa de verdade: branco opaco, sem underlay, sem objetos.
            this.imageStudioBgColor = '#ffffff';
            this.imageStudioBgTransparency = 0;
            this.imageStudioUnderlayEnabled = false;
            this.imageStudioUnderlaySlideIndex = -1;
            this.imageStudioDeckPages = [this.newImageStudioDeckPage?.('Slide 1') || { id: 'slide-1', name: 'Slide 1', canvas: null }];
            this.imageStudioDeckPageIndex = 0;
            this.imageStudioDeckKind = 'presentation';

            const w = this.imageStudioEngine?.designWidth
                || this.imageStudioCustomWidth
                || 1080;
            const h = this.imageStudioEngine?.designHeight
                || this.imageStudioCustomHeight
                || 1080;

            this.imageStudioEngine?.init(w, h, '#ffffff');
            this.imageStudioEngine?.setBackgroundColor('#ffffff', 0);
            this.imageStudioEngine?.setUnderlayState?.(-1, false);
            this.imageStudioEngine?.pushHistory?.();

            this.imageStudioLastExport = null;
            this.refreshImageStudioLayers?.();
            this.closeImageStudioContextMenu?.();
            this.$nextTick?.(() => this.fitImageStudioCanvas?.());
            this.message = 'Prancheta limpa: fundo branco, sem elementos.';
        },

        /** Alias do botão MarkCraft */
        async clearImageStudioWorkspace(force = false) {
            if (force) {
                localStorage.removeItem(this.studioDraftKey);
            }
            return this.imageStudioClearWorkspace();
        },

        async imageStudioAddIconGlyph(glyph) {
            await base.imageStudioAddIconGlyph.call(this, glyph);
            const active = this.imageStudioEngine?.getActiveObject?.();
            if (active && resolveInsertFill) {
                const fill = resolveInsertFill(active.fill, DEFAULT_SHAPE_FILL);
                if (fill && fill !== active.fill) {
                    active.set('fill', fill);
                    this.imageStudioEngine.canvas?.requestRenderAll();
                }
            }
        },
    };
}

document.addEventListener('alpine:init', () => {
    Alpine.data('markCraftHub', markCraftHub);
    Alpine.data('markCraftStudio', () => ({
        ...markCraftStudioMethods(),
        async init() {
            this.seedImageStudioBgRemovalFromPage?.();
            this.imageStudioUnderlayEnabled = false;
            // Sem beforeunload: o aviso nativo cancelava cliques do navbar
            // ("botão sem efeito") e competia com o Vite HMR. Rascunho fica no localStorage.
            await this.$nextTick();
            await this.initImageStudio();
            this.imageStudioUnderlayEnabled = false;
            this.imageStudioEngine?.setUnderlayState?.(null, false);
            if (this.isMarkCraftDesktopApp?.()) {
                await this.browseDesktopWorkspace?.();
            }

            const startPreset = document
                .querySelector('meta[name="studio-initial-preset"]')
                ?.getAttribute('content')
                ?.trim();
            if (startPreset && typeof this.switchImageStudioPreset === 'function') {
                await this.switchImageStudioPreset(startPreset);
            }
        },
    }));
});

window.Alpine = Alpine;
Alpine.start();
