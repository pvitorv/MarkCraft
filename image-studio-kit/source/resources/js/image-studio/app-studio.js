import Alpine from 'alpinejs';
import sort from '@alpinejs/sort';
import axios from 'axios';
import { imageStudioMethods } from './imageStudio';
import { preloadIconFontCdns, preloadStarterGoogleFonts } from './imageStudioTextFonts';

Alpine.plugin(sort);

const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
const saveUrl = document.querySelector('meta[name="studio-save-url"]')?.getAttribute('content');
const catalogUrl = document.querySelector('meta[name="studio-catalog-url"]')?.getAttribute('content') || '/painel/studio/catalogo';

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

function blogStudioMethods() {
    const base = imageStudioMethods();

    return {
        ...base,

        projectId: null,
        message: '',
        error: '',
        studioDraftKey: 'blog-studio-draft',
        slides: [],
        selectedSlide: null,
        imageStudioUnderlayEnabled: false,
        imageStudioPreset: 'blog_cover',
        imageStudioCustomWidth: 1200,
        imageStudioCustomHeight: 630,

        // stubs do host CriaSys (não existem no blog)
        selectedThumbnailPlatform: null,
        thumbnailSettingsByPlatform: {},
        thumbnailPreviewUrl: null,
        thumbnailPreviewUrls: {},
        switchTab() {},
        applyThumbnailSettingsPatch() {},
        upsertProjectLibraryAsset() {},
        async loadProjectLibraryAssets() {},

        async loadImageStudioCatalog() {
            this.seedImageStudioPresetsFromEmbedded();
            try {
                const { data } = await axios.get(catalogUrl);
                if (data.presets?.length) {
                    this.imageStudioPresets = data.presets;
                }
                this.imageStudioGroups = data.groups || {};
                this.imageStudioExportFormats = data.export_formats || [];
                this.imageStudioTemplates = data.templates || [];
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
                if (!this.imageStudioPresets?.length) {
                    /* fallback embutido no mixin */
                }
                this.error = e.response?.data?.message || 'Catálogo remoto indisponível — usando dados embutidos.';
            }
        },

        async loadImageStudioDesign() {
            const p = this.resolveImageStudioPresetMeta?.() || { width: 1280, height: 720 };
            let w = this.imageStudioCustomWidth || p.width || 1280;
            let h = this.imageStudioCustomHeight || p.height || 720;
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

        async imageStudioExport(format, options = {}) {
            if (!this.imageStudioEngine) {
                return;
            }
            const openPreview = options.openPreview !== false;
            const alsoSaveToBlog = options.saveToBlog === true;
            try {
                const blob = await this.imageStudioEngine.exportBlob(format, 0.92, this.buildImageStudioExportOptions());
                if (!blob) {
                    return;
                }
                const ext = format === 'jpeg' ? 'jpg' : format;
                const filename = `arte-${this.imageStudioPreset || 'custom'}.${ext}`;

                downloadBlob(blob, filename);
                this.imageStudioLastExport = { filename, format: ext, local: true };
                this.message = `Baixado: ${filename}`;

                if (alsoSaveToBlog && saveUrl && (ext === 'png' || ext === 'jpg' || ext === 'webp')) {
                    const form = new FormData();
                    form.append('file', blob, filename);
                    form.append('format', ext);
                    form.append('preset', this.imageStudioPreset || 'custom');
                    const { data } = await axios.post(saveUrl, form);
                    this.imageStudioLastExport = { ...this.imageStudioLastExport, ...data.export };
                    this.message = data.message || 'Salvo no blog e baixado.';
                    if (openPreview && data.export?.url) {
                        window.open(data.export.url, '_blank');
                    }
                }
            } catch (e) {
                this.error = e.response?.data?.message || e.message || 'Erro ao exportar';
            }
        },

        async imageStudioSaveToBlog() {
            await this.imageStudioExport('png', { openPreview: true, saveToBlog: true });
        },

        async imageStudioPushThumbnail() {
            this.message = 'No blog, use “Salvar no blog” ou baixe a imagem e envie na capa do artigo.';
        },

        async imageStudioPushLibrary() {
            await this.imageStudioSaveToBlog();
        },

        async imageStudioClearWorkspace() {
            if (!confirm('Limpar o canvas? O rascunho local será apagado. Baixe antes se precisar.')) {
                return;
            }
            localStorage.removeItem(this.studioDraftKey);
            this.imageStudioCropping = false;
            this.imageStudioEngine?.cancelCropMode?.(false);
            const p = this.resolveImageStudioPresetMeta?.() || { width: 1280, height: 720 };
            this.imageStudioEngine?.init(p.width, p.height, this.imageStudioBgColor || '#ffffff');
            this.imageStudioEngine?.setBackgroundColor(this.imageStudioBgColor || '#ffffff', this.imageStudioBgTransparency ?? 0);
            this.imageStudioEngine?.pushHistory?.();
            this.refreshImageStudioLayers?.();
            this.closeImageStudioContextMenu?.();
            this.message = 'Área limpa.';
        },
    };
}

document.addEventListener('alpine:init', () => {
    Alpine.data('blogImageStudio', () => ({
        ...blogStudioMethods(),
        async init() {
            this.seedImageStudioBgRemovalFromPage?.();
            this.imageStudioUnderlayEnabled = false;
            await this.$nextTick();
            await this.initImageStudio();
            this.imageStudioUnderlayEnabled = false;
            this.imageStudioEngine?.setUnderlayState?.(null, false);
        },
    }));
});

window.Alpine = Alpine;
Alpine.start();
