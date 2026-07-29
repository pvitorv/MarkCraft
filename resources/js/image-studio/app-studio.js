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
        studioDraftKey: 'markcraft-studio-draft-v2',
        slides: [],
        selectedSlide: null,
        imageStudioUnderlayEnabled: false,
        imageStudioPreset: 'ig_feed_square',
        imageStudioCustomWidth: 1080,
        imageStudioCustomHeight: 1080,
        imageStudioShapeFill: DEFAULT_SHAPE_FILL,
        imageStudioShapeStroke: DEFAULT_SHAPE_STROKE,

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
            const p = this.resolveImageStudioPresetMeta?.() || { width: 1080, height: 1080 };
            let w = this.imageStudioCustomWidth || p.width || 1080;
            let h = this.imageStudioCustomHeight || p.height || 1080;
            let canvasJson = null;

            try {
                const raw = localStorage.getItem(this.studioDraftKey);
                if (raw) {
                    const draft = JSON.parse(raw);
                    if (draft?.width && draft?.height) {
                        w = draft.width;
                        h = draft.height;
                    }
                    if (draft?.preset) {
                        this.imageStudioPreset = draft.preset;
                    }
                    canvasJson = draft?.canvas || null;
                }
            } catch {
                /* draft inválido */
            }

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
                const json = this.imageStudioEngine.toJSON();
                json.width = this.imageStudioEngine.designWidth;
                json.height = this.imageStudioEngine.designHeight;
                localStorage.setItem(this.studioDraftKey, JSON.stringify({
                    preset: this.imageStudioPreset || 'custom',
                    width: json.width,
                    height: json.height,
                    canvas: json,
                    saved_at: Date.now(),
                }));
            } catch (e) {
                this.error = e.message || 'Erro ao guardar rascunho local';
            } finally {
                this.imageStudioSaving = false;
            }
        },

        async imageStudioExport(format) {
            if (!this.imageStudioEngine) {
                return;
            }
            try {
                const blob = await this.imageStudioEngine.exportBlob(format, 0.92, this.buildImageStudioExportOptions());
                if (!blob) {
                    return;
                }
                const ext = format === 'jpeg' ? 'jpg' : format;
                const filename = `markcraft-${this.imageStudioPreset || 'arte'}.${ext}`;
                downloadBlob(blob, filename);
                this.imageStudioLastExport = { filename, format: ext, local: true };
                this.message = `Baixado: ${filename} — o servidor não guarda sua arte.`;
            } catch (e) {
                this.error = e.response?.data?.message || e.message || 'Erro ao exportar';
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
