import {
    Canvas,
    FabricText,
    IText,
    Textbox,
    FabricImage,
    Rect,
    Circle,
    Ellipse,
    Line,
    Gradient,
    loadSVGFromURL,
    util,
    filters,
    Shadow,
    ActiveSelection,
    Group,
    InteractiveFabricObject,
    controlsUtils,
} from 'fabric';

/** Detecção mobile: servidor envia meta `studio-layout`. */
export const IMAGE_STUDIO_MOBILE_MQ = '(max-width: 1023px) and (pointer: coarse)';

export function readStudioLayoutMeta() {
    return document.querySelector('meta[name="studio-layout"]')?.getAttribute('content')?.trim() || '';
}

export function isImageStudioMobileShell() {
    const layout = readStudioLayoutMeta();
    if (layout === 'mobile') {
        return true;
    }
    if (layout === 'desktop') {
        return false;
    }

    return false;
}

/** Props customizadas preservadas no JSON / histórico do canvas. */
const FABRIC_JSON_PROPS = [
    'name',
    'criasysId',
    'criasysGuide',
    'criasysCropGuide',
    'criasysSvgIcon',
    'criasysRecolorable',
    'criasysLastFill',
    'criasysTemplateLayer',
];

/**
 * Trava overflow dos pais durante arraste (range / alças Fabric).
 * Sem isso, overflow-y:auto da sidebar e do canvas rouba o gesto.
 */
let studioScrollLockItems = null;

function unlockStudioScrollDuringDrag() {
    if (!studioScrollLockItems) {
        return;
    }
    studioScrollLockItems.forEach((item) => {
        item.node.style.overflow = item.overflow;
        item.node.style.overflowX = item.overflowX;
        item.node.style.overflowY = item.overflowY;
        item.node.style.touchAction = item.touchAction;
        item.node.classList.remove('is-scroll-locked');
        try {
            item.node.scrollTop = item.scrollTop;
            item.node.scrollLeft = item.scrollLeft;
        } catch {
            /* ignore */
        }
    });
    studioScrollLockItems = null;
    document.documentElement.classList.remove('is-studio-drag-lock');
    document.body.classList.remove('is-studio-drag-lock');
}

function lockStudioScrollDuringDrag(fromEl) {
    if (!studioScrollLockItems) {
        const roots = new Set();
        let el = fromEl && fromEl.nodeType === 1 ? fromEl : null;
        while (el) {
            const cs = window.getComputedStyle(el);
            if (/(auto|scroll|overlay)/.test(cs.overflowY) || /(auto|scroll|overlay)/.test(cs.overflowX)) {
                roots.add(el);
            }
            el = el.parentElement;
        }
        document.querySelectorAll('.is-canvas-dropzone, .is-sidebar-drawer, .sm-canvas, .sm-sheet__body').forEach((node) => roots.add(node));
        studioScrollLockItems = [];
        roots.forEach((node) => {
            studioScrollLockItems.push({
                node,
                overflow: node.style.overflow,
                overflowX: node.style.overflowX,
                overflowY: node.style.overflowY,
                touchAction: node.style.touchAction,
                scrollTop: node.scrollTop,
                scrollLeft: node.scrollLeft,
            });
            node.style.overflow = 'hidden';
            node.style.overflowX = 'hidden';
            node.style.overflowY = 'hidden';
            node.style.touchAction = 'none';
            node.classList.add('is-scroll-locked');
        });
        document.documentElement.classList.add('is-studio-drag-lock');
        document.body.classList.add('is-studio-drag-lock');
    }

    const onEnd = () => {
        window.removeEventListener('pointerup', onEnd, true);
        window.removeEventListener('pointercancel', onEnd, true);
        window.removeEventListener('mouseup', onEnd, true);
        window.removeEventListener('touchend', onEnd, true);
        unlockStudioScrollDuringDrag();
    };
    window.addEventListener('pointerup', onEnd, true);
    window.addEventListener('pointercancel', onEnd, true);
    window.addEventListener('mouseup', onEnd, true);
    window.addEventListener('touchend', onEnd, true);
}

function installStudioRangeScrollLock() {
    if (typeof document === 'undefined' || document.__markcraftRangeScrollLock) {
        return;
    }
    document.__markcraftRangeScrollLock = true;
    document.addEventListener('pointerdown', (e) => {
        const t = e.target;
        if (!(t instanceof HTMLInputElement) || t.type !== 'range') {
            return;
        }
        if (!t.closest('.studio-shell, .is-workspace-row, .is-sidebar-drawer, .studio-canvas-toolbar, .sm-shell, .sm-sheet__body')) {
            return;
        }
        lockStudioScrollDuringDrag(t);
    }, true);
}

/** Controles padrão do Fabric — tamanho visual normal (não inflar as bolinhas). */
InteractiveFabricObject.ownDefaults = {
    ...(InteractiveFabricObject.ownDefaults || {}),
    cornerStyle: 'circle',
    cornerColor: '#a78bfa',
    cornerStrokeColor: '#ffffff',
    borderColor: '#a78bfa',
    cornerSize: 12,
    touchCornerSize: 24,
    transparentCorners: false,
    borderScaleFactor: 1.5,
    padding: 4,
    hasControls: true,
    hasBorders: true,
    lockScalingX: false,
    lockScalingY: false,
};

/**
 * Hit-test por distância (viewport px): clicar perto da bolinha conta como a bolinha,
 * sem desenhar alças enormes. O Fabric padrão às vezes perde o canto e cai em "drag".
 */
function installCornerHitFix(canvas, getViewportZoom) {
    if (!canvas || canvas._mcCornerHitFixed) {
        return;
    }
    canvas._mcCornerHitFixed = true;

    const maxHitPx = () => {
        const z = Math.max(0.08, Number(getViewportZoom?.()) || 1);
        // ~14px na tela, convertido p/ coords do canvas (design / viewport)
        return Math.max(12, Math.round(14 / z));
    };

    const findNearestControl = (target, pointer, forTouch = false) => {
        if (!target?.hasControls || !target.oCoords || !target.controls) {
            return undefined;
        }
        if (target.canvas?.getActiveObject() !== target) {
            return undefined;
        }
        const limit = forTouch ? Math.max(maxHitPx(), 24) : maxHitPx();
        let bestKey = null;
        let bestDist = limit;
        for (const key of Object.keys(target.controls)) {
            if (!target.isControlVisible?.(key)) {
                continue;
            }
            const coord = target.oCoords[key];
            if (!coord) {
                continue;
            }
            const d = Math.hypot(pointer.x - coord.x, pointer.y - coord.y);
            if (d <= bestDist) {
                bestDist = d;
                bestKey = key;
            }
        }
        if (!bestKey) {
            return undefined;
        }
        target.__corner = bestKey;

        return {
            key: bestKey,
            control: target.controls[bestKey],
            coord: target.oCoords[bestKey],
        };
    };

    // 1) findControl: se o polígono do Fabric falhar, usa distância ao centro da bolinha
    const objects = () => canvas.getObjects?.() || [];
    const patchTarget = (target) => {
        if (!target || target._mcFindControlPatched) {
            return;
        }
        target._mcFindControlPatched = true;
        const original = target.findControl?.bind(target);
        target.findControl = (pointer, forTouch = false) => {
            const hit = original?.(pointer, forTouch);
            if (hit) {
                return hit;
            }

            return findNearestControl(target, pointer, forTouch);
        };
    };

    canvas.on('object:added', (e) => patchTarget(e.target));
    objects().forEach(patchTarget);

    // 2) _setupCurrentTransform: se há canto, FORÇA alreadySelected + handler de escala
    //    (senão o Fabric usa dragHandler e o objeto só anda)
    const setup = canvas._setupCurrentTransform.bind(canvas);
    canvas._setupCurrentTransform = (e, target, alreadySelected) => {
        patchTarget(target);
        try {
            const pointer = canvas.getViewportPoint(e);
            const found = target.findControl?.(pointer, false)
                || target.findControl?.(pointer, true);
            if (found?.key) {
                target.__corner = found.key;
                alreadySelected = true;
            }
        } catch {
            /* keep fabric default */
        }

        return setup(e, target, alreadySelected);
    };
}
import { writePsdBuffer, readPsd } from 'ag-psd';
import { jsPDF } from 'jspdf';
import PptxGenJS from 'pptxgenjs';
import JSZip from 'jszip';
import {
    EMOJI_FONT_STACK,
    addCanvasObject,
    centerObjectOnCanvas,
    createShapeFromSpec,
} from './imageStudioShapes';
import {
    buildTextStyleFromFont,
    ensureFontLoaded,
    findFontBySlug,
    resolveFontWeight,
    fontCssFamily,
    isTextLikeType,
    normalizeColorInput,
    preloadIconFontCdns,
    preloadStarterGoogleFonts,
    FALLBACK_FONTS,
} from './imageStudioTextFonts';

const OBJECT_SCALE_MIN_PERCENT = 5;
const OBJECT_SCALE_MAX_PERCENT = 600;

const FALLBACK_PRESETS = [
    { slug: 'yt_thumb_hd', name: 'Thumbnail Full HD', group: 'youtube', group_label: 'YouTube', width: 1920, height: 1080, icon: '▶', aspect: '16:9' },
    { slug: 'yt_thumb', name: 'Thumbnail 16:9', group: 'youtube', group_label: 'YouTube', width: 1280, height: 720, icon: '▶', aspect: '16:9' },
    { slug: 'yt_shorts', name: 'Shorts capa 9:16', group: 'youtube', group_label: 'YouTube', width: 1080, height: 1920, icon: '▲', aspect: '9:16' },
    { slug: 'ig_feed_square', name: 'Feed quadrado 1:1', group: 'instagram', group_label: 'Instagram', width: 1080, height: 1080, icon: '◎', aspect: '1:1' },
    { slug: 'ig_story', name: 'Story 9:16', group: 'instagram', group_label: 'Instagram', width: 1080, height: 1920, icon: '▲', aspect: '9:16' },
    { slug: 'tt_video', name: 'Vídeo / capa 9:16', group: 'tiktok', group_label: 'TikTok', width: 1080, height: 1920, icon: '♪', aspect: '9:16' },
    { slug: 'ratio_16_9_hd', name: 'Paisagem 16:9 — 1280', group: 'ratios', group_label: 'Proporções genéricas', width: 1280, height: 720, icon: '▭', aspect: '16:9' },
];

const DEFAULT_CANVAS_FALLBACK = { slug: 'yt_thumb', width: 1280, height: 720, aspect: '16:9' };

function normalizeFabricType(obj) {
    const t = String(obj?.type || '').toLowerCase();
    if (t === 'image') return 'image';
    if (isTextLikeType(t)) return 'text';
    return t;
}

function isFabricText(obj) {
    return !!obj && (
        obj instanceof Textbox
        || obj instanceof IText
        || obj instanceof FabricText
        || isTextLikeType(obj?.type)
    );
}

const LITERAL_LF = String.fromCharCode(92, 110); // "\" + "n"
const LITERAL_CR = String.fromCharCode(92, 114); // "\" + "r"
const LITERAL_CRLF = String.fromCharCode(92, 114, 92, 110); // "\r\n" literal
const REAL_LF = String.fromCharCode(10);
const REAL_CR = String.fromCharCode(13);

/**
 * Converte sequências literais \n / \r\n (dois chars) em Line Feed real.
 * Usa fromCharCode para não depender de escapes no bundler.
 */
export function normalizeMultilineText(text) {
    if (typeof text !== 'string' || text === '') {
        return text;
    }
    let out = text;
    for (let pass = 0; pass < 4; pass += 1) {
        if (!out.includes(LITERAL_LF) && !out.includes(LITERAL_CR)) {
            break;
        }
        out = out
            .split(LITERAL_CRLF)
            .join(REAL_LF)
            .split(LITERAL_LF)
            .join(REAL_LF)
            .split(LITERAL_CR)
            .join(REAL_LF);
    }
    return out
        .split(REAL_CR + REAL_LF)
        .join(REAL_LF)
        .split(REAL_CR)
        .join(REAL_LF);
}

/** Normaliza text em specs de template/pack antes de criar objetos Fabric. */
export function normalizeTemplateTextFields(template) {
    if (!template || typeof template !== 'object') {
        return template;
    }
    const objects = Array.isArray(template.objects) ? template.objects : null;
    if (!objects) {
        return template;
    }
    template.objects = objects.map((spec) => {
        if (!spec || typeof spec !== 'object') {
            return spec;
        }
        const kind = spec.kind || spec.type;
        if (kind === 'text' && typeof spec.text === 'string') {
            return { ...spec, text: normalizeMultilineText(spec.text) };
        }
        return spec;
    });
    return template;
}

export function normalizeTemplatesList(list) {
    if (!Array.isArray(list)) {
        return list;
    }
    return list.map((tpl) => normalizeTemplateTextFields(tpl));
}

function createMultilineTextObject(text, options = {}) {
    const content = normalizeMultilineText(text ?? '');
    const width = Math.max(80, Number(options.width) || 400);
    return new Textbox(content, {
        ...options,
        text: content,
        width,
        editable: options.editable !== false,
        splitByGrapheme: false,
    });
}

function isFabricImage(obj) {
    return !!obj && (obj instanceof FabricImage || normalizeFabricType(obj) === 'image');
}

function isFabricActiveSelection(obj) {
    return !!obj && (
        obj instanceof ActiveSelection
        || normalizeFabricType(obj) === 'activeselection'
    );
}

function isFabricGroup(obj) {
    return !!obj && (
        obj instanceof Group
        || normalizeFabricType(obj) === 'group'
    ) && !isFabricActiveSelection(obj);
}

function isFabricShape(obj) {
    if (!obj || obj.criasysGuide) {
        return false;
    }
    if (isFabricActiveSelection(obj) || isFabricGroup(obj)) {
        return false;
    }

    return !isFabricText(obj) && !isFabricImage(obj);
}

/** Forma simples OU ícone SVG marcado como recolorível (não todo Group de usuário). */
function isRecolorableObject(obj) {
    if (!obj || obj.criasysGuide || obj.criasysCropGuide) {
        return false;
    }
    if (isFabricActiveSelection(obj) || isFabricText(obj) || isFabricImage(obj)) {
        return false;
    }
    if (isFabricGroup(obj)) {
        return !!(obj.criasysSvgIcon || obj.criasysRecolorable);
    }

    return isFabricShape(obj);
}

/** Percorre folhas pintáveis (pula imagem/texto). */
function forEachPaintLeaf(obj, fn) {
    if (!obj) {
        return;
    }
    const nested = typeof obj.getObjects === 'function'
        ? obj.getObjects()
        : (obj._objects || null);
    if (Array.isArray(nested) && nested.length) {
        nested.forEach((child) => forEachPaintLeaf(child, fn));
        return;
    }
    if (isFabricImage(obj) || isFabricText(obj)) {
        return;
    }
    fn(obj);
}

function findFirstPaintLeaf(obj) {
    let found = null;
    forEachPaintLeaf(obj, (leaf) => {
        if (!found) {
            found = leaf;
        }
    });

    return found;
}

/**
 * Aplica fill/stroke numa folha. Em ícones SVG, força fill mesmo se era "none"
 * (senão o color picker parece não fazer nada).
 */
function paintLeafObject(leaf, { fill, stroke, strokeWidth, isLine, forceFill = true } = {}) {
    if (!leaf) {
        return;
    }
    const type = String(leaf.type || '').toLowerCase();
    const leafIsLine = type === 'line' || isLine;
    const updates = {};

    if (!leafIsLine && fill !== undefined) {
        if (fill === 'transparent' || fill === '') {
            updates.fill = 'transparent';
        } else if (fill != null) {
            updates.fill = fill;
        }
    }

    if (strokeWidth != null) {
        const sw = Math.max(0, parseFloat(strokeWidth) || 0);
        updates.strokeWidth = sw;
        if (sw > 0) {
            updates.stroke = stroke || leaf.stroke || '#ffffff';
        } else {
            updates.stroke = null;
        }
    } else if (stroke != null && leaf.stroke && leaf.stroke !== 'none') {
        updates.stroke = stroke;
    }

    // Sem forceFill: não inventa fill em path que era só stroke — mas ícones usam forceFill=true
    if (!forceFill && updates.fill !== undefined) {
        const prev = leaf.fill;
        const hadNoFill = prev == null || prev === '' || prev === 'none' || prev === 'transparent';
        const hadStroke = leaf.stroke && leaf.stroke !== 'none' && (parseFloat(leaf.strokeWidth) || 0) > 0;
        if (hadNoFill && hadStroke && updates.fill !== 'transparent') {
            updates.stroke = updates.fill;
            delete updates.fill;
        }
    }

    if (Object.keys(updates).length) {
        leaf.set(updates);
        leaf.set('dirty', true);
    }
}

/** Coords % para degradê linear a partir do ângulo (0° = esquerda→direita). */
function linearGradientCoordsFromAngle(angleDeg = 0) {
    const rad = ((Number(angleDeg) || 0) % 360) * (Math.PI / 180);
    const x = Math.cos(rad);
    const y = Math.sin(rad);

    return {
        x1: 0.5 - x / 2,
        y1: 0.5 - y / 2,
        x2: 0.5 + x / 2,
        y2: 0.5 + y / 2,
    };
}

function buildShapeGradientFill({ mode = 'linear', colorA, colorB, angle = 90 } = {}) {
    const a = normalizeColorInput(colorA, '#0d9488');
    const b = normalizeColorInput(colorB, '#0f172a');
    const type = mode === 'radial' ? 'radial' : 'linear';
    const coords = type === 'radial'
        ? { x1: 0.5, y1: 0.5, r1: 0, x2: 0.5, y2: 0.5, r2: 0.65 }
        : linearGradientCoordsFromAngle(angle);

    return new Gradient({
        type,
        gradientUnits: 'percentage',
        coords,
        colorStops: [
            { offset: 0, color: a },
            { offset: 1, color: b },
        ],
    });
}

function readShapeFillState(fill) {
    if (fill == null || fill === '' || fill === 'transparent') {
        return {
            mode: 'solid',
            fill: '',
            colorA: '#0d9488',
            colorB: '#0f172a',
            angle: 90,
        };
    }

    if (typeof fill === 'object' && fill && (fill.type === 'linear' || fill.type === 'radial' || Array.isArray(fill.colorStops))) {
        const stops = Array.isArray(fill.colorStops) ? fill.colorStops : [];
        const first = stops[0]?.color || '#0d9488';
        const last = stops[stops.length - 1]?.color || '#0f172a';
        let angle = 90;
        if (fill.type === 'linear' && fill.coords) {
            const { x1 = 0, y1 = 0, x2 = 1, y2 = 0 } = fill.coords;
            angle = Math.round((Math.atan2(y2 - y1, x2 - x1) * 180) / Math.PI);
            if (angle < 0) {
                angle += 360;
            }
        }

        return {
            mode: fill.type === 'radial' ? 'radial' : 'linear',
            fill: normalizeColorInput(first, '#0d9488'),
            colorA: normalizeColorInput(first, '#0d9488'),
            colorB: normalizeColorInput(last, '#0f172a'),
            angle,
        };
    }

    const solid = normalizeColorInput(fill, '#ffffff');

    return {
        mode: 'solid',
        fill: solid,
        colorA: solid,
        colorB: '#0f172a',
        angle: 90,
    };
}

const DEFAULT_FILTER_STATE = {
    brightness: 50,
    contrast: 50,
    saturation: 50,
    blur: 0,
    grayscale: 0,
    vignette: 0,
};

function loadHtmlImage(url) {
    return new Promise((resolve, reject) => {
        const el = new Image();
        el.onload = () => resolve(el);
        el.onerror = () => reject(new Error('Navegador não conseguiu decodificar a imagem'));
        el.src = url;
    });
}

function parseHexColor(hex) {
    let h = String(hex || '#ffffff').replace('#', '').trim();
    if (h.length === 3) {
        h = h.split('').map((c) => c + c).join('');
    }
    if (h.length !== 6) {
        return { r: 255, g: 255, b: 255 };
    }

    return {
        r: parseInt(h.substring(0, 2), 16),
        g: parseInt(h.substring(2, 4), 16),
        b: parseInt(h.substring(4, 6), 16),
    };
}

function parseCanvasBackgroundState(backgroundColor, fallbackColor = '#ffffff') {
    const bg = backgroundColor;
    if (!bg || bg === 'transparent') {
        return { color: fallbackColor, transparency: 100 };
    }
    if (typeof bg === 'string' && bg.startsWith('rgba')) {
        const match = bg.match(/rgba\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*,\s*([\d.]+)\s*\)/i);
        if (match) {
            const alpha = parseFloat(match[4]);
            const r = Number(match[1]).toString(16).padStart(2, '0');
            const g = Number(match[2]).toString(16).padStart(2, '0');
            const b = Number(match[3]).toString(16).padStart(2, '0');
            return {
                color: `#${r}${g}${b}`,
                transparency: Math.round((1 - alpha) * 100),
            };
        }
    }

    return { color: bg, transparency: 0 };
}

async function canvasToBlob(canvas, mime, quality = 0.92) {
    return new Promise((resolve) => {
        canvas.toBlob((blob) => resolve(blob), mime, quality);
    });
}

function drawCoverMedia(ctx, media, destW, destH) {
    const sw = media.videoWidth || media.width || 1;
    const sh = media.videoHeight || media.height || 1;
    const scale = Math.max(destW / sw, destH / sh);
    const dw = sw * scale;
    const dh = sh * scale;
    ctx.drawImage(media, (destW - dw) / 2, (destH - dh) / 2, dw, dh);
}

async function loadVideoFrameCanvas(url) {
    return new Promise((resolve, reject) => {
        const video = document.createElement('video');
        video.crossOrigin = 'anonymous';
        video.preload = 'auto';
        video.muted = true;
        video.playsInline = true;
        const fail = () => {
            video.src = '';
            reject(new Error('video'));
        };
        video.addEventListener('error', fail, { once: true });
        video.addEventListener('loadeddata', () => {
            const seekTo = Math.min(0.5, Math.max(0, (video.duration || 1) * 0.05));
            video.currentTime = Number.isFinite(seekTo) ? seekTo : 0;
        }, { once: true });
        video.addEventListener('seeked', () => {
            const c = document.createElement('canvas');
            c.width = Math.max(1, video.videoWidth || 1);
            c.height = Math.max(1, video.videoHeight || 1);
            c.getContext('2d').drawImage(video, 0, 0);
            video.src = '';
            resolve(c);
        }, { once: true });
        video.src = url;
    });
}

async function loadMediaDrawable(url, isVideo = false) {
    if (!url) {
        return null;
    }
    if (isVideo) {
        return loadVideoFrameCanvas(url);
    }

    return loadHtmlImage(url);
}

async function compositeFrameOnCanvasDataUrl(engine, frameUrl, frameVisible = true, exportOpts = {}) {
    const exportCanvas = await engine.renderExportCanvas(1, exportOpts);
    const w = exportCanvas.width;
    const h = exportCanvas.height;
    if (!frameUrl || frameVisible === false) {
        return exportCanvas.toDataURL('image/png');
    }
    const off = document.createElement('canvas');
    off.width = w;
    off.height = h;
    const ctx = off.getContext('2d');
    ctx.drawImage(exportCanvas, 0, 0, w, h);
    const frame = await loadHtmlImage(frameUrl);
    ctx.drawImage(frame, 0, 0, w, h);
    return off.toDataURL('image/png');
}

export class ImageStudioEngine {
    static readEmbeddedJson(elementId) {
        const el = document.getElementById(elementId);
        if (!el?.textContent?.trim()) {
            return null;
        }
        try {
            return JSON.parse(el.textContent);
        } catch {
            return null;
        }
    }

    constructor(canvasEl) {
        this.canvasEl = canvasEl;
        this.canvas = null;
        this.onChange = null;
        this.history = [];
        this.historyIndex = -1;
        this.historyPaused = false;
        this.historyTimeout = null;
        this.maxHistory = 40;
        this.showGrid = false;
        this.snapToGrid = false;
        this.gridSize = 20;
        this.designWidth = 1080;
        this.designHeight = 1080;
        this.viewportZoom = 1;
        this.scaleWrapper = null;
        this.showFormatGuides = true;
        this._bgColor = '#ffffff';
        this._bgTransparency = 0;
        this._underlaySlideIndex = null;
        this._underlayEnabled = true;
    }

    getBackgroundState() {
        return {
            color: this._bgColor || '#ffffff',
            transparency: this._bgTransparency ?? 0,
        };
    }

    getBackgroundPaint(options = {}) {
        if (options.omitCanvasBackground) {
            return null;
        }
        const transparency = Math.max(0, Math.min(100, Number(this._bgTransparency) || 0));
        if (transparency >= 100) {
            return null;
        }
        const { r, g, b } = parseHexColor(this._bgColor);
        const alpha = transparency <= 0 ? 1 : 1 - (transparency / 100);

        return { r, g, b, a: alpha };
    }

    canvasBackgroundIsTransparent() {
        return (Number(this._bgTransparency) || 0) >= 100;
    }

    syncBackgroundUiState() {
        const parsed = parseCanvasBackgroundState(this.canvas?.backgroundColor, this._bgColor);
        this._bgColor = parsed.color;
        this._bgTransparency = parsed.transparency;

        return this.getBackgroundState();
    }

    getUnderlayState() {
        return {
            slideIndex: this._underlaySlideIndex,
            enabled: this._underlayEnabled !== false,
        };
    }

    setUnderlayState(slideIndex, enabled = true) {
        const idx = slideIndex === null || slideIndex === undefined || Number(slideIndex) < 0
            ? null
            : Math.max(0, Number(slideIndex) || 0);
        this._underlaySlideIndex = idx;
        this._underlayEnabled = enabled !== false;
    }

    setScaleWrapper(el) {
        this.scaleWrapper = el || null;
        this.applyViewportZoom(this.viewportZoom || 1);
    }

    configureSelectableObject(obj) {
        if (!obj || obj.criasysGuide) {
            return;
        }
        obj.set({
            cornerStyle: 'circle',
            cornerColor: '#a78bfa',
            cornerStrokeColor: '#ffffff',
            borderColor: '#a78bfa',
            cornerSize: 12,
            touchCornerSize: 24,
            padding: 4,
            transparentCorners: false,
            borderScaleFactor: 1.5,
            hasControls: true,
            hasBorders: true,
            centeredRotation: true,
            centeredScaling: false,
            lockScalingFlip: false,
            lockRotation: false,
            lockScalingX: false,
            lockScalingY: false,
            lockSkewingX: true,
            lockSkewingY: true,
            selectable: true,
            evented: true,
        });
        if (typeof obj.setControlsVisibility === 'function') {
            obj.setControlsVisibility({
                tl: true, tr: true, bl: true, br: true,
                ml: true, mt: true, mr: true, mb: true, mtr: true,
            });
        }
        if (obj.controls) {
            const {
                scalingEqually,
                scalingX,
                scalingY,
                rotationWithSnapping,
                scaleCursorStyleHandler,
                rotationStyleHandler,
            } = controlsUtils;
            ['tl', 'tr', 'bl', 'br'].forEach((key) => {
                if (obj.controls[key]) {
                    obj.controls[key].actionHandler = scalingEqually;
                    obj.controls[key].cursorStyleHandler = scaleCursorStyleHandler;
                    obj.controls[key].actionName = 'scale';
                }
            });
            if (obj.controls.ml) {
                obj.controls.ml.actionHandler = scalingX;
                obj.controls.ml.cursorStyleHandler = scaleCursorStyleHandler;
                obj.controls.ml.actionName = 'scale';
            }
            if (obj.controls.mr) {
                obj.controls.mr.actionHandler = scalingX;
                obj.controls.mr.cursorStyleHandler = scaleCursorStyleHandler;
                obj.controls.mr.actionName = 'scale';
            }
            if (obj.controls.mt) {
                obj.controls.mt.actionHandler = scalingY;
                obj.controls.mt.cursorStyleHandler = scaleCursorStyleHandler;
                obj.controls.mt.actionName = 'scale';
            }
            if (obj.controls.mb) {
                obj.controls.mb.actionHandler = scalingY;
                obj.controls.mb.cursorStyleHandler = scaleCursorStyleHandler;
                obj.controls.mb.actionName = 'scale';
            }
            if (obj.controls.mtr) {
                obj.controls.mtr.actionHandler = rotationWithSnapping;
                obj.controls.mtr.cursorStyleHandler = rotationStyleHandler;
                obj.controls.mtr.offsetY = -28;
                obj.controls.mtr.withConnection = true;
                obj.controls.mtr.actionName = 'rotate';
            }
        }
        if (isFabricImage(obj)) {
            obj.set({
                objectCaching: false,
                lockScalingX: false,
                lockScalingY: false,
            });
        }
        obj.setCoords?.();
    }

    configureAllObjects() {
        this.canvas?.getObjects().forEach((obj) => this.configureSelectableObject(obj));
    }

    init(width, height, backgroundColor = '#ffffff') {
        if (this.canvas) {
            this.canvas.dispose();
        }
        this.designWidth = width;
        this.designHeight = height;
        this.viewportZoom = 1;
        this.canvas = new Canvas(this.canvasEl, {
            width,
            height,
            backgroundColor,
            preserveObjectStacking: true,
            selection: true,
            enableRetinaScaling: false,
            uniformScaling: true,
            uniScaleKey: 'shiftKey',
            stopContextMenu: true,
            controlsAboveOverlay: true,
            allowTouchScrolling: false,
            targetFindTolerance: 8,
        });
        installCornerHitFix(this.canvas, () => this.viewportZoom || 1);
        this.canvas.on('object:scaling', () => {
            this.canvas?.requestRenderAll();
        });
        this.canvas.on('object:rotating', () => {
            this.canvas?.requestRenderAll();
        });
        this.canvas.on('object:modified', (e) => {
            if (e.target) {
                this.clampObjectScale(e.target);
                e.target.setCoords?.();
            }
            this.emitChange();
        });
        this.canvas.on('object:added', (e) => {
            if (e.target) {
                this.configureSelectableObject(e.target);
            }
            this.emitChange();
        });
        this.canvas.on('object:removed', () => this.emitChange());
        this.canvas.on('selection:created', (e) => {
            (e.selected || []).forEach((obj) => this.configureSelectableObject(obj));
            this.notifyChange();
        });
        this.canvas.on('selection:updated', (e) => {
            (e.selected || []).forEach((obj) => this.configureSelectableObject(obj));
            this.notifyChange();
        });
        this.canvas.on('selection:cleared', () => this.notifyChange());
        this.canvas.on('object:moving', (e) => this.handleObjectMoving(e));
        this.canvas.on('text:changed', () => this.emitChange(false));
        this.canvas.on('mouse:down', (opt) => {
            this.canvas?.calcOffset();
            // Escala / rotação / mover: trava scroll da área do canvas e da sidebar
            if (opt?.target || opt?.transform) {
                const host = this.canvas?.wrapperEl || this.scaleWrapper || this.canvasEl;
                lockStudioScrollDuringDrag(host);
            }
        });
        this.canvas.on('mouse:up', () => {
            this.canvas?.calcOffset();
            unlockStudioScrollDuringDrag();
        });
        this.canvas.on('after:render', () => {
            this.drawGridOverlay();
            this.drawFormatGuidesOverlay();
        });
        this.applyViewportZoom(1);
        this.history = [];
        this.historyIndex = -1;
        return this.canvas;
    }

    notifyChange() {
        if (typeof this.onChange === 'function') {
            this.onChange();
        }
    }

    emitChange(recordHistory = true) {
        if (recordHistory && !this.historyPaused) {
            this.scheduleHistory();
        }
        this.notifyChange();
    }

    scheduleHistory() {
        clearTimeout(this.historyTimeout);
        this.historyTimeout = setTimeout(() => this.pushHistory(), 350);
    }

    pushHistory() {
        if (!this.canvas || this.historyPaused) {
            return;
        }
        const json = JSON.stringify(this.canvas.toJSON(FABRIC_JSON_PROPS));
        if (this.historyIndex >= 0 && this.history[this.historyIndex] === json) {
            return;
        }
        this.history = this.history.slice(0, this.historyIndex + 1);
        this.history.push(json);
        if (this.history.length > this.maxHistory) {
            this.history.shift();
        } else {
            this.historyIndex += 1;
        }
    }

    async undo() {
        if (this.historyIndex <= 0 || !this.canvas) {
            return false;
        }
        this.historyIndex -= 1;
        await this.restoreHistoryState(this.history[this.historyIndex]);
        return true;
    }

    async redo() {
        if (this.historyIndex >= this.history.length - 1 || !this.canvas) {
            return false;
        }
        this.historyIndex += 1;
        await this.restoreHistoryState(this.history[this.historyIndex]);
        return true;
    }

    canUndo() {
        return this.historyIndex > 0;
    }

    canRedo() {
        return this.historyIndex < this.history.length - 1;
    }

    async restoreHistoryState(jsonStr) {
        this.historyPaused = true;
        await this.canvas.loadFromJSON(JSON.parse(jsonStr));
        this.canvas.getObjects().forEach((obj) => {
            this.configureSelectableObject(obj);
            if (obj.criasysFilters && isFabricImage(obj)) {
                this.applyFiltersToObject(obj, obj.criasysFilters);
            }
        });
        this.canvas.requestRenderAll();
        this.historyPaused = false;
        this.notifyChange();
    }

    setGridOptions({ showGrid, snapToGrid, gridSize } = {}) {
        if (showGrid !== undefined) {
            this.showGrid = !!showGrid;
        }
        if (snapToGrid !== undefined) {
            this.snapToGrid = !!snapToGrid;
        }
        if (gridSize !== undefined) {
            this.gridSize = Math.max(5, Math.min(100, Number(gridSize) || 20));
        }
        this.canvas?.requestRenderAll();
    }

    handleObjectMoving(e) {
        if (!this.snapToGrid || !e.target) {
            return;
        }
        const g = this.gridSize;
        e.target.set({
            left: Math.round(e.target.left / g) * g,
            top: Math.round(e.target.top / g) * g,
        });
    }

    drawGridOverlay() {
        if (!this.canvas || !this.showGrid) {
            return;
        }
        const ctx = this.canvas.contextTop;
        if (!ctx) {
            return;
        }
        const w = this.designWidth;
        const h = this.designHeight;
        const g = this.gridSize;
        const zoom = this.canvas.getZoom() || 1;
        const vpt = this.canvas.viewportTransform || [1, 0, 0, 1, 0, 0];
        ctx.save();
        ctx.transform(vpt[0], vpt[1], vpt[2], vpt[3], vpt[4], vpt[5]);
        ctx.strokeStyle = 'rgba(139, 92, 246, 0.25)';
        ctx.lineWidth = 1 / zoom;
        for (let x = 0; x <= w; x += g) {
            ctx.beginPath();
            ctx.moveTo(x, 0);
            ctx.lineTo(x, h);
            ctx.stroke();
        }
        for (let y = 0; y <= h; y += g) {
            ctx.beginPath();
            ctx.moveTo(0, y);
            ctx.lineTo(w, y);
            ctx.stroke();
        }
        ctx.restore();
    }

    drawFormatGuidesOverlay() {
        if (!this.canvas || !this.showFormatGuides) {
            return;
        }
        const ctx = this.canvas.contextTop;
        if (!ctx) {
            return;
        }
        const w = this.designWidth || this.canvas.getWidth();
        const h = this.designHeight || this.canvas.getHeight();
        const zoom = this.canvas.getZoom() || 1;
        const vpt = this.canvas.viewportTransform || [1, 0, 0, 1, 0, 0];
        ctx.save();
        ctx.transform(vpt[0], vpt[1], vpt[2], vpt[3], vpt[4], vpt[5]);
        ctx.lineWidth = 2 / zoom;
        ctx.strokeStyle = '#8b5cf6';
        ctx.strokeRect(1, 1, w - 2, h - 2);
        const margin = Math.max(24, Math.round(Math.min(w, h) * 0.05));
        ctx.setLineDash([8 / zoom, 6 / zoom]);
        ctx.strokeStyle = 'rgba(251, 191, 36, 0.85)';
        ctx.strokeRect(margin, margin, w - margin * 2, h - margin * 2);
        ctx.setLineDash([]);
        if (h >= w * 1.4) {
            const safe = Math.min(250, Math.round(h * 0.12));
            ctx.strokeStyle = 'rgba(239, 68, 68, 0.55)';
            ctx.lineWidth = 1 / zoom;
            ctx.beginPath();
            ctx.moveTo(0, safe);
            ctx.lineTo(w, safe);
            ctx.moveTo(0, h - safe);
            ctx.lineTo(w, h - safe);
            ctx.stroke();
        }
        ctx.fillStyle = 'rgba(167, 139, 250, 0.9)';
        ctx.font = `${Math.max(10, Math.round(11 / zoom))}px sans-serif`;
        ctx.fillText(`${w} × ${h}px`, 8, h - 8);
        ctx.restore();
    }

    setFormatGuidesVisible(visible) {
        this.showFormatGuides = !!visible;
        this.canvas?.requestRenderAll();
    }

    /**
     * Zoom com viewport nativo do Fabric (sem CSS zoom/transform).
     * CSS zoom quebrava o hit-test das bolinhas — o mouse arrastava o objeto em vez de escalar.
     */
    applyViewportZoom(zoom) {
        if (!this.canvas) {
            return 1;
        }
        const z = Math.max(0.08, Math.min(4, Number(zoom) || 1));
        this.viewportZoom = z;
        const w = Math.max(1, Math.round(this.designWidth * z));
        const h = Math.max(1, Math.round(this.designHeight * z));

        this.canvas.setViewportTransform([z, 0, 0, z, 0, 0]);
        this.canvas.setDimensions({ width: w, height: h });

        if (this.scaleWrapper) {
            this.scaleWrapper.style.width = `${w}px`;
            this.scaleWrapper.style.height = `${h}px`;
            this.scaleWrapper.style.zoom = '';
            this.scaleWrapper.style.transform = 'none';
            this.scaleWrapper.style.transformOrigin = 'top left';
        }

        this.canvas.getObjects().forEach((obj) => {
            if (obj?.criasysGuide) {
                return;
            }
            obj.set({
                cornerSize: 12,
                touchCornerSize: 24,
                padding: 4,
                lockScalingX: false,
                lockScalingY: false,
            });
            obj.setCoords?.();
        });

        requestAnimationFrame(() => {
            this.canvas?.calcOffset();
            this.canvas?.requestRenderAll();
        });

        return z;
    }

    /** Export em coordenadas de design (zoom 100%). Reentrante. */
    async withDesignViewport(fn) {
        const canvas = this.canvas;
        if (!canvas) {
            return fn();
        }
        if (this._designViewportDepth > 0) {
            return fn();
        }
        this._designViewportDepth = 1;
        const prevZ = this.viewportZoom || 1;
        canvas.setViewportTransform([1, 0, 0, 1, 0, 0]);
        canvas.setDimensions({ width: this.designWidth, height: this.designHeight });
        try {
            return await fn();
        } finally {
            this._designViewportDepth = 0;
            this.applyViewportZoom(prevZ);
        }
    }

    getActiveObjectScalePercent() {
        const obj = this.canvas?.getActiveObject();
        if (!obj) {
            return 100;
        }
        const sx = Math.abs(obj.scaleX ?? 1);
        const sy = Math.abs(obj.scaleY ?? 1);
        return Math.round(((sx + sy) / 2) * 100);
    }

    clampObjectScale(obj) {
        if (!obj || obj.criasysGuide) {
            return false;
        }
        const sx = Math.abs(obj.scaleX ?? 1) || 1;
        const sy = Math.abs(obj.scaleY ?? 1) || 1;
        const ratio = sy / sx;
        const currentPercent = ((sx + sy) / 2) * 100;
        const clampedPercent = Math.max(
            OBJECT_SCALE_MIN_PERCENT,
            Math.min(OBJECT_SCALE_MAX_PERCENT, currentPercent)
        );
        if (Math.abs(clampedPercent - currentPercent) < 0.5) {
            return false;
        }
        const p = clampedPercent / 100;
        const signX = (obj.scaleX ?? 1) < 0 ? -1 : 1;
        const signY = (obj.scaleY ?? 1) < 0 ? -1 : 1;
        if (Math.abs(ratio - 1) < 0.02) {
            obj.set({ scaleX: signX * p, scaleY: signY * p });
        } else {
            obj.set({ scaleX: signX * p, scaleY: signY * p * ratio });
        }
        obj.setCoords();
        return true;
    }

    setActiveObjectScalePercent(percent) {
        const obj = this.canvas?.getActiveObject();
        if (!obj || obj.criasysGuide) {
            return;
        }
        const sx = Math.abs(obj.scaleX ?? 1) || 1;
        const sy = Math.abs(obj.scaleY ?? 1) || 1;
        const ratio = sy / sx;
        const p = Math.max(
            OBJECT_SCALE_MIN_PERCENT,
            Math.min(OBJECT_SCALE_MAX_PERCENT, Number(percent) || 100)
        ) / 100;
        const signX = (obj.scaleX ?? 1) < 0 ? -1 : 1;
        const signY = (obj.scaleY ?? 1) < 0 ? -1 : 1;
        if (Math.abs(ratio - 1) < 0.02) {
            obj.set({ scaleX: signX * p, scaleY: signY * p });
        } else {
            obj.set({ scaleX: signX * p, scaleY: signY * p * ratio });
        }
        obj.setCoords();
        this.canvas.requestRenderAll();
        this.emitChange();
    }

    nudgeActiveObjectScale(deltaPercent) {
        this.setActiveObjectScalePercent(this.getActiveObjectScalePercent() + deltaPercent);
    }

    normalizeObjectAngle(degrees) {
        let angle = Math.round(Number(degrees) || 0);
        while (angle < 0) {
            angle += 360;
        }
        while (angle >= 360) {
            angle -= 360;
        }

        return angle;
    }

    getActiveObjectAngle() {
        const obj = this.canvas?.getActiveObject();
        if (!obj || obj.criasysGuide) {
            return 0;
        }

        return this.normalizeObjectAngle(obj.angle ?? 0);
    }

    setActiveObjectAngle(degrees) {
        const obj = this.canvas?.getActiveObject();
        if (!obj || obj.criasysGuide) {
            return;
        }
        obj.set({ angle: this.normalizeObjectAngle(degrees) });
        obj.setCoords();
        this.canvas.requestRenderAll();
        this.emitChange();
    }

    nudgeActiveObjectAngle(deltaDegrees) {
        this.setActiveObjectAngle(this.getActiveObjectAngle() + (Number(deltaDegrees) || 0));
    }

    alignActiveObject(mode) {
        const obj = this.canvas?.getActiveObject();
        if (!obj) {
            return;
        }
        const w = this.designWidth || this.canvas.getWidth();
        const h = this.designHeight || this.canvas.getHeight();
        const bounds = obj.getBoundingRect();
        if (mode === 'left') {
            obj.set('left', obj.left - bounds.left);
        } else if (mode === 'center-h') {
            obj.set('left', obj.left + (w / 2 - (bounds.left + bounds.width / 2)));
        } else if (mode === 'right') {
            obj.set('left', obj.left + (w - (bounds.left + bounds.width)));
        } else if (mode === 'top') {
            obj.set('top', obj.top - bounds.top);
        } else if (mode === 'center-v') {
            obj.set('top', obj.top + (h / 2 - (bounds.top + bounds.height / 2)));
        } else if (mode === 'bottom') {
            obj.set('top', obj.top + (h - (bounds.top + bounds.height)));
        }
        obj.setCoords();
        this.canvas.requestRenderAll();
        this.emitChange();
    }

    setSize(width, height, backgroundColor) {
        if (!this.canvas) {
            return this.init(width, height, backgroundColor);
        }
        this.designWidth = width;
        this.designHeight = height;
        this.canvas.setDimensions({ width, height });
        if (backgroundColor) {
            this.canvas.backgroundColor = backgroundColor;
        }
        this.applyViewportZoom(this.viewportZoom || 1);
        this.canvas.requestRenderAll();
        this.emitChange();
    }

    setBackgroundColor(color, transparency = 0) {
        if (!this.canvas) {
            return;
        }
        const t = Math.max(0, Math.min(100, Number(transparency) || 0));
        this._bgColor = color || '#ffffff';
        this._bgTransparency = t;

        if (t >= 100) {
            this.canvas.backgroundColor = 'transparent';
        } else if (t <= 0) {
            this.canvas.backgroundColor = this._bgColor;
        } else {
            const { r, g, b } = parseHexColor(this._bgColor);
            const alpha = 1 - (t / 100);
            this.canvas.backgroundColor = `rgba(${r},${g},${b},${alpha})`;
        }
        this.canvas.requestRenderAll();
        this.emitChange();
    }

    applyBackgroundState(color, transparency) {
        this.setBackgroundColor(color, transparency);
    }

    async loadFromJSON(json) {
        if (!this.canvas || !json) {
            return;
        }
        const payload = typeof json === 'string' ? JSON.parse(json) : { ...json };
        const savedBg = payload.criasysBackground;
        if (savedBg && typeof savedBg === 'object') {
            delete payload.criasysBackground;
        }
        const savedUnderlay = payload.criasysUnderlay;
        if (savedUnderlay && typeof savedUnderlay === 'object') {
            delete payload.criasysUnderlay;
        }

        this.historyPaused = true;
        await this.canvas.loadFromJSON(payload);
        this.canvas.getObjects().forEach((obj) => {
            this.configureSelectableObject(obj);
            if (obj.criasysFilters && isFabricImage(obj)) {
                this.applyFiltersToObject(obj, obj.criasysFilters);
            }
        });
        this.repairEscapedNewlinesOnCanvas();

        if (savedBg?.color !== undefined) {
            const transparency = savedBg.transparency ?? savedBg.opacity;
            if (savedBg.opacity !== undefined && savedBg.transparency === undefined) {
                this.applyBackgroundState(savedBg.color, Math.max(0, 100 - Number(savedBg.opacity)));
            } else {
                this.applyBackgroundState(savedBg.color, transparency ?? 0);
            }
        } else {
            this.syncBackgroundUiState();
        }

        if (savedUnderlay && typeof savedUnderlay === 'object') {
            this.setUnderlayState(savedUnderlay.slideIndex, savedUnderlay.enabled);
        }

        this.canvas.requestRenderAll();
        this.historyPaused = false;
        this.pushHistory();
    }

    getFilterState(object) {
        return { ...DEFAULT_FILTER_STATE, ...(object?.criasysFilters || {}) };
    }

    applyFiltersToObject(object, state) {
        if (!isFabricImage(object)) {
            return;
        }

        const s = { ...DEFAULT_FILTER_STATE, ...state };
        object.criasysFilters = s;

        const strength = (v) => Math.max(0, Math.min(100, Number(v) || 0)) / 100;
        const list = [];

        const bri = (strength(s.brightness) - 0.5) * 0.6;
        if (Math.abs(bri) > 0.01) {
            list.push(new filters.Brightness({ brightness: bri }));
        }

        const con = (strength(s.contrast) - 0.5) * 0.8;
        if (Math.abs(con) > 0.01) {
            list.push(new filters.Contrast({ contrast: con }));
        }

        const sat = (strength(s.saturation) - 0.5) * 1.2;
        if (Math.abs(sat) > 0.01) {
            list.push(new filters.Saturation({ saturation: sat }));
        }

        const blurVal = strength(s.blur) * 0.35;
        if (blurVal > 0.01) {
            list.push(new filters.Blur({ blur: blurVal }));
        }

        if (strength(s.grayscale) > 0.05) {
            list.push(new filters.Grayscale());
        }

        object.filters = list;
        object.applyFilters();
        this.canvas?.requestRenderAll();
        this.emitChange();
    }

    clearFilters(object) {
        if (!isFabricImage(object)) {
            return;
        }
        object.criasysFilters = { ...DEFAULT_FILTER_STATE };
        object.filters = [];
        object.applyFilters();
        this.canvas?.requestRenderAll();
        this.emitChange();
    }

    toJSON() {
        const base = this.canvas?.toObject?.(FABRIC_JSON_PROPS) ?? this.canvas?.toJSON?.(FABRIC_JSON_PROPS) ?? null;
        if (!base) {
            return null;
        }

        return {
            ...base,
            criasysBackground: {
                color: this._bgColor || '#ffffff',
                transparency: this._bgTransparency ?? 0,
            },
            criasysUnderlay: this.getUnderlayState(),
        };
    }

    async renderExportCanvas(multiplier = 1, options = {}) {
        return this.withDesignViewport(async () => {
            const { underlayUrl = null, underlayIsVideo = false, omitCanvasBackground = false } = options;
            const w = this.designWidth;
            const h = this.designHeight;
            const out = document.createElement('canvas');
            out.width = w * multiplier;
            out.height = h * multiplier;
            const ctx = out.getContext('2d');
            ctx.clearRect(0, 0, out.width, out.height);

            if (underlayUrl) {
                try {
                    const media = await loadMediaDrawable(underlayUrl, underlayIsVideo);
                    if (media) {
                        drawCoverMedia(ctx, media, out.width, out.height);
                    }
                } catch {
                    /* slide sem mídia utilizável */
                }
            }

            const paint = this.getBackgroundPaint({ omitCanvasBackground });
            if (paint) {
                ctx.fillStyle = `rgba(${paint.r},${paint.g},${paint.b},${paint.a})`;
                ctx.fillRect(0, 0, out.width, out.height);
            }

            const savedBg = this.canvas.backgroundColor;
            this.canvas.backgroundColor = 'transparent';
            this.canvas.requestRenderAll();
            const objectsLayer = this.canvas.toCanvasElement(multiplier);
            this.canvas.backgroundColor = savedBg;
            this.canvas.requestRenderAll();
            ctx.drawImage(objectsLayer, 0, 0);

            return out;
        });
    }

    getLayers() {
        if (!this.canvas) {
            return [];
        }

        const active = this.canvas.getActiveObject();
        const selected = new Set();
        if (active) {
            // ActiveSelection: marca filhos. Grupo permanente: marca o próprio grupo.
            if (isFabricActiveSelection(active)) {
                const nested = typeof active.getObjects === 'function'
                    ? active.getObjects()
                    : (active._objects || []);
                nested.forEach((obj) => selected.add(obj));
            } else {
                selected.add(active);
            }
        }

        return [...this.canvas.getObjects()]
            .filter((obj) => !obj.criasysGuide && !obj.criasysCropGuide)
            .reverse()
            .map((obj, idx) => ({
                id: obj.criasysId || `${obj.type || 'layer'}_${idx}`,
                name: obj.name || (isFabricGroup(obj) ? 'Grupo' : (obj.type || `Camada ${idx + 1}`)),
                type: obj.type,
                visible: obj.visible !== false,
                locked: obj.selectable === false,
                active: selected.has(obj),
                object: obj,
            }));
    }

    selectLayer(object) {
        if (!this.canvas || !object) {
            return;
        }
        this.canvas.setActiveObject(object);
        this.canvas.requestRenderAll();
    }

    toggleLayerVisibility(object) {
        if (!object) {
            return;
        }
        object.visible = !object.visible;
        this.canvas.requestRenderAll();
        this.emitChange();
    }

    toggleLayerLock(object) {
        if (!object) {
            return;
        }
        const locked = object.selectable !== false;
        object.selectable = !locked;
        object.evented = !locked;
        this.canvas.requestRenderAll();
        this.emitChange();
    }

    moveLayer(object, direction) {
        if (!this.canvas || !object) {
            return;
        }
        if (direction === 'up') {
            this.canvas.bringObjectForward(object);
        } else if (direction === 'down') {
            this.canvas.sendObjectBackwards(object);
        } else if (direction === 'top') {
            this.canvas.bringObjectToFront(object);
        } else if (direction === 'bottom') {
            this.canvas.sendObjectToBack(object);
        }
        this.canvas.requestRenderAll();
        this.emitChange();
    }

    removeLayer(object) {
        if (!this.canvas || !object) {
            return;
        }
        this.canvas.remove(object);
        this.canvas.requestRenderAll();
        this.emitChange();
    }

    async duplicateObject(object) {
        if (!this.canvas || !object) {
            return null;
        }
        const cloned = await object.clone();
        cloned.set({
            left: (object.left || 0) + 24,
            top: (object.top || 0) + 24,
            criasysId: (object.type || 'obj') + '_' + Date.now(),
            name: (object.name || object.type || 'Camada') + ' cópia',
        });
        this.configureSelectableObject(cloned);
        this.canvas.add(cloned);
        this.canvas.setActiveObject(cloned);
        this.canvas.requestRenderAll();
        this.emitChange();

        return cloned;
    }

    /** Objetos editáveis (ignora guias). */
    getEditableObjects() {
        return (this.canvas?.getObjects() || []).filter(
            (obj) => obj && !obj.criasysGuide && !obj.criasysCropGuide
        );
    }

    selectAllObjects() {
        if (!this.canvas) {
            return null;
        }
        const list = this.getEditableObjects().filter((obj) => obj.selectable !== false);
        if (!list.length) {
            this.canvas.discardActiveObject();
            this.canvas.requestRenderAll();
            return null;
        }
        if (list.length === 1) {
            this.canvas.setActiveObject(list[0]);
        } else {
            const selection = new ActiveSelection(list, { canvas: this.canvas });
            this.canvas.setActiveObject(selection);
        }
        this.canvas.requestRenderAll();
        this.notifyChange();

        return this.canvas.getActiveObject();
    }

    discardSelection() {
        if (!this.canvas) {
            return;
        }
        this.canvas.discardActiveObject();
        this.canvas.requestRenderAll();
        this.notifyChange();
    }

    nudgeActiveObjects(dx, dy) {
        if (!this.canvas) {
            return false;
        }
        const active = this.canvas.getActiveObject();
        if (!active || active.criasysGuide || active.criasysCropGuide) {
            return false;
        }
        active.set({
            left: (active.left || 0) + dx,
            top: (active.top || 0) + dy,
        });
        active.setCoords?.();
        this.canvas.requestRenderAll();
        this.emitChange();

        return true;
    }

    /**
     * Modo de ferramenta do canvas (estilo Photoshop).
     * select | marquee | hand | text
     */
    applyInteractionTool(tool = 'select') {
        if (!this.canvas) {
            return;
        }
        const mode = ['select', 'marquee', 'hand', 'text'].includes(tool) ? tool : 'select';
        this._interactionTool = mode;

        if (mode === 'hand') {
            this.canvas.selection = false;
            this.canvas.skipTargetFind = true;
            this.canvas.defaultCursor = 'grab';
            this.canvas.hoverCursor = 'grab';
            this.canvas.getObjects().forEach((obj) => {
                if (obj.criasysGuide || obj.criasysCropGuide) {
                    return;
                }
                obj.evented = false;
            });
        } else {
            this.canvas.selection = true;
            this.canvas.skipTargetFind = false;
            this.canvas.defaultCursor = mode === 'marquee' ? 'crosshair' : 'default';
            this.canvas.hoverCursor = 'move';
            this.canvas.getObjects().forEach((obj) => {
                if (obj.criasysGuide || obj.criasysCropGuide) {
                    return;
                }
                obj.evented = true;
            });
            // Marquee: só objetos totalmente dentro do retângulo
            if ('selectionFullyContained' in this.canvas) {
                this.canvas.selectionFullyContained = mode === 'marquee';
            }
        }
        this.canvas.requestRenderAll();
    }

    async serializeActiveObjectsForClipboard() {
        if (!this.canvas) {
            return [];
        }
        const active = this.canvas.getActiveObject();
        if (!active) {
            return [];
        }
        let targets = [];
        if (isFabricActiveSelection(active)) {
            targets = active.getObjects?.() || [];
        } else {
            targets = [active];
        }
        const out = [];
        for (const obj of targets) {
            if (!obj || obj.criasysGuide || obj.criasysCropGuide) {
                continue;
            }
            const json = obj.toObject?.(FABRIC_JSON_PROPS);
            if (json) {
                out.push(json);
            }
        }

        return out;
    }

    async pasteObjectsFromClipboard(items = [], offset = 24) {
        if (!this.canvas || !Array.isArray(items) || !items.length) {
            return [];
        }
        const created = [];
        for (const raw of items) {
            try {
                const cloneJson = {
                    ...raw,
                    left: (Number(raw.left) || 0) + offset,
                    top: (Number(raw.top) || 0) + offset,
                    criasysId: `${raw.type || 'obj'}_${Date.now()}_${Math.random().toString(36).slice(2, 7)}`,
                    name: raw.name ? `${raw.name} cópia` : undefined,
                };
                const enlivened = await util.enlivenObjects([cloneJson]);
                const list = Array.isArray(enlivened) ? enlivened : [enlivened];
                const obj = list[0];
                if (!obj || typeof obj.set !== 'function') {
                    continue;
                }
                this.configureSelectableObject(obj);
                this.canvas.add(obj);
                created.push(obj);
            } catch (_) {
                // ignora item inválido
            }
        }
        if (created.length === 1) {
            this.canvas.setActiveObject(created[0]);
        } else if (created.length > 1) {
            const selection = new ActiveSelection(created, { canvas: this.canvas });
            this.canvas.setActiveObject(selection);
        }
        this.canvas.requestRenderAll();
        this.emitChange();

        return created;
    }

    /**
     * Objetos candidatas ao agrupamento (ActiveSelection, getActiveObjects, ou lista explícita).
     */
    resolveObjectsForGrouping(preferred = null) {
        if (!this.canvas) {
            return [];
        }
        const uniq = [];
        const seen = new Set();
        const push = (obj) => {
            if (!obj || obj.criasysGuide || obj.criasysCropGuide || seen.has(obj)) {
                return;
            }
            // Não agrupar o próprio ActiveSelection / Group wrapper como membro
            if (isFabricActiveSelection(obj)) {
                (obj.getObjects?.() || []).forEach(push);
                return;
            }
            seen.add(obj);
            uniq.push(obj);
        };

        if (Array.isArray(preferred) && preferred.length) {
            preferred.forEach(push);
        } else {
            const activeList = typeof this.canvas.getActiveObjects === 'function'
                ? this.canvas.getActiveObjects()
                : [];
            if (activeList.length) {
                activeList.forEach(push);
            } else {
                const active = this.canvas.getActiveObject();
                if (isFabricActiveSelection(active)) {
                    (active.getObjects?.() || []).forEach(push);
                } else if (active) {
                    push(active);
                }
            }
        }

        return uniq;
    }

    /** AABB da seleção no plano do canvas (antes de mutar). */
    getObjectsSceneBounds(objects) {
        let minX = Infinity;
        let minY = Infinity;
        let maxX = -Infinity;
        let maxY = -Infinity;
        let ok = false;
        (objects || []).forEach((obj) => {
            try {
                obj.setCoords?.();
                const r = obj.getBoundingRect();
                if (!r) {
                    return;
                }
                minX = Math.min(minX, r.left);
                minY = Math.min(minY, r.top);
                maxX = Math.max(maxX, r.left + r.width);
                maxY = Math.max(maxY, r.top + r.height);
                ok = true;
            } catch {
                /* ignore */
            }
        });
        if (!ok) {
            return null;
        }

        return {
            left: minX,
            top: minY,
            width: Math.max(1, maxX - minX),
            height: Math.max(1, maxY - minY),
            centerX: (minX + maxX) / 2,
            centerY: (minY + maxY) / 2,
        };
    }

    /**
     * Sai da ActiveSelection aplicando a matriz correta (objetos ficam no canvas).
     * NÃO remova do canvas antes de new Group — isso zera transform e “some” a cor.
     */
    releaseActiveSelectionToCanvas() {
        if (!this.canvas) {
            return;
        }
        const active = this.canvas.getActiveObject();
        if (isFabricActiveSelection(active) && typeof active.removeAll === 'function') {
            try {
                active.removeAll();
            } catch {
                /* ignore */
            }
        }
        try {
            this.canvas.discardActiveObject();
        } catch {
            /* ignore */
        }
    }

    /**
     * Puxa o grupo de volta se o centro tiver voado para fora da prancheta.
     */
    clampObjectCenterToArtboard(obj, padding = 24) {
        if (!this.canvas || !obj) {
            return;
        }
        const w = this.designWidth || this.canvas.getWidth() || 0;
        const h = this.designHeight || this.canvas.getHeight() || 0;
        if (w < 8 || h < 8) {
            return;
        }
        obj.setCoords?.();
        const c = obj.getCenterPoint?.() || { x: obj.left || 0, y: obj.top || 0 };
        if (!Number.isFinite(c.x) || !Number.isFinite(c.y)) {
            return;
        }
        // Só move se estiver claramente fora (evita “corrigir” e sumir com o grupo)
        const outside = c.x < -50 || c.y < -50 || c.x > w + 50 || c.y > h + 50;
        if (!outside) {
            return;
        }
        const nx = Math.min(w - padding, Math.max(padding, c.x));
        const ny = Math.min(h - padding, Math.max(padding, c.y));
        if (typeof obj.setPositionByOrigin === 'function') {
            obj.setPositionByOrigin({ x: nx, y: ny }, 'center', 'center');
        } else {
            obj.set({
                left: (obj.left || 0) + (nx - c.x),
                top: (obj.top || 0) + (ny - c.y),
            });
        }
        obj.setCoords?.();
    }

    /**
     * Agrupa N objetos preservando posição e cores (Fabric 6/7).
     * @param {FabricObject[]|null} preferred
     * @returns {Group|null}
     */
    groupObjects(preferred = null) {
        if (!this.canvas) {
            return null;
        }
        const objects = this.resolveObjectsForGrouping(preferred);
        if (objects.length < 2) {
            return null;
        }

        // Snapshot visual (se algo no Group constructor bagunçar)
        const paintSnap = objects.map((obj) => ({
            obj,
            opacity: obj.opacity,
            fill: obj.fill,
            stroke: obj.stroke,
            strokeWidth: obj.strokeWidth,
            visible: obj.visible,
        }));

        this.historyPaused = true;
        try {
            // 1) ActiveSelection → plano do canvas (matriz correta)
            this.releaseActiveSelectionToCanvas();

            // 2) Garantir que cada membro está no canvas (Group.enterGroup precisa disso)
            objects.forEach((obj) => {
                if (!obj) {
                    return;
                }
                // Não chamar group.remove manualmente aqui — removeAll da AS já saiu.
                if (!this.canvas.getObjects().includes(obj)) {
                    this.canvas.add(obj);
                }
                obj.setCoords?.();
            });

            // 3) new Group ENQUANTO ainda estão no canvas — NÃO canvas.remove antes
            const group = new Group([...objects], {
                name: 'Grupo',
                criasysId: 'group_' + Date.now(),
                subTargetCheck: false,
                interactive: false,
                objectCaching: false,
            });

            // enterGroup já tirou os filhos do canvas; só adiciona o grupo
            if (!this.canvas.getObjects().includes(group)) {
                this.canvas.add(group);
            }

            // Restaura paint/opacity se algum filho ficou transparente/sujo
            paintSnap.forEach(({ obj, opacity, fill, stroke, strokeWidth, visible }) => {
                if (!obj) {
                    return;
                }
                const patch = {};
                if (obj.opacity !== opacity && opacity != null) {
                    patch.opacity = opacity;
                }
                if (fill !== undefined && obj.fill !== fill) {
                    patch.fill = fill;
                }
                if (stroke !== undefined && obj.stroke !== stroke) {
                    patch.stroke = stroke;
                }
                if (strokeWidth !== undefined && obj.strokeWidth !== strokeWidth) {
                    patch.strokeWidth = strokeWidth;
                }
                if (visible === false) {
                    patch.visible = false;
                } else if (obj.visible === false && visible !== false) {
                    patch.visible = true;
                }
                if (Object.keys(patch).length) {
                    obj.set(patch);
                    obj.set('dirty', true);
                }
            });

            group.set({
                objectCaching: false,
                dirty: true,
                visible: true,
                opacity: 1,
            });
            this.configureSelectableObject(group);
            // Não marcar grupo de usuário como recolorível (evita painel pintar tudo de branco)
            group.set('criasysRecolorable', false);
            group.setCoords?.();

            this.clampObjectCenterToArtboard(group);
            this.canvas.setActiveObject(group);
            this.canvas.requestRenderAll();
        } finally {
            this.historyPaused = false;
        }

        this.emitChange();
        return this.canvas.getActiveObject();
    }

    /**
     * Transforma ActiveSelection (2+ camadas) num Group permanente.
     * @returns {Group|null}
     */
    groupActiveSelection() {
        return this.groupObjects(null);
    }

    /**
     * Desfaz um Group → objetos livres + ActiveSelection (posição preservada).
     * @returns {ActiveSelection|null}
     */
    ungroupActiveObject(preferredGroup = null) {
        if (!this.canvas) {
            return null;
        }
        const active = preferredGroup && isFabricGroup(preferredGroup)
            ? preferredGroup
            : this.canvas.getActiveObject();
        if (!isFabricGroup(active)) {
            return null;
        }

        const before = this.getObjectsSceneBounds([active]);
        this.historyPaused = true;
        let items = [];
        try {
            items = typeof active.removeAll === 'function'
                ? active.removeAll()
                : [];
            try {
                this.canvas.remove(active);
            } catch {
                /* ignore */
            }
            if (!items.length) {
                this.canvas.requestRenderAll();
                return null;
            }
            items.forEach((obj) => {
                this.configureSelectableObject(obj);
                if (!obj.criasysId) {
                    obj.criasysId = (obj.type || 'obj') + '_' + Date.now() + '_' + Math.random().toString(36).slice(2, 6);
                }
                if (!this.canvas.getObjects().includes(obj)) {
                    this.canvas.add(obj);
                }
                obj.setCoords?.();
            });

            const after = this.getObjectsSceneBounds(items);
            if (before && after) {
                const dx = before.centerX - after.centerX;
                const dy = before.centerY - after.centerY;
                if ((Math.abs(dx) > 1 || Math.abs(dy) > 1)
                    && Number.isFinite(dx) && Number.isFinite(dy)
                    && Math.abs(dx) < (this.designWidth || 4000)
                    && Math.abs(dy) < (this.designHeight || 4000)) {
                    items.forEach((obj) => {
                        obj.set({
                            left: (obj.left || 0) + dx,
                            top: (obj.top || 0) + dy,
                        });
                        obj.setCoords?.();
                    });
                }
            }

            const selection = new ActiveSelection(items, { canvas: this.canvas });
            this.canvas.setActiveObject(selection);
            this.canvas.requestRenderAll();
        } finally {
            this.historyPaused = false;
        }

        this.emitChange();
        return this.canvas.getActiveObject();
    }

    canGroupActiveSelection(preferred = null) {
        return this.resolveObjectsForGrouping(preferred).length >= 2;
    }

    canUngroupActiveObject() {
        return isFabricGroup(this.canvas?.getActiveObject());
    }

    /**
     * Monta ActiveSelection a partir de uma lista (ex.: Ctrl+clique nas camadas).
     */
    setMultiSelection(objects) {
        if (!this.canvas) {
            return null;
        }
        const list = this.resolveObjectsForGrouping(objects);
        this.canvas.discardActiveObject();
        if (!list.length) {
            this.canvas.requestRenderAll();
            this.notifyChange();

            return null;
        }
        if (list.length === 1) {
            this.canvas.setActiveObject(list[0]);
            this.canvas.requestRenderAll();
            this.notifyChange();

            return list[0];
        }
        list.forEach((obj) => this.configureSelectableObject(obj));
        const selection = new ActiveSelection(list, { canvas: this.canvas });
        this.canvas.setActiveObject(selection);
        this.canvas.requestRenderAll();
        this.notifyChange();

        return selection;
    }

    flipObject(object, axis = 'x') {
        if (!object) {
            return;
        }
        if (axis === 'y') {
            object.set('flipY', !object.flipY);
        } else {
            object.set('flipX', !object.flipX);
        }
        object.setCoords();
        this.canvas.requestRenderAll();
        this.emitChange();
    }

    /**
     * Inicia modo de recorte: retângulo sobre a imagem selecionada.
     */
    startCropMode(image) {
        if (!this.canvas || !isFabricImage(image)) {
            return null;
        }
        this.cancelCropMode(false);

        const bounds = image.getBoundingRect();
        const padX = Math.max(8, bounds.width * 0.1);
        const padY = Math.max(8, bounds.height * 0.1);
        const rect = new Rect({
            left: bounds.left + padX,
            top: bounds.top + padY,
            width: Math.max(40, bounds.width - padX * 2),
            height: Math.max(40, bounds.height - padY * 2),
            fill: 'rgba(124, 58, 237, 0.15)',
            stroke: '#a78bfa',
            strokeWidth: 2,
            strokeDashArray: [6, 4],
            cornerColor: '#a78bfa',
            borderColor: '#c4b5fd',
            transparentCorners: false,
            name: 'Área de recorte',
            criasysId: 'crop_rect_' + Date.now(),
            criasysCropGuide: true,
            excludeFromExport: true,
        });
        this.configureSelectableObject(rect);
        this.canvas.add(rect);
        this.canvas.setActiveObject(rect);
        this.canvas.requestRenderAll();
        this._cropTarget = image;
        this._cropRect = rect;

        return rect;
    }

    cancelCropMode(emit = true) {
        if (this._cropRect && this.canvas) {
            this.canvas.remove(this._cropRect);
        }
        this._cropRect = null;
        this._cropTarget = null;
        if (this.canvas) {
            this.canvas.requestRenderAll();
            if (emit) {
                this.emitChange();
            }
        }
    }

    /**
     * Aplica o recorte: gera PNG só da região da imagem e substitui o objeto.
     */
    async applyCropMode() {
        const image = this._cropTarget;
        const guide = this._cropRect;
        if (!this.canvas || !isFabricImage(image) || !guide) {
            return false;
        }

        const imgBounds = image.getBoundingRect(true);
        const cropBounds = guide.getBoundingRect(true);

        const left = Math.max(imgBounds.left, cropBounds.left);
        const top = Math.max(imgBounds.top, cropBounds.top);
        const right = Math.min(imgBounds.left + imgBounds.width, cropBounds.left + cropBounds.width);
        const bottom = Math.min(imgBounds.top + imgBounds.height, cropBounds.top + cropBounds.height);
        const width = Math.max(1, Math.floor(right - left));
        const height = Math.max(1, Math.floor(bottom - top));

        if (width < 4 || height < 4) {
            this.cancelCropMode();
            return false;
        }

        const el = image.getElement?.() || image._element;
        if (!el) {
            this.cancelCropMode();
            return false;
        }

        const naturalW = el.naturalWidth || el.width || image.width || 1;
        const naturalH = el.naturalHeight || el.height || image.height || 1;
        const relLeft = (left - imgBounds.left) / Math.max(1, imgBounds.width);
        const relTop = (top - imgBounds.top) / Math.max(1, imgBounds.height);
        const relW = width / Math.max(1, imgBounds.width);
        const relH = height / Math.max(1, imgBounds.height);

        const sx = Math.max(0, Math.floor(relLeft * naturalW));
        const sy = Math.max(0, Math.floor(relTop * naturalH));
        const sw = Math.max(1, Math.min(Math.floor(relW * naturalW), naturalW - sx));
        const sh = Math.max(1, Math.min(Math.floor(relH * naturalH), naturalH - sy));

        const off = document.createElement('canvas');
        off.width = sw;
        off.height = sh;
        const ctx = off.getContext('2d');
        ctx.drawImage(el, sx, sy, sw, sh, 0, 0, sw, sh);
        const blob = await canvasToBlob(off, 'image/png', 0.95);
        const url = URL.createObjectURL(blob);

        const keepName = image.name || 'Imagem';
        this.cancelCropMode(false);
        this.canvas.remove(image);

        const cropped = await FabricImage.fromURL(url, { crossOrigin: 'anonymous' });
        cropped.set({
            left: left + width / 2,
            top: top + height / 2,
            originX: 'center',
            originY: 'center',
            scaleX: width / Math.max(1, cropped.width || sw),
            scaleY: height / Math.max(1, cropped.height || sh),
            name: keepName + ' recortada',
            criasysId: 'img_crop_' + Date.now(),
        });
        this.configureSelectableObject(cropped);
        this.canvas.add(cropped);
        this.canvas.setActiveObject(cropped);
        this.canvas.requestRenderAll();
        this.emitChange();

        return true;
    }

    addText(text = 'Seu texto', options = {}) {
        const boxW = Math.max(160, Math.round(this.designWidth * 0.55));
        const textObj = createMultilineTextObject(text, {
            left: (this.designWidth / 2) - (boxW / 2),
            top: (this.designHeight / 2) - 30,
            width: boxW,
            fontFamily: options.fontFamily || 'Impact, Arial Black, sans-serif',
            fontSize: options.fontSize || 64,
            fill: normalizeColorInput(options.fill, '#ffffff'),
            fontWeight: options.fontWeight ?? 'bold',
            fontStyle: options.fontStyle || 'normal',
            underline: !!options.underline,
            linethrough: !!options.linethrough,
            stroke: (options.strokeWidth || 0) > 0 ? normalizeColorInput(options.stroke, '#000000') : null,
            strokeWidth: options.strokeWidth || 0,
            textAlign: options.textAlign || 'left',
            lineHeight: options.lineHeight || 1.16,
            charSpacing: options.charSpacing || 0,
            name: options.name || 'Texto',
            criasysId: options.criasysId || ('text_' + Date.now()),
            criasysFontSlug: options.fontSlug || null,
            criasysIconGlyph: options.iconGlyph || null,
        });
        if (options.shadow) {
            textObj.set('shadow', new Shadow({
                color: options.shadowColor || '#000000',
                blur: options.shadowBlur ?? 8,
                offsetX: 2,
                offsetY: 2,
            }));
        }
        this.configureSelectableObject(textObj);
        this.canvas.add(textObj);
        this.canvas.setActiveObject(textObj);
        this.canvas.requestRenderAll();
        this.emitChange();
        return textObj;
    }

    /**
     * Corrige \n literal em qualquer texto já no canvas (rascunho antigo / templates).
     */
    repairEscapedNewlinesOnCanvas() {
        if (!this.canvas) {
            return false;
        }
        let changed = false;
        this.canvas.getObjects().forEach((obj) => {
            if (!isFabricText(obj) || typeof obj.text !== 'string') {
                return;
            }
            const next = normalizeMultilineText(obj.text);
            if (next === obj.text) {
                return;
            }
            obj.set('text', next);
            if (typeof obj.initDimensions === 'function') {
                obj.initDimensions();
            }
            obj.set('dirty', true);
            changed = true;
        });
        if (changed) {
            this.canvas.requestRenderAll();
        }
        return changed;
    }

    getTextStyleFromObject(obj) {
        if (!isFabricText(obj)) {
            return null;
        }
        const rawContent = obj.text || '';
        const content = normalizeMultilineText(rawContent);
        if (content !== rawContent) {
            obj.set('text', content);
            if (typeof obj.initDimensions === 'function') {
                obj.initDimensions();
            }
            obj.set('dirty', true);
            this.canvas?.requestRenderAll?.();
        }
        const fw = obj.fontWeight;
        const bold = fw === 'bold' || fw === 700 || fw === '700' || Number(fw) >= 600;
        return {
            fontSlug: obj.criasysFontSlug || 'bebas_neue',
            content,
            fontSize: obj.fontSize || 48,
            fill: normalizeColorInput(obj.fill, '#ffffff'),
            stroke: normalizeColorInput(obj.stroke, '#000000'),
            strokeWidth: Math.max(0, parseFloat(obj.strokeWidth) || 0),
            bold,
            italic: obj.fontStyle === 'italic',
            underline: !!obj.underline,
            linethrough: !!obj.linethrough,
            align: obj.textAlign || 'left',
            lineHeight: obj.lineHeight || 1.16,
            charSpacing: obj.charSpacing || 0,
            shadow: !!obj.shadow,
            shadowColor: obj.shadow?.color || '#000000',
            shadowBlur: obj.shadow?.blur ?? 8,
        };
    }

    async applyTextStyle(object, style, fontMap = {}) {
        if (!isFabricText(object) || !style) {
            return;
        }
        const fontMeta = fontMap[style.fontSlug]
            || findFontBySlug(Object.values(fontMap), style.fontSlug)
            || null;
        const isIcon = fontMeta?.source === 'icon';
        const fontStyle = !isIcon && style.italic ? 'italic' : 'normal';
        const fontWeight = fontMeta
            ? resolveFontWeight(fontMeta, style.bold)
            : (style.bold ? 'bold' : 'normal');

        if (fontMeta) {
            await ensureFontLoaded(fontMeta, { bold: style.bold, italic: style.italic });
            object.set(buildTextStyleFromFont(fontMeta, { bold: style.bold, italic: style.italic }));
            object.criasysFontSlug = style.fontSlug;
        }

        this.applyTextPaint(object, style);

        object.set({
            fontStyle,
            fontWeight,
            fontSize: style.fontSize,
            textAlign: style.align || 'left',
            lineHeight: style.lineHeight || 1.16,
            charSpacing: style.charSpacing || 0,
            underline: !!style.underline,
            linethrough: !!style.linethrough,
        });
        if (style.content != null && style.content !== object.text) {
            object.set('text', normalizeMultilineText(String(style.content)));
        }
        if (style.shadow) {
            object.set('shadow', new Shadow({
                color: style.shadowColor || '#000000',
                blur: style.shadowBlur ?? 8,
                offsetX: 2,
                offsetY: 2,
            }));
        } else {
            object.set('shadow', null);
        }
        if (typeof object.initDimensions === 'function') {
            object.initDimensions();
        }
        object.set('dirty', true);
        this.canvas.requestRenderAll();
        this.emitChange();
    }

    addRect(color = '#ef4444', opacity = 80) {
        const rect = new Rect({
            originX: 'center',
            originY: 'center',
            left: this.designWidth / 2,
            top: this.designHeight / 2,
            width: this.designWidth * 0.7,
            height: this.designHeight * 0.25,
            fill: color,
            opacity: opacity / 100,
            name: 'Retângulo',
            criasysId: 'rect_' + Date.now(),
        });
        this.configureSelectableObject(rect);
        this.canvas.add(rect);
        this.canvas.setActiveObject(rect);
        this.canvas.requestRenderAll();
        this.emitChange();
    }

    addCircle(color = '#3b82f6', opacity = 80) {
        const size = Math.min(this.designWidth, this.designHeight) * 0.25;
        const circle = new Circle({
            originX: 'center',
            originY: 'center',
            left: this.designWidth / 2,
            top: this.designHeight / 2,
            radius: size / 2,
            fill: color,
            opacity: opacity / 100,
            name: 'Círculo',
            criasysId: 'circle_' + Date.now(),
        });
        this.configureSelectableObject(circle);
        this.canvas.add(circle);
        this.canvas.setActiveObject(circle);
        this.canvas.requestRenderAll();
        this.emitChange();
    }

    async addSvgIconFromSpec(spec) {
        let url = spec.icon_url || spec.url;
        if (!url || !this.canvas) {
            return;
        }
        if (url.startsWith('/')) {
            url = `${window.location.origin}${url}`;
        }
        const { objects, options } = await loadSVGFromURL(url);
        const grouped = util.groupSVGElements(objects, options);
        const color = spec.fill || '#0d9488';
        const applyFill = (obj) => {
            if (!obj) return;
            if (obj._objects?.length) {
                obj._objects.forEach(applyFill);
                return;
            }
            paintLeafObject(obj, { fill: color, forceFill: true });
        };
        applyFill(grouped);
        const size = spec.size || 100;
        const base = Math.max(grouped.width || 16, grouped.height || 16, 1);
        const scale = size / base;
        grouped.set({
            left: (this.designWidth - size) / 2,
            top: (this.designHeight - size) / 2,
            scaleX: scale,
            scaleY: scale,
            name: spec.name || 'Ícone',
            criasysId: 'icon_' + Date.now(),
            criasysSvgIcon: true,
            criasysRecolorable: true,
            criasysLastFill: color,
        });
        this.canvas.add(grouped);
        this.configureSelectableObject(grouped);
        this.canvas.setActiveObject(grouped);
        this.canvas.requestRenderAll();
        this.emitChange();
    }

    async addElementFromCatalog(spec) {
        if (!spec || !this.canvas) {
            return;
        }

        const type = spec.type || spec.kind;

        if (type === 'svg_icon') {
            return this.addSvgIconFromSpec(spec);
        }

        if (type === 'emoji' || type === 'sticker') {
            const char = spec.char || spec.icon || '★';
            const isColorEmoji = /[\u{1F300}-\u{1FAFF}\u2600-\u27BF]/u.test(char);
            this.addText(char, {
                fontSize: spec.fontSize || 120,
                fill: spec.fill || '#ffffff',
                fontFamily: isColorEmoji ? EMOJI_FONT_STACK : 'Impact, Arial Black, sans-serif',
                fontWeight: isColorEmoji ? 'normal' : 'bold',
                textAlign: 'center',
                name: spec.name || 'Emoji',
                fontSlug: null,
                charSpacing: spec.charSpacing || 0,
            });
            const active = this.getActiveObject();
            if (active && isFabricText(active)) {
                centerObjectOnCanvas(active, this.designWidth, this.designHeight);
                this.canvas.requestRenderAll();
            }
            return;
        }

        const shape = createShapeFromSpec(spec, this.designWidth, this.designHeight);
        if (shape) {
            addCanvasObject(
                this.canvas,
                shape,
                this.designWidth,
                this.designHeight,
                (obj) => this.configureSelectableObject(obj),
            );
            this.emitChange();
            return;
        }

        this.emitChange();
    }

    async applyTemplate(template, fontMap = {}) {
        if (!this.canvas || !template) {
            return;
        }

        this.historyPaused = true;
        try {
            this.cancelCropMode?.(false);
            this.canvas.discardActiveObject();
            this.canvas.clear();

            const width = this.designWidth || this.canvas.getWidth();
            const height = this.designHeight || this.canvas.getHeight();
            const bg = template.background || {};
            this.setBackgroundColor(
                bg.color || '#ffffff',
                bg.transparency ?? Math.max(0, 100 - (bg.opacity ?? 100))
            );

            const fontList = Object.values(fontMap || {});
            normalizeTemplateTextFields(template);
            const specs = Array.isArray(template.objects) ? template.objects : [];

            // 1) Formas de fundo primeiro (faixas, caixas, círculos)
            // 2) Textos por cima — cliente edita o que importa sem caçar camada
            const shapeSpecs = [];
            const textSpecs = [];
            specs.forEach((spec, idx) => {
                const kind = spec?.kind || spec?.type;
                if (['rect', 'circle', 'ellipse', 'line'].includes(kind)) {
                    shapeSpecs.push({ spec, idx });
                } else if (kind === 'text') {
                    textSpecs.push({ spec, idx });
                }
            });

            for (const { spec, idx } of shapeSpecs) {
                await this.addTemplateShapeObject(spec, idx, width, height);
            }
            for (const { spec, idx } of textSpecs) {
                await this.addTemplateTextObject(spec, idx, width, height, fontMap, fontList);
            }

            this.repairEscapedNewlinesOnCanvas();
            this.organizeCanvasForClient(template);
            this.canvas.requestRenderAll();
        } finally {
            this.historyPaused = false;
            this.pushHistory();
            this.notifyChange();
        }
    }

    resolveTemplateFill(spec, objW, objH) {
        if (spec.gradient && Array.isArray(spec.gradient.stops) && spec.gradient.stops.length >= 2) {
            const g = spec.gradient;
            const vertical = (g.direction || 'vertical') === 'vertical';

            return new Gradient({
                type: 'linear',
                gradientUnits: 'pixels',
                coords: vertical
                    ? { x1: 0, y1: 0, x2: 0, y2: Math.max(1, objH) }
                    : { x1: 0, y1: 0, x2: Math.max(1, objW), y2: 0 },
                colorStops: g.stops.map((stop) => ({
                    offset: Number(stop.offset ?? 0),
                    color: stop.color || '#000000',
                })),
            });
        }

        return spec.fill || '#ef4444';
    }

    applyTemplateObjectShadow(obj, shadowSpec) {
        if (!shadowSpec || !obj) {
            return;
        }
        obj.set('shadow', new Shadow({
            color: shadowSpec.color || 'rgba(0,0,0,0.28)',
            blur: shadowSpec.blur ?? 18,
            offsetX: shadowSpec.offsetX ?? 0,
            offsetY: shadowSpec.offsetY ?? 10,
        }));
    }

    async addTemplateShapeObject(spec, idx, width, height) {
        const kind = spec.kind || spec.type;
        const baseName = this.friendlyTemplateLayerName(spec, kind, idx);
        const opacity = Math.min(1, Math.max(0.04, (spec.opacity ?? 100) / 100));
        const strokeWidth = Number(spec.strokeWidth || 0);
        const stroke = strokeWidth > 0 ? (spec.stroke || '#000000') : null;
        const angle = Number(spec.angle || 0);

        if (kind === 'line') {
            const x1 = (spec.x1 ?? spec.x ?? 0) * width;
            const y1 = (spec.y1 ?? spec.y ?? 0) * height;
            const x2 = (spec.x2 ?? ((spec.x ?? 0) + (spec.w ?? 0.2))) * width;
            const y2 = (spec.y2 ?? (spec.y ?? 0)) * height;
            const line = new Line([x1, y1, x2, y2], {
                stroke: spec.stroke || spec.fill || '#c4a574',
                strokeWidth: Math.max(1, strokeWidth || 2),
                opacity,
                selectable: true,
                evented: true,
                name: baseName,
                criasysId: 'tpl_line_' + idx + '_' + Date.now(),
                criasysTemplateLayer: true,
            });
            this.canvas.add(line);
            this.configureSelectableObject(line);
            return line;
        }

        if (kind === 'rect') {
            const w = Math.max(2, (spec.w ?? 0.5) * width);
            const h = Math.max(2, (spec.h ?? 0.5) * height);
            const rect = new Rect({
                left: (spec.x ?? 0) * width,
                top: (spec.y ?? 0) * height,
                width: w,
                height: h,
                fill: this.resolveTemplateFill(spec, w, h),
                opacity,
                rx: spec.rx ?? 0,
                ry: spec.ry ?? spec.rx ?? 0,
                stroke,
                strokeWidth: stroke ? strokeWidth : 0,
                angle,
                originX: spec.originX || 'left',
                originY: spec.originY || 'top',
                visible: true,
                selectable: true,
                evented: true,
                name: baseName,
                criasysId: 'tpl_rect_' + idx + '_' + Date.now(),
                criasysTemplateLayer: true,
            });
            this.applyTemplateObjectShadow(rect, spec.shadow);
            this.canvas.add(rect);
            this.configureSelectableObject(rect);
            return rect;
        }

        if (kind === 'circle') {
            const r = Math.max(2, (spec.r ?? 0.1) * Math.min(width, height));
            const circle = new Circle({
                left: (spec.x ?? 0.5) * width,
                top: (spec.y ?? 0.5) * height,
                radius: r,
                fill: this.resolveTemplateFill(spec, r * 2, r * 2),
                opacity,
                stroke,
                strokeWidth: stroke ? strokeWidth : 0,
                angle,
                originX: 'center',
                originY: 'center',
                visible: true,
                selectable: true,
                evented: true,
                name: baseName,
                criasysId: 'tpl_circle_' + idx + '_' + Date.now(),
                criasysTemplateLayer: true,
            });
            this.applyTemplateObjectShadow(circle, spec.shadow);
            this.canvas.add(circle);
            this.configureSelectableObject(circle);
            return circle;
        }

        if (kind === 'ellipse') {
            const rx = Math.max(2, (spec.rx ?? spec.r ?? 0.12) * width);
            const ry = Math.max(2, (spec.ry ?? spec.r ?? 0.08) * height);
            const ellipse = new Ellipse({
                left: (spec.x ?? 0.5) * width,
                top: (spec.y ?? 0.5) * height,
                rx,
                ry,
                fill: this.resolveTemplateFill(spec, rx * 2, ry * 2),
                opacity,
                stroke,
                strokeWidth: stroke ? strokeWidth : 0,
                angle,
                originX: 'center',
                originY: 'center',
                visible: true,
                selectable: true,
                evented: true,
                name: baseName,
                criasysId: 'tpl_ellipse_' + idx + '_' + Date.now(),
                criasysTemplateLayer: true,
            });
            this.applyTemplateObjectShadow(ellipse, spec.shadow);
            this.canvas.add(ellipse);
            this.configureSelectableObject(ellipse);
            return ellipse;
        }

        return null;
    }

    async addTemplateTextObject(spec, idx, width, height, fontMap, fontList) {
        const fontMeta = spec.fontSlug
            ? (fontMap[spec.fontSlug] || findFontBySlug(fontList, spec.fontSlug))
            : null;
        const bold = spec.fontWeight === 'bold' || spec.fontWeight === 700 || spec.fontWeight === '700';

        if (fontMeta) {
            await ensureFontLoaded(fontMeta, { bold, italic: spec.italic });
        }

        const fontFamily = fontMeta
            ? fontCssFamily(fontMeta)
            : (spec.fontFamily || 'Georgia, serif');

        const layerName = this.friendlyTemplateLayerName(spec, 'text', idx);

        // Escala tipográfica pelo preset (base 1080 de altura de design)
        const scale = Math.min(1.35, Math.max(0.55, height / 1080));
        const fontSize = Math.round((spec.fontSize || 48) * (spec.scaleFont === false ? 1 : scale));

        let bgRect = null;
        if (spec.textBackground) {
            bgRect = this.createTemplateTextBackgroundRect(spec, idx, width, height, layerName);
            if (bgRect) {
                this.canvas.add(bgRect);
                this.configureSelectableObject(bgRect);
            }
        }

        const textObj = createMultilineTextObject(spec.text || 'Seu texto aqui', {
            left: (spec.x ?? 0.5) * width,
            top: (spec.y ?? 0.5) * height,
            width: Math.max(120, Math.round((spec.w ?? 0.88) * width)),
            fontFamily,
            fontSize,
            fill: spec.fill || '#ffffff',
            originX: spec.originX || 'left',
            originY: spec.originY || 'top',
            fontWeight: fontMeta ? resolveFontWeight(fontMeta, bold) : (spec.fontWeight || 'normal'),
            fontStyle: spec.italic ? 'italic' : 'normal',
            textAlign: spec.textAlign || 'left',
            charSpacing: spec.charSpacing ?? 0,
            lineHeight: spec.lineHeight ?? 1.12,
            stroke: (spec.strokeWidth || 0) > 0 ? (spec.stroke || '#000000') : '',
            strokeWidth: spec.strokeWidth || 0,
            angle: Number(spec.angle || 0),
            visible: true,
            selectable: true,
            evented: true,
            opacity: Math.min(1, Math.max(0.2, (spec.opacity ?? 100) / 100)),
            name: layerName,
            criasysId: 'tpl_text_' + idx + '_' + Date.now(),
            criasysFontSlug: spec.fontSlug || null,
            criasysTemplateLayer: true,
            criasysPrimaryEdit: this.isPrimaryTemplateText(spec, layerName),
        });

        if (fontMeta) {
            textObj.set(buildTextStyleFromFont(fontMeta, { bold, italic: spec.italic }));
        }

        if (spec.shadow) {
            this.applyTemplateObjectShadow(textObj, spec.shadow);
        }

        this.canvas.add(textObj);
        this.configureSelectableObject(textObj);
        textObj.setCoords();

        if (bgRect && spec.textBackground) {
            this.fitTemplateTextBackground(bgRect, textObj, spec.textBackground);
        }

        return textObj;
    }

    createTemplateTextBackgroundRect(spec, idx, width, height, layerName) {
        const bgSpec = spec.textBackground;
        if (!bgSpec) {
            return null;
        }
        // Placeholder; fitTemplateTextBackground corrige após o texto medir
        return new Rect({
            left: (spec.x ?? 0.5) * width,
            top: (spec.y ?? 0.5) * height,
            width: 40,
            height: 24,
            fill: bgSpec.fill || '#fde047',
            opacity: Math.min(1, Math.max(0.5, (bgSpec.opacity ?? 100) / 100)),
            rx: bgSpec.rx ?? 6,
            ry: bgSpec.ry ?? bgSpec.rx ?? 6,
            originX: spec.originX || 'left',
            originY: spec.originY || 'top',
            visible: true,
            selectable: true,
            evented: true,
            name: `${layerName} · fundo`,
            criasysId: 'tpl_tbg_' + idx + '_' + Date.now(),
            criasysTemplateLayer: true,
            criasysTextBackground: true,
        });
    }

    fitTemplateTextBackground(bgRect, textObj, bgSpec) {
        if (!bgRect || !textObj) {
            return;
        }
        const bounds = textObj.getBoundingRect(true);
        const padX = bgSpec.padX ?? 16;
        const padY = bgSpec.padY ?? 10;
        bgRect.set({
            left: bounds.left - padX,
            top: bounds.top - padY,
            width: Math.max(8, bounds.width + padX * 2),
            height: Math.max(8, bounds.height + padY * 2),
            originX: 'left',
            originY: 'top',
            visible: true,
        });
        bgRect.setCoords();
        // Garante fundo atrás do texto
        const canvas = this.canvas;
        const textIndex = canvas.getObjects().indexOf(textObj);
        const bgIndex = canvas.getObjects().indexOf(bgRect);
        if (textIndex >= 0 && bgIndex >= 0 && bgIndex > textIndex) {
            if (typeof canvas.moveObjectTo === 'function') {
                canvas.moveObjectTo(bgRect, textIndex);
            } else {
                canvas.remove(bgRect);
                if (typeof canvas.insertAt === 'function') {
                    canvas.insertAt(textIndex, bgRect);
                } else {
                    canvas.add(bgRect);
                    // re-puxa o texto para frente
                    canvas.bringObjectToFront(textObj);
                }
            }
        }
    }

    friendlyTemplateLayerName(spec, kind, idx) {
        const raw = String(spec?.name || '').trim();
        if (raw) {
            return raw;
        }
        if (kind === 'text') {
            return `Texto ${idx + 1}`;
        }
        if (kind === 'rect') {
            return `Faixa ${idx + 1}`;
        }
        if (kind === 'circle') {
            return `Círculo ${idx + 1}`;
        }
        return `Camada ${idx + 1}`;
    }

    isPrimaryTemplateText(spec, layerName) {
        const hay = `${spec?.name || ''} ${layerName || ''} ${spec?.text || ''}`.toLowerCase();
        return /t[ií]tulo|headline|cita[cç][aã]o|hook|frase|promo|evento|nome/.test(hay);
    }

    /**
     * Deixa o canvas “pronto para cliente”: tudo visível, desbloqueado,
     * nomes úteis e o texto principal já selecionado para editar.
     */
    organizeCanvasForClient(template = null) {
        if (!this.canvas) {
            return;
        }

        const objects = this.canvas.getObjects().filter((obj) =>
            !obj.criasysGuide && !obj.criasysCropGuide
        );

        // Remove restos inválidos (tamanho zero / fora do uso)
        objects.forEach((obj) => {
            const w = Math.abs((obj.width || 0) * (obj.scaleX || 1));
            const h = Math.abs((obj.height || 0) * (obj.scaleY || 1));
            if ((w > 0 && w < 1) || (h > 0 && h < 1)) {
                this.canvas.remove(obj);
            }
        });

        const alive = this.canvas.getObjects().filter((obj) =>
            !obj.criasysGuide && !obj.criasysCropGuide
        );

        alive.forEach((obj, index) => {
            const keepOpacity = obj.criasysTemplateLayer || (obj.opacity != null && obj.opacity < 1);
            obj.set({
                visible: true,
                selectable: true,
                evented: true,
                // Não esmagar opacidades sutis de template (antes forçava <0.2 → 1).
                opacity: keepOpacity
                    ? Math.max(0.04, obj.opacity ?? 1)
                    : (obj.opacity == null || obj.opacity < 0.2 ? 1 : obj.opacity),
            });
            if (!obj.name || /^(rect|circle|ellipse|line|i-text|text|textbox|path|group)$/i.test(String(obj.name))) {
                const type = normalizeFabricType(obj);
                obj.name = isFabricText(obj)
                    ? `Texto ${index + 1}`
                    : (type === 'circle' || type === 'ellipse'
                        ? `Forma ${index + 1}`
                        : (type === 'line' ? `Linha ${index + 1}` : `Faixa ${index + 1}`));
            }
            this.configureSelectableObject(obj);
            obj.setCoords();
        });

        // Textos editáveis acima das formas de fundo (exceto fundos de texto, que ficam logo atrás do seu texto)
        const texts = alive.filter((o) => isFabricText(o));
        texts.forEach((text) => {
            this.canvas.bringObjectToFront(text);
        });

        // Seleciona o texto principal para o cliente já começar digitando
        const primary = texts.find((t) => t.criasysPrimaryEdit)
            || texts.find((t) => this.isPrimaryTemplateText({ name: t.name, text: t.text }, t.name))
            || texts[0]
            || null;

        this.canvas.discardActiveObject();
        if (primary) {
            this.canvas.setActiveObject(primary);
            if (typeof primary.enterEditing === 'function' && template?.auto_edit !== false) {
                // Não força enterEditing (mobile/foco chato); só seleciona
            }
        }

        this.canvas.requestRenderAll();
    }

    addTemplateTextBackground(textObj, bgSpec, idx) {
        // Mantido por compatibilidade; o fluxo novo usa create/fitTemplateTextBackground.
        const bounds = textObj.getBoundingRect();
        const padX = bgSpec.padX ?? 16;
        const padY = bgSpec.padY ?? 10;
        const rect = new Rect({
            left: bounds.left - padX,
            top: bounds.top - padY,
            width: bounds.width + (padX * 2),
            height: bounds.height + (padY * 2),
            fill: bgSpec.fill || '#fde047',
            opacity: (bgSpec.opacity ?? 100) / 100,
            rx: bgSpec.rx ?? 6,
            ry: bgSpec.ry ?? bgSpec.rx ?? 6,
            name: (textObj.name || 'Texto') + ' · fundo',
            criasysId: 'tpl_tbg_' + idx + '_' + Date.now(),
            visible: true,
            selectable: true,
            evented: true,
            criasysTemplateLayer: true,
            criasysTextBackground: true,
        });
        this.configureSelectableObject(rect);
        const textIndex = this.canvas.getObjects().indexOf(textObj);
        this.canvas.insertAt(Math.max(0, textIndex), rect);
    }

    async replaceActiveImageSource(url, target = null) {
        const obj = isFabricImage(target)
            ? target
            : this.canvas?.getActiveObject();
        if (!isFabricImage(obj) || !this.canvas) {
            return null;
        }
        const props = {
            left: obj.left,
            top: obj.top,
            scaleX: obj.scaleX,
            scaleY: obj.scaleY,
            angle: obj.angle,
            flipX: obj.flipX,
            flipY: obj.flipY,
            opacity: obj.opacity,
            name: obj.name,
            criasysFilters: obj.criasysFilters,
        };
        this.canvas.remove(obj);
        const img = await FabricImage.fromURL(url, { crossOrigin: 'anonymous' });
        img.set({ ...props, criasysId: 'img_' + Date.now() });
        this.configureSelectableObject(img);
        if (props.criasysFilters) {
            this.applyFiltersToObject(img, props.criasysFilters);
        }
        this.canvas.add(img);
        this.canvas.setActiveObject(img);
        this.canvas.requestRenderAll();
        this.emitChange();
        return img;
    }

    async removeBackgroundFromBlob(blob) {
        const driver = this.bgRemovalDriver
            || document.querySelector('meta[name="studio-bg-driver"]')?.getAttribute('content')
            || 'rembg';

        if (driver === 'off') {
            throw new Error('Remoção de fundo está desligada (IMAGE_STUDIO_BG_REMOVAL_DRIVER=off).');
        }

        // Driver padrão de teste: rembg no servidor (open source).
        if (driver === 'rembg') {
            return this.removeBackgroundViaRembg(blob);
        }

        // IMG.LY isolada: só carrega o pacote AGPL se o driver for explicitamente "imgly".
        if (driver === 'imgly') {
            const { removeBackground } = await import('@imgly/background-removal');
            const result = await removeBackground(blob);
            return URL.createObjectURL(result);
        }

        throw new Error(`Driver de remoção desconhecido: ${driver}`);
    }

    async removeBackgroundViaRembg(blob) {
        const url = this.bgRemovalUrl
            || document.querySelector('meta[name="studio-remove-bg-url"]')?.getAttribute('content');
        if (!url) {
            throw new Error('URL de remoção de fundo (rembg) não configurada.');
        }

        const form = new FormData();
        const file = blob instanceof File
            ? blob
            : new File([blob], 'entrada.png', { type: blob.type || 'image/png' });
        form.append('file', file);

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const res = await fetch(url, {
            method: 'POST',
            body: form,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {}),
            },
            credentials: 'same-origin',
        });

        if (!res.ok) {
            let message = 'Falha ao remover fundo com rembg.';
            const ct = res.headers.get('content-type') || '';
            if (ct.includes('application/json')) {
                try {
                    const data = await res.json();
                    message = data.message || message;
                } catch {
                    /* ignore */
                }
            }
            throw new Error(message);
        }

        const out = await res.blob();
        return URL.createObjectURL(out);
    }

    async addImageFromUrl(url, name = 'Imagem') {
        const img = await FabricImage.fromURL(url, { crossOrigin: 'anonymous' });
        const maxW = this.designWidth * 0.85;
        const maxH = this.designHeight * 0.85;
        const scale = Math.min(maxW / (img.width || 1), maxH / (img.height || 1), 1);
        img.set({
            originX: 'center',
            originY: 'center',
            left: this.designWidth / 2,
            top: this.designHeight / 2,
            scaleX: scale,
            scaleY: scale,
            name,
            criasysId: 'img_' + Date.now(),
        });
        this.configureSelectableObject(img);
        this.canvas.add(img);
        this.canvas.setActiveObject(img);
        this.canvas.requestRenderAll();
        this.emitChange();
    }

    /**
     * Importa Photoshop (.psd): cada camada com bitmap vira imagem no Fabric.
     * Grupos são percorridos; textos/smart objects entram como raster da camada (fidelidade visual).
     * MVP: não reconstrói tipografia nativa 100% editável do PS.
     */
    async importPsdFromArrayBuffer(buffer, options = {}) {
        if (!this.canvas || !buffer) {
            throw new Error('Canvas ou arquivo PSD inválido');
        }

        const {
            replaceWorkspace = true,
            backgroundColor = '#ffffff',
        } = options;

        let psd;
        try {
            psd = readPsd(buffer);
        } catch (e) {
            throw new Error(e?.message || 'Não foi possível ler o PSD (arquivo corrompido ou não suportado)');
        }

        const w = Math.max(1, Math.round(Number(psd.width) || this.designWidth || 1080));
        const h = Math.max(1, Math.round(Number(psd.height) || this.designHeight || 1080));

        const flatLayers = [];
        const walk = (nodes) => {
            if (!Array.isArray(nodes)) {
                return;
            }
            nodes.forEach((node) => {
                if (!node) {
                    return;
                }
                if (Array.isArray(node.children) && node.children.length) {
                    walk(node.children);

                    return;
                }
                const c = node.canvas;
                if (c && c.width > 0 && c.height > 0) {
                    flatLayers.push(node);
                }
            });
        };
        walk(psd.children);

        this.historyPaused = true;
        try {
            if (replaceWorkspace) {
                this.cancelCropMode?.(false);
                this.canvas.discardActiveObject();
                this.canvas.clear();
                this.setSize(w, h);
                this.setBackgroundColor(backgroundColor, 0);
            }

            // children do ag-psd: topo → fundo (como no painel do PS). Fabric: último add = topo.
            const ordered = flatLayers.slice().reverse();
            let added = 0;

            for (const layer of ordered) {
                // eslint-disable-next-line no-await-in-loop
                const ok = await this.addPsdLayerAsImage(layer);
                if (ok) {
                    added += 1;
                }
            }

            if (added === 0 && psd.canvas) {
                await this.addPsdLayerAsImage({
                    name: 'Composição PSD',
                    canvas: psd.canvas,
                    left: 0,
                    top: 0,
                    opacity: 1,
                    hidden: false,
                });
                added = 1;
            }

            if (added === 0) {
                throw new Error('O PSD não tem camadas com imagem utilizável');
            }

            this.canvas.discardActiveObject();
            this.canvas.requestRenderAll();
        } finally {
            this.historyPaused = false;
            this.pushHistory();
            this.emitChange();
        }

        return {
            width: w,
            height: h,
            layers: flatLayers.length || 1,
        };
    }

    /**
     * ag-psd usa opacidade 0–1. Alguns exports legados usam 0–255.
     */
    normalizePsdOpacity(raw) {
        if (raw == null || Number.isNaN(Number(raw))) {
            return 1;
        }
        const n = Number(raw);
        if (n > 1) {
            return Math.max(0, Math.min(1, n / 255));
        }

        return Math.max(0, Math.min(1, n));
    }

    async addPsdLayerAsImage(layer) {
        if (!layer?.canvas || !this.canvas) {
            return false;
        }
        if (!(layer.canvas.width > 0) || !(layer.canvas.height > 0)) {
            return false;
        }

        let dataUrl;
        try {
            dataUrl = layer.canvas.toDataURL('image/png');
        } catch {
            return false;
        }

        const img = await FabricImage.fromURL(dataUrl, { crossOrigin: 'anonymous' });
        const left = Number.isFinite(layer.left) ? layer.left : 0;
        const top = Number.isFinite(layer.top) ? layer.top : 0;
        let opacity = this.normalizePsdOpacity(layer.opacity);
        const fillOpacity = layer.fillOpacity ?? layer.blending?.fillOpacity;
        if (fillOpacity != null && !Number.isNaN(Number(fillOpacity))) {
            opacity *= this.normalizePsdOpacity(fillOpacity);
        }

        const props = {
            originX: 'left',
            originY: 'top',
            left,
            top,
            opacity,
            visible: layer.hidden !== true,
            name: layer.name || 'Camada PSD',
            criasysId: `psd_${Date.now()}_${Math.random().toString(36).slice(2, 7)}`,
            criasysFromPsd: true,
        };

        const gco = this.mapPsdBlendModeToComposite(layer.blendMode);
        if (gco) {
            props.globalCompositeOperation = gco;
        }

        img.set(props);
        this.configureSelectableObject(img);
        this.canvas.add(img);

        return true;
    }

    /** Mapeia blend modes comuns do PSD → globalCompositeOperation do canvas. */
    mapPsdBlendModeToComposite(mode) {
        const m = String(mode || 'normal').toLowerCase().trim();
        const map = {
            normal: null,
            multiply: 'multiply',
            screen: 'screen',
            overlay: 'overlay',
            darken: 'darken',
            lighten: 'lighten',
            'color dodge': 'color-dodge',
            'color-dodge': 'color-dodge',
            'color burn': 'color-burn',
            'color-burn': 'color-burn',
            'hard light': 'hard-light',
            'hard-light': 'hard-light',
            'soft light': 'soft-light',
            'soft-light': 'soft-light',
            difference: 'difference',
            exclusion: 'exclusion',
            hue: 'hue',
            saturation: 'saturation',
            color: 'color',
            luminosity: 'luminosity',
        };

        return Object.prototype.hasOwnProperty.call(map, m) ? map[m] : null;
    }

    applyObjectOpacity(object, opacity) {
        if (!object) {
            return;
        }
        object.set('opacity', Math.max(0, Math.min(1, opacity / 100)));
        this.canvas.requestRenderAll();
        this.emitChange();
    }

    applyObjectFill(object, color) {
        if (!object) {
            return;
        }
        object.set('fill', color);
        this.canvas.requestRenderAll();
        this.emitChange();
    }

    getShapeStyleFromObject(obj) {
        if (!isRecolorableObject(obj)) {
            return null;
        }
        const leaf = isFabricGroup(obj) ? (findFirstPaintLeaf(obj) || obj) : obj;
        const isLine = String(leaf.type || '').toLowerCase() === 'line';
        const strokeWidth = Math.max(0, parseFloat(leaf.strokeWidth) || 0);
        const fillSource = (obj.criasysLastFill && typeof obj.criasysLastFill === 'string')
            ? obj.criasysLastFill
            : leaf.fill;
        const fillState = readShapeFillState(fillSource);

        return {
            fill: fillState.fill || obj.criasysLastFill || '#0d9488',
            fillMode: fillState.mode,
            gradientColorA: fillState.colorA,
            gradientColorB: fillState.colorB,
            gradientAngle: fillState.angle,
            stroke: normalizeColorInput(leaf.stroke, '#ffffff'),
            strokeWidth,
            isLine,
            isElementGroup: isFabricGroup(obj),
        };
    }

    applyShapePaint(object, style) {
        if (!isRecolorableObject(object) || !style) {
            return;
        }
        const strokeWidth = Math.max(0, parseFloat(style.strokeWidth) || 0);
        const stroke = strokeWidth > 0
            ? normalizeColorInput(style.stroke, '#ffffff')
            : '';
        const mode = style.fillMode || 'solid';
        let fill = null;
        if (!style.isLine) {
            if (mode === 'linear' || mode === 'radial') {
                fill = buildShapeGradientFill({
                    mode,
                    colorA: style.gradientColorA || style.fill,
                    colorB: style.gradientColorB,
                    angle: style.gradientAngle,
                });
            } else if (style.fill) {
                fill = normalizeColorInput(style.fill, '#ffffff');
            } else {
                fill = 'transparent';
            }
        }

        if (isFabricGroup(object)) {
            // Ícones SVG: tintas fill e/ou stroke das folhas sem zerar traço existente
            forEachPaintLeaf(object, (leaf) => {
                if (style.isLine) {
                    return;
                }
                const prevFill = leaf.fill;
                const prevStroke = leaf.stroke;
                const leafSw = Math.max(0, parseFloat(leaf.strokeWidth) || 0);
                const hasFill = prevFill != null && prevFill !== '' && prevFill !== 'none' && prevFill !== 'transparent';
                const hasStroke = prevStroke != null && prevStroke !== '' && prevStroke !== 'none';

                if (fill === 'transparent') {
                    if (hasFill) {
                        leaf.set('fill', 'transparent');
                    }
                } else if (fill != null) {
                    if (hasFill || !hasStroke) {
                        leaf.set('fill', fill);
                    }
                    if (hasStroke) {
                        // Traço acompanha a cor sólida; degradê mantém stroke anterior
                        leaf.set('stroke', typeof fill === 'string' ? fill : prevStroke);
                    }
                    if (!hasFill && !hasStroke) {
                        leaf.set('fill', fill);
                    }
                }

                if (strokeWidth > 0) {
                    leaf.set({ stroke, strokeWidth });
                }
                leaf.set('dirty', true);
            });
            if (typeof fill === 'string' && fill && fill !== 'transparent') {
                object.set('criasysLastFill', fill);
            } else if (fill === 'transparent') {
                object.set('criasysLastFill', '');
            }
            object.set({ dirty: true, criasysRecolorable: true });
            object.setCoords?.();
        } else {
            const updates = {
                stroke: strokeWidth > 0 ? stroke : '',
                strokeWidth,
            };
            if (!style.isLine) {
                updates.fill = fill;
            }
            object.set(updates);
            object.setCoords();
        }

        this.canvas.requestRenderAll();
        this.emitChange();
    }

    getActiveObject() {
        return this.canvas?.getActiveObject() ?? null;
    }

    getActiveTextObject() {
        const obj = this.getActiveObject();
        if (!obj) {
            return null;
        }

        if (isFabricText(obj)) {
            return obj;
        }

        if (obj instanceof ActiveSelection || String(obj.type || '').toLowerCase() === 'activeselection') {
            const nested = typeof obj.getObjects === 'function' ? obj.getObjects() : [];
            return nested.find((item) => isFabricText(item)) ?? null;
        }

        return null;
    }

    applyTextPaint(object, style) {
        if (!isFabricText(object) || !style) {
            return;
        }

        const fill = normalizeColorInput(style.fill, '#ffffff');
        const strokeWidth = Math.max(0, parseFloat(style.strokeWidth) || 0);
        const stroke = strokeWidth > 0
            ? normalizeColorInput(style.stroke, '#000000')
            : null;

        object.set({
            fill,
            stroke,
            strokeWidth,
        });

        if (typeof object.setSelectionStyles === 'function' && object.text?.length) {
            object.setSelectionStyles({
                fill,
                stroke,
                strokeWidth,
            }, 0, object.text.length);
        }

        object.set('styles', {});
    }

    async exportBlob(format = 'png', quality = 0.92, options = {}) {
        if (!this.canvas) {
            return null;
        }
        const {
            frameOverlayUrl = null,
            frameVisible = true,
            underlayUrl = null,
            underlayIsVideo = false,
            pagePngDataUrls = null,
            pageJpegDataUrls = null,
            zipPrefix = null,
            omitCanvasBackground = false,
        } = options;
        const exportOpts = {
            underlayUrl,
            underlayIsVideo,
            pagePngDataUrls,
            pageJpegDataUrls,
            zipPrefix,
            omitCanvasBackground,
        };
        if (format === 'svg') {
            return this.withDesignViewport(async () => {
                const svg = this.canvas.toSVG();

                return new Blob([svg], { type: 'image/svg+xml' });
            });
        }
        if (format === 'json') {
            return new Blob([JSON.stringify(this.toJSON(), null, 2)], { type: 'application/json' });
        }
        if (format === 'psd') {
            return await this.exportPsdBlob(frameOverlayUrl, frameVisible, exportOpts);
        }
        if (format === 'pdf') {
            return this.exportPdfBlob(quality, frameOverlayUrl, frameVisible, exportOpts);
        }
        if (format === 'pptx') {
            return this.exportPptxBlob(quality, frameOverlayUrl, frameVisible, exportOpts);
        }
        if (format === 'zip' || format === 'png_zip') {
            return this.exportZipPngBlob(quality, frameOverlayUrl, frameVisible, exportOpts);
        }
        const mime = format === 'jpg' ? 'image/jpeg' : 'image/png';
        let blob;
        if (format === 'jpg') {
            const exportCanvas = await this.renderExportCanvas(1, exportOpts);
            const jpegOff = document.createElement('canvas');
            jpegOff.width = exportCanvas.width;
            jpegOff.height = exportCanvas.height;
            const ctx = jpegOff.getContext('2d');
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, jpegOff.width, jpegOff.height);
            ctx.drawImage(exportCanvas, 0, 0);
            blob = await canvasToBlob(jpegOff, mime, quality);
        } else if (frameOverlayUrl && frameVisible !== false) {
            blob = await this.withDesignViewport(async () => {
                let dataUrl = await compositeFrameOnCanvasDataUrl(this, frameOverlayUrl, frameVisible, exportOpts);
                if (format === 'jpg') {
                    const jpegOff = document.createElement('canvas');
                    jpegOff.width = this.designWidth;
                    jpegOff.height = this.designHeight;
                    const ctx = jpegOff.getContext('2d');
                    ctx.fillStyle = '#ffffff';
                    ctx.fillRect(0, 0, jpegOff.width, jpegOff.height);
                    const img = await loadHtmlImage(dataUrl);
                    ctx.drawImage(img, 0, 0);
                    dataUrl = jpegOff.toDataURL('image/jpeg', quality);
                }
                const res = await fetch(dataUrl);

                return res.blob();
            });
        } else {
            const exportCanvas = await this.renderExportCanvas(1, exportOpts);
            blob = await canvasToBlob(exportCanvas, mime, quality);
        }

        return blob;
    }

    async exportPsdBlob(frameOverlayUrl = null, frameVisible = true, exportOpts = {}) {
        return this.withDesignViewport(async () => {
        const w = this.designWidth;
        const h = this.designHeight;
        const layers = [];

        const { underlayUrl = null, underlayIsVideo = false } = exportOpts;
        if (underlayUrl) {
            try {
                const media = await loadMediaDrawable(underlayUrl, underlayIsVideo);
                if (media) {
                    const underCanvas = document.createElement('canvas');
                    underCanvas.width = w;
                    underCanvas.height = h;
                    drawCoverMedia(underCanvas.getContext('2d'), media, w, h);
                    layers.push({ name: 'Slide', canvas: underCanvas });
                }
            } catch {
                /* skip */
            }
        }

        const paint = this.getBackgroundPaint();
        if (paint) {
            const bgCanvas = document.createElement('canvas');
            bgCanvas.width = w;
            bgCanvas.height = h;
            const ctx = bgCanvas.getContext('2d');
            ctx.fillStyle = `rgba(${paint.r},${paint.g},${paint.b},${paint.a})`;
            ctx.fillRect(0, 0, w, h);
            layers.push({ name: 'Fundo', canvas: bgCanvas });
        }

        [...this.canvas.getObjects()].reverse().forEach((obj, idx) => {
            if (obj.visible === false) {
                return;
            }
            const bounds = obj.getBoundingRect();
            let el;
            try {
                el = obj.toCanvasElement({ multiplier: 1 });
            } catch {
                return;
            }
            layers.push({
                name: obj.name || obj.type || `Camada ${idx + 1}`,
                left: Math.round(bounds.left),
                top: Math.round(bounds.top),
                opacity: obj.opacity ?? 1,
                canvas: el,
            });
        });

        if (frameOverlayUrl && frameVisible !== false) {
            const frameCanvas = document.createElement('canvas');
            frameCanvas.width = w;
            frameCanvas.height = h;
            const fctx = frameCanvas.getContext('2d');
            const frameImg = await loadHtmlImage(frameOverlayUrl);
            fctx.drawImage(frameImg, 0, 0, w, h);
            layers.unshift({
                name: 'Moldura',
                canvas: frameCanvas,
            });
        }

        const buffer = writePsdBuffer({ width: w, height: h, children: layers });
        return new Blob([buffer], { type: 'application/vnd.adobe.photoshop' });
        });
    }

    async exportDesignDataUrl(format = 'png', quality = 0.92, frameOverlayUrl = null, frameVisible = true, exportOpts = {}) {
        const w = this.designWidth;
        const h = this.designHeight;
        const wantJpeg = format === 'jpg' || format === 'jpeg';

        if (frameOverlayUrl && frameVisible !== false) {
            const pngUrl = await compositeFrameOnCanvasDataUrl(this, frameOverlayUrl, frameVisible, exportOpts);
            if (!wantJpeg) {
                return pngUrl;
            }
            const jpegOff = document.createElement('canvas');
            jpegOff.width = w;
            jpegOff.height = h;
            const ctx = jpegOff.getContext('2d');
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, w, h);
            const img = await loadHtmlImage(pngUrl);
            ctx.drawImage(img, 0, 0);

            return jpegOff.toDataURL('image/jpeg', quality);
        }

        const exportCanvas = await this.renderExportCanvas(1, exportOpts);
        if (wantJpeg) {
            const jpegOff = document.createElement('canvas');
            jpegOff.width = exportCanvas.width;
            jpegOff.height = exportCanvas.height;
            const ctx = jpegOff.getContext('2d');
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, jpegOff.width, jpegOff.height);
            ctx.drawImage(exportCanvas, 0, 0);

            return jpegOff.toDataURL('image/jpeg', quality);
        }

        return exportCanvas.toDataURL('image/png');
    }

    async exportPdfBlob(quality = 0.92, frameOverlayUrl = null, frameVisible = true, exportOpts = {}) {
        return this.withDesignViewport(async () => {
        const w = this.designWidth;
        const h = this.designHeight;
        let pageUrls = Array.isArray(exportOpts.pageJpegDataUrls)
            ? exportOpts.pageJpegDataUrls.filter(Boolean)
            : [];
        if (!pageUrls.length) {
            pageUrls = [await this.exportDesignDataUrl('jpg', quality, frameOverlayUrl, frameVisible, exportOpts)];
        }
        const orientation = w >= h ? 'landscape' : 'portrait';
        const pdf = new jsPDF({
            orientation,
            unit: 'px',
            format: [w, h],
            hotfixes: ['px_scaling'],
        });
        pageUrls.forEach((dataUrl, index) => {
            if (index > 0) {
                pdf.addPage([w, h], orientation);
            }
            pdf.addImage(dataUrl, 'JPEG', 0, 0, w, h);
        });
        return pdf.output('blob');
        });
    }

    /**
     * MVP PowerPoint: cada página = 1 slide 16:9 (ou proporção do canvas) com PNG full-bleed.
     * Texto/formas nativos do PPT ficam para fase 2.
     */
    async exportPptxBlob(quality = 0.92, frameOverlayUrl = null, frameVisible = true, exportOpts = {}) {
        return this.withDesignViewport(async () => {
            const w = this.designWidth;
            const h = this.designHeight;
            let pageUrls = Array.isArray(exportOpts.pagePngDataUrls)
                ? exportOpts.pagePngDataUrls.filter(Boolean)
                : [];
            if (!pageUrls.length) {
                pageUrls = [await this.exportDesignDataUrl('png', quality, frameOverlayUrl, frameVisible, exportOpts)];
            }

            const pptx = new PptxGenJS();
            const inchesW = w >= h ? 13.333 : 7.5;
            const inchesH = inchesW * (h / w);
            pptx.defineLayout({ name: 'MARKCRAFT', width: inchesW, height: inchesH });
            pptx.layout = 'MARKCRAFT';
            pptx.author = 'MarkCraft';
            pptx.title = 'Apresentação MarkCraft';

            pageUrls.forEach((dataUrl) => {
                const slide = pptx.addSlide();
                slide.addImage({
                    data: dataUrl,
                    x: 0,
                    y: 0,
                    w: inchesW,
                    h: inchesH,
                });
            });

            return pptx.write({ outputType: 'blob' });
        });
    }

    /**
     * Kit sequencial: ZIP com um PNG por página do deck (carrossel redes / site).
     */
    async exportZipPngBlob(quality = 0.92, frameOverlayUrl = null, frameVisible = true, exportOpts = {}) {
        return this.withDesignViewport(async () => {
            let pageUrls = Array.isArray(exportOpts.pagePngDataUrls)
                ? exportOpts.pagePngDataUrls.filter(Boolean)
                : [];
            if (!pageUrls.length) {
                pageUrls = [await this.exportDesignDataUrl('png', quality, frameOverlayUrl, frameVisible, exportOpts)];
            }

            const zip = new JSZip();
            const prefix = String(exportOpts.zipPrefix || 'frame').replace(/[^\w\-]+/g, '_');
            pageUrls.forEach((dataUrl, index) => {
                const base64 = String(dataUrl).includes(',')
                    ? String(dataUrl).split(',')[1]
                    : String(dataUrl);
                const name = `${prefix}-${String(index + 1).padStart(2, '0')}.png`;
                zip.file(name, base64, { base64: true });
            });

            return zip.generateAsync({ type: 'blob' });
        });
    }

    zoomToFit(containerWidth, containerHeight) {
        if (!this.canvas || !containerWidth || !containerHeight) {
            return this.viewportZoom || 1;
        }
        const pad = 12;
        const scale = Math.min(
            (containerWidth - pad) / this.designWidth,
            (containerHeight - pad) / this.designHeight
        );

        return this.applyViewportZoom(Math.max(0.08, Math.min(1, scale)));
    }
}

export function imageStudioMethods() {
    return {
        imageStudioReady: false,
        imageStudioPresets: [],
        imageStudioPrimaryFormatDefs: [],
        imageStudioGroupOrder: [],
        imageStudioGroups: {},
        imageStudioExportFormats: [],
        imageStudioTemplates: [],
        imageStudioFonts: [],
        imageStudioFontGroups: {},
        imageStudioIconGlyphs: [],
        imageStudioFontMap: {},
        imageStudioFontFilter: '',
        imageStudioIconFilter: '',
        imageStudioTextFontSlug: 'bebas_neue',
        imageStudioTextContent: 'Seu título aqui',
        imageStudioTextSize: 64,
        imageStudioTextFill: '#ffffff',
        imageStudioTextStroke: '#000000',
        imageStudioTextStrokeWidth: 0,
        imageStudioTextBold: true,
        imageStudioTextItalic: false,
        imageStudioTextUnderline: false,
        imageStudioTextLinethrough: false,
        imageStudioTextAlign: 'center',
        imageStudioTextLineHeight: 1.16,
        imageStudioTextCharSpacing: 0,
        imageStudioTextShadow: false,
        imageStudioTextShadowColor: '#000000',
        imageStudioTextShadowBlur: 8,
        _syncingTextUi: false,
        imageStudioSidebarTab: 'tools',
        imageStudioBgRemoval: false,
        imageStudioBgRemovalDriver: 'rembg',
        imageStudioBgRemovalLabel: '',
        imageStudioPreset: 'custom',
        imageStudioCustomWidth: 1920,
        imageStudioCustomHeight: 1080,
        imageStudioDimensionsModalOpen: false,
        imageStudioDimensionsFilter: '',
        imageStudioTemplatesModalOpen: false,
        imageStudioTemplatesFilter: '',
        imageStudioPacksModalOpen: false,
        imageStudioPacksFilter: '',
        imageStudioPacksCategory: '',
        imageStudioPacks: [],
        imageStudioPackCategories: [],
        imageStudioBrand: null,
        imageStudioCropping: false,
        imageStudioContextMenu: { open: false, x: 0, y: 0, hasSelection: false },
        imageStudioPresetPlatformMap: {},
        imageStudioEngine: null,
        imageStudioLayers: [],
        imageStudioActiveLayerId: null,
        imageStudioActiveLayerName: '',
        imageStudioSaving: false,
        imageStudioLastExport: null,
        imageStudioBgColor: '#ffffff',
        imageStudioBgTransparency: 0,
        imageStudioUnderlaySlideIndex: -1,
        imageStudioUnderlayEnabled: true,
        imageStudioSelectedObject: null,
        imageStudioObjectScale: 100,
        imageStudioObjectAngle: 0,
        _imageStudioControlDragging: false,
        _imageStudioSkipLayerScroll: false,
        imageStudioObjectAngle: 0,
        imageStudioShapeFill: '#ffffff',
        imageStudioShapeStroke: '#ffffff',
        imageStudioShapeStrokeWidth: 0,
        imageStudioShapeIsLine: false,
        imageStudioFillMode: 'solid',
        imageStudioGradientColorA: '#0d9488',
        imageStudioGradientColorB: '#0f172a',
        imageStudioGradientAngle: 90,
        imageStudioCanGroup: false,
        imageStudioCanUngroup: false,
        imageStudioGroupBagCount: 0,
        imageStudioCanRecolorSelection: false,
        imageStudioZoom: 100,
        imageStudioShowFormatGuides: true,
        imageStudioBgRemoving: false,
        imageStudioFilters: { ...DEFAULT_FILTER_STATE },
        imageStudioShowGrid: false,
        imageStudioSnapGrid: false,
        imageStudioGridSize: 20,
        imageStudioCanUndo: false,
        imageStudioCanRedo: false,
        imageStudioElements: [],
        imageStudioElementGroups: {},
        imageStudioElementFilter: '',
        imageStudioElementFilterGroup: '',
        imageStudioElementsModalOpen: false,
        _imageStudioElementsModalShown: false,
        imageStudioExpanded: false,
        imageStudioMobileSheetOpen: false,
        imageStudioMobileMenuOpen: false,
        _imageStudioClipboard: [],
        imageStudioLocalWatch: null,
        imageStudioFileDragOver: false,
        imageStudioDeckPages: [{ id: 'slide-1', name: 'Slide 1', canvas: null }],
        imageStudioDeckPageIndex: 0,
        imageStudioDeckBusy: false,
        imageStudioDeckKind: 'presentation',
        imageStudioFileDragDepth: 0,

        normalizeImageStudioElementList(source) {
            if (Array.isArray(source)) {
                return source;
            }
            if (source && typeof source === 'object') {
                return Object.values(source);
            }

            return [];
        },

        filterImageStudioElements() {
            const list = this.normalizeImageStudioElementList(this.imageStudioElements);
            const q = (this.imageStudioElementFilter || '').trim().toLowerCase();
            const groupFilter = (this.imageStudioElementFilterGroup || '').trim();

            return list.filter((el) => {
                if (!el || typeof el !== 'object') {
                    return false;
                }
                if (groupFilter) {
                    const matchesGroup = groupFilter === 'formas'
                        ? String(el.group || '').startsWith('formas')
                        : el.group === groupFilter;
                    if (!matchesGroup) {
                        return false;
                    }
                }
                if (q) {
                    const hay = `${el.name || ''} ${el.group || ''} ${el.char || ''} ${el.icon || ''}`.toLowerCase();
                    if (!hay.includes(q)) {
                        return false;
                    }
                }

                return true;
            });
        },

        imageStudioElementGroupList() {
            const groups = {};
            this.filterImageStudioElements().forEach((el) => {
                const key = this.imageStudioElementGroups?.[el.group] || el.group || 'Outros';
                if (!groups[key]) {
                    groups[key] = [];
                }
                groups[key].push(el);
            });

            return Object.entries(groups).map(([name, items]) => ({ name, items }));
        },

        imageStudioElementsFilteredCount() {
            return this.filterImageStudioElements().length;
        },

        imageStudioElementQuickGroups() {
            return [
                { id: '', label: 'Todos' },
                { id: 'icones', label: 'Ícones' },
                { id: 'emojis', label: 'Emojis' },
                { id: 'blobs', label: 'Slimes' },
                { id: 'formas', label: 'Formas' },
                { id: 'formas_molduras', label: 'Molduras' },
                { id: 'formas_3d', label: '3D' },
                { id: 'formas_extras', label: 'Decor' },
                { id: 'adesivos', label: 'Adesivos' },
                { id: 'linhas', label: 'Linhas' },
            ];
        },

        imageStudioIconGlyphClass(font) {
            const map = {
                fa_regular: 'is-ic-fa-regular',
                fa_brands: 'is-ic-fa-brands',
                material_symbols: 'is-ic-material',
                fa_solid: 'is-ic-fa-solid',
            };
            return map[font] || 'is-ic-fa-solid';
        },

        syncImageStudioElementsCatalog() {
            const base = this.normalizeImageStudioElementList(this.imageStudioElements)
                .filter((el) => el?.type !== 'icon_glyph');
            const glyphs = (this.imageStudioIconGlyphs || []).map((g) => ({
                type: 'icon_glyph',
                slug: g.slug || ('icon_' + String(g.char || '').charCodeAt(0)),
                name: g.label || g.slug || 'Ícone',
                char: g.char,
                font: g.font || 'fa_solid',
                group: 'icones',
                icon_group: g.group || '',
            }));
            const slugs = new Set(base.map((el) => el.slug));
            const merged = [...base];
            glyphs.forEach((g) => {
                if (g.slug && !slugs.has(g.slug)) {
                    merged.push(g);
                    slugs.add(g.slug);
                }
            });
            this.imageStudioElements = merged;
            if (!this.imageStudioElementGroups?.icones) {
                this.imageStudioElementGroups = {
                    ...(this.imageStudioElementGroups || {}),
                    icones: 'Ícones',
                };
            }
        },

        toggleImageStudioExpanded() {
            this.imageStudioExpanded = !this.imageStudioExpanded;
            document.body.classList.toggle('overflow-hidden', this.imageStudioExpanded);
            this.$nextTick(() => this.fitImageStudioCanvas());
        },

        closeImageStudioExpanded() {
            if (!this.imageStudioExpanded) {
                return;
            }
            this.imageStudioExpanded = false;
            document.body.classList.remove('overflow-hidden');
            this.$nextTick(() => this.fitImageStudioCanvas());
        },

        imageStudioOpenElementsModal() {
            this.imageStudioElementFilter = '';
            this.imageStudioElementFilterGroup = '';
            this.imageStudioElementsModalOpen = true;
        },

        imageStudioCloseElementsModal() {
            this.imageStudioElementsModalOpen = false;
        },

        async imageStudioAddElementFromModal(el) {
            await this.imageStudioAddElement(el);
        },

        get filteredImageStudioPresets() {
            const q = (this.imageStudioPresetFilter || '').trim().toLowerCase();
            let list = this.imageStudioPresets || [];
            if (q) {
                list = list.filter(
                    (p) =>
                        (p.name || '').toLowerCase().includes(q)
                        || (p.group_label || '').toLowerCase().includes(q)
                );
            }
            return list;
        },

        get imageStudioPresetGroups() {
            const groups = {};
            (this.filteredImageStudioPresets || []).forEach((p) => {
                const key = p.group_label || p.group || 'Outros';
                if (!groups[key]) groups[key] = [];
                groups[key].push(p);
            });
            const order = this.imageStudioGroupOrder?.length
                ? this.imageStudioGroupOrder
                : ['YouTube', 'Proporções genéricas', 'Instagram'];
            const sorted = {};
            order.forEach((name) => {
                if (groups[name]?.length) {
                    sorted[name] = groups[name];
                }
            });
            Object.keys(groups).forEach((name) => {
                if (!sorted[name]) {
                    sorted[name] = groups[name];
                }
            });
            return sorted;
        },

        imageStudioPrimaryFormats() {
            const defs = this.imageStudioPrimaryFormatDefs?.length
                ? this.imageStudioPrimaryFormatDefs
                : [
                    { slug: 'yt_thumb_hd', label: 'YouTube Full HD', aspect: '16:9' },
                    { slug: 'yt_thumb', label: 'YouTube Thumbnail', aspect: '16:9' },
                    { slug: 'yt_shorts', label: 'YouTube Shorts', aspect: '9:16' },
                ];
            return defs.map((def) => {
                const preset = (this.imageStudioPresets || []).find((p) => p.slug === def.slug);
                if (!preset) {
                    return null;
                }
                return {
                    slug: def.slug,
                    label: def.label || preset.name,
                    aspect: def.aspect || preset.aspect,
                    width: preset.width,
                    height: preset.height,
                };
            }).filter(Boolean);
        },

        imageStudioCanvasAspectLabel() {
            const w = this.imageStudioEngine?.designWidth || this.imageStudioCustomWidth || 1920;
            const h = this.imageStudioEngine?.designHeight || this.imageStudioCustomHeight || 1080;
            if (w === h) {
                return '1:1';
            }
            if (w > h) {
                const ratio = (w / h).toFixed(2);
                return `${ratio}:1`;
            }
            return `1:${(h / w).toFixed(2)}`;
        },

        syncImageStudioCustomDimensionsFromEngine() {
            const w = this.imageStudioEngine?.designWidth;
            const h = this.imageStudioEngine?.designHeight;
            if (w && h) {
                this.imageStudioCustomWidth = w;
                this.imageStudioCustomHeight = h;
            }
        },

        imageStudioReferencePresets() {
            const q = (this.imageStudioDimensionsFilter || '').trim().toLowerCase();
            return (this.imageStudioPresets || [])
                .filter((p) => p.slug !== 'custom')
                .filter((p) => {
                    if (!q) {
                        return true;
                    }
                    return (p.name || '').toLowerCase().includes(q)
                        || (p.group_label || '').toLowerCase().includes(q)
                        || String(p.width).includes(q)
                        || String(p.height).includes(q)
                        || (p.aspect || '').includes(q);
                });
        },

        imageStudioReferencePresetGroups() {
            const groups = {};
            this.imageStudioReferencePresets().forEach((p) => {
                const key = p.group_label || p.group || 'Outros';
                if (!groups[key]) {
                    groups[key] = [];
                }
                groups[key].push(p);
            });
            const order = this.imageStudioGroupOrder?.length
                ? this.imageStudioGroupOrder
                : ['YouTube', 'Proporções genéricas', 'Instagram', 'TikTok', 'Facebook'];
            const sorted = {};
            order.forEach((name) => {
                if (groups[name]?.length) {
                    sorted[name] = groups[name];
                }
            });
            Object.keys(groups).forEach((name) => {
                if (!sorted[name]) {
                    sorted[name] = groups[name];
                }
            });
            return sorted;
        },

        openImageStudioDimensionsModal() {
            this.imageStudioDimensionsModalOpen = true;
        },

        closeImageStudioDimensionsModal() {
            this.imageStudioDimensionsModalOpen = false;
        },

        openImageStudioTemplatesModal() {
            this.imageStudioTemplatesModalOpen = true;
        },

        closeImageStudioTemplatesModal() {
            this.imageStudioTemplatesModalOpen = false;
        },

        openImageStudioPacksModal() {
            this.imageStudioPacksFilter = '';
            this.imageStudioPacksCategory = '';
            this.imageStudioPacksModalOpen = true;
        },

        closeImageStudioPacksModal() {
            this.imageStudioPacksModalOpen = false;
        },

        imageStudioPacksItemCount() {
            return (this.imageStudioPacks || []).reduce((n, p) => n + (p.items?.length || 0), 0);
        },

        imageStudioFilteredPacks() {
            const q = (this.imageStudioPacksFilter || '').trim().toLowerCase();
            const cat = (this.imageStudioPacksCategory || '').trim();

            return (this.imageStudioPacks || [])
                .filter((pack) => !cat || pack.category === cat || pack.id === cat)
                .map((pack) => {
                    const items = (pack.items || []).filter((item) => {
                        if (!q) {
                            return true;
                        }
                        const hay = `${item.name || ''} ${item.description || ''} ${item.slug || ''} ${pack.name || ''}`.toLowerCase();

                        return hay.includes(q);
                    });

                    return { ...pack, items };
                })
                .filter((pack) => (pack.items || []).length > 0);
        },

        async imageStudioApplyPackItem(item) {
            if (!item) {
                return;
            }
            if (item.type === 'brand_kit' || item.slug === 'action_brand_kit') {
                this.closeImageStudioPacksModal();
                await this.imageStudioApplyBrandKit();
                this.message = this.message || 'Marca MarkCraft aplicada — ajuste cores e textos nas camadas.';

                return;
            }
            await this.imageStudioApplyTemplate(item);
        },

        imageStudioTemplateGroups() {
            const q = (this.imageStudioTemplatesFilter || '').trim().toLowerCase();
            const labels = {
                blog: 'Blog',
                web: 'Web',
                stories: 'Stories / Reels',
                shorts: 'Shorts',
                feed: 'Feed',
                youtube: 'YouTube',
                instagram: 'Instagram',
                facebook: 'Facebook',
                linkedin: 'LinkedIn',
                pinterest: 'Pinterest',
                twitter: 'X / Twitter',
                tiktok: 'TikTok',
                whatsapp: 'WhatsApp',
                marketing: 'Marketing',
                avatars: 'Avatar / perfil',
                ratios: 'Proporções',
                print: 'Impressão',
                outros: 'Outros',
            };
            const list = (this.imageStudioTemplates || []).filter((t) => {
                if (!q) {
                    return true;
                }
                const hay = `${t.name || ''} ${t.description || ''} ${t.group || ''} ${t.slug || ''}`.toLowerCase();

                return hay.includes(q);
            });
            const groups = {};
            list.forEach((t) => {
                const raw = t.group || 'outros';
                const key = labels[raw] || raw;
                if (!groups[key]) {
                    groups[key] = [];
                }
                groups[key].push(t);
            });

            return groups;
        },

        async pickImageStudioReferenceDimensions(preset) {
            if (!preset?.width || !preset?.height) {
                return;
            }
            // Clique no modal aplica o tamanho imediatamente (sem exigir “Aplicar”).
            if (preset.slug && preset.slug !== 'custom') {
                await this.switchImageStudioPreset(preset.slug);
            } else {
                this.imageStudioCustomWidth = preset.width;
                this.imageStudioCustomHeight = preset.height;
                await this.applyImageStudioCustomDimensions();
                this.message = `Dimensões: ${preset.name || 'Personalizado'} (${preset.width}×${preset.height})`;
            }
            this.closeImageStudioDimensionsModal();
        },

        async applyImageStudioCustomDimensions() {
            let w = Math.round(Number(this.imageStudioCustomWidth) || 0);
            let h = Math.round(Number(this.imageStudioCustomHeight) || 0);
            w = Math.min(8000, Math.max(100, w));
            h = Math.min(8000, Math.max(100, h));
            this.imageStudioCustomWidth = w;
            this.imageStudioCustomHeight = h;
            this.imageStudioPreset = 'custom';

            if (!this.imageStudioEngine?.canvas) {
                await this.initImageStudio();
            }
            if (!this.imageStudioEngine?.canvas) {
                return;
            }

            this.imageStudioEngine.setSize(w, h);
            this.fitImageStudioCanvas();
            await this.saveImageStudioDesign();
            this.message = `Canvas ${w}×${h} px (${this.imageStudioCanvasAspectLabel()})`;
        },

        imageStudioCanvasViewportStyle() {
            const w = this.imageStudioEngine?.designWidth || this.imageStudioCustomWidth || 1920;
            const h = this.imageStudioEngine?.designHeight || this.imageStudioCustomHeight || 1080;
            const z = (this.imageStudioZoom || 100) / 100;

            return {
                width: `${Math.ceil(w * z)}px`,
                height: `${Math.ceil(h * z)}px`,
                flexShrink: '0',
            };
        },

        imageStudioCanvasScalerStyle() {
            const w = this.imageStudioEngine?.designWidth || this.imageStudioCustomWidth || 1920;
            const h = this.imageStudioEngine?.designHeight || this.imageStudioCustomHeight || 1080;
            const z = (this.imageStudioZoom || 100) / 100;
            const checker = 'repeating-conic-gradient(#3f3f46 0% 25%, #27272a 0% 50%) 50% / 16px 16px';
            let bg = 'transparent';

            if (this.imageStudioShowUnderlayMedia()) {
                bg = 'transparent';
            } else {
                const transparency = Number(this.imageStudioBgTransparency) || 0;
                if (transparency >= 100) {
                    bg = checker;
                } else {
                    const paint = this.imageStudioEngine?.getBackgroundPaint?.();
                    if (paint) {
                        bg = `rgba(${paint.r},${paint.g},${paint.b},${paint.a})`;
                    } else {
                        bg = this.imageStudioBgColor || '#ffffff';
                    }
                }
            }

            return {
                width: `${Math.ceil(w * z)}px`,
                height: `${Math.ceil(h * z)}px`,
                zoom: 1,
                transform: 'none',
                transformOrigin: 'top left',
                background: bg,
            };
        },

        resolveImageStudioPresetMeta(slug = null) {
            const target = slug || this.imageStudioPreset;
            if (target && target !== 'custom') {
                const found = (this.imageStudioPresets || []).find((p) => p.slug === target);
                if (found?.width && found?.height) {
                    return {
                        slug: found.slug,
                        name: found.name || found.slug,
                        width: found.width,
                        height: found.height,
                        aspect: found.aspect || null,
                        group: found.group || null,
                        group_label: found.group_label || null,
                    };
                }
            }

            const w = this.imageStudioEngine?.designWidth || this.imageStudioCustomWidth || 1920;
            const h = this.imageStudioEngine?.designHeight || this.imageStudioCustomHeight || 1080;

            return {
                slug: 'custom',
                name: 'Personalizado',
                width: w,
                height: h,
                aspect: this.imageStudioCanvasAspectLabel(),
            };
        },

        get imageStudioCurrentPreset() {
            return this.resolveImageStudioPresetMeta();
        },

        get filteredImageStudioFonts() {
            const q = (this.imageStudioFontFilter || '').trim().toLowerCase();
            let list = this.imageStudioFonts || [];
            if (q) {
                list = list.filter((f) =>
                    (f.label || '').toLowerCase().includes(q)
                    || (f.group_label || '').toLowerCase().includes(q)
                    || (f.slug || '').toLowerCase().includes(q)
                );
            }
            return list;
        },

        get imageStudioFontsGrouped() {
            const groups = {};
            (this.filteredImageStudioFonts || []).forEach((f) => {
                const key = f.group_label || f.group || 'Outros';
                if (!groups[key]) {
                    groups[key] = [];
                }
                groups[key].push(f);
            });
            return groups;
        },

        get filteredImageStudioIcons() {
            const q = (this.imageStudioIconFilter || '').trim().toLowerCase();
            let list = this.imageStudioIconGlyphs || [];
            if (q) {
                list = list.filter((g) =>
                    (g.label || '').toLowerCase().includes(q)
                    || (g.group || '').toLowerCase().includes(q)
                );
            }
            return list;
        },

        imageStudioTextStylePayload() {
            return {
                fontSlug: this.imageStudioTextFontSlug,
                content: normalizeMultilineText(this.imageStudioTextContent || ''),
                fontSize: this.imageStudioTextSize,
                fill: this.imageStudioTextFill,
                stroke: this.imageStudioTextStroke,
                strokeWidth: this.imageStudioTextStrokeWidth,
                bold: this.imageStudioTextBold,
                italic: this.imageStudioTextItalic,
                underline: this.imageStudioTextUnderline,
                linethrough: this.imageStudioTextLinethrough,
                align: this.imageStudioTextAlign,
                lineHeight: this.imageStudioTextLineHeight,
                charSpacing: this.imageStudioTextCharSpacing,
                shadow: this.imageStudioTextShadow,
                shadowColor: this.imageStudioTextShadowColor,
                shadowBlur: this.imageStudioTextShadowBlur,
            };
        },

        buildImageStudioFontMap() {
            const map = {};
            (this.imageStudioFonts || []).forEach((f) => {
                if (f.slug) {
                    map[f.slug] = f;
                }
            });
            this.imageStudioFontMap = map;
        },

        seedImageStudioPresetsFromEmbedded() {
            const presets = ImageStudioEngine.readEmbeddedJson('criasys-image-studio-presets');
            const defaults = ImageStudioEngine.readEmbeddedJson('criasys-image-studio-defaults');
            const primary = ImageStudioEngine.readEmbeddedJson('criasys-image-studio-primary-formats');
            const groupOrder = ImageStudioEngine.readEmbeddedJson('criasys-image-studio-group-order');
            const templates = ImageStudioEngine.readEmbeddedJson('criasys-image-studio-templates');
            const packs = ImageStudioEngine.readEmbeddedJson('criasys-image-studio-packs');
            const packCategories = ImageStudioEngine.readEmbeddedJson('criasys-image-studio-pack-categories');
            const brand = ImageStudioEngine.readEmbeddedJson('criasys-image-studio-brand');

            if (Array.isArray(presets) && presets.length) {
                this.imageStudioPresets = presets;
            } else if (!this.imageStudioPresets?.length) {
                this.imageStudioPresets = FALLBACK_PRESETS;
            }
            if (Array.isArray(primary) && primary.length) {
                this.imageStudioPrimaryFormatDefs = primary;
            }
            if (Array.isArray(groupOrder) && groupOrder.length) {
                this.imageStudioGroupOrder = groupOrder;
            }
            if (Array.isArray(templates) && templates.length) {
                this.imageStudioTemplates = normalizeTemplatesList(templates);
            }
            if (Array.isArray(packs) && packs.length) {
                this.imageStudioPacks = packs;
            }
            if (Array.isArray(packCategories) && packCategories.length) {
                this.imageStudioPackCategories = packCategories;
            }
            if (brand && typeof brand === 'object') {
                this.imageStudioBrand = brand;
            }
            if (defaults?.preset) {
                this.imageStudioPreset = 'custom';
            }
            if (defaults?.width) {
                this.imageStudioCustomWidth = defaults.width;
            }
            if (defaults?.height) {
                this.imageStudioCustomHeight = defaults.height;
            }
            this.seedImageStudioBgRemovalFromPage();
        },

        /**
         * Liga o botão de remoção imediatamente (meta + JSON embutido),
         * sem esperar o /catalogo (que pode falhar/atrasar o check do Python).
         */
        seedImageStudioBgRemovalFromPage() {
            const embedded = ImageStudioEngine.readEmbeddedJson('criasys-image-studio-bg-removal') || {};
            const url = document.querySelector('meta[name="studio-remove-bg-url"]')?.getAttribute('content') || '';
            const driver = embedded.driver
                || document.querySelector('meta[name="studio-bg-driver"]')?.getAttribute('content')
                || this.imageStudioBgRemovalDriver
                || 'rembg';

            this.imageStudioBgRemovalDriver = driver;
            this.imageStudioBgRemovalLabel = embedded.label || this.imageStudioBgRemovalLabel || '';

            // Habilita se o servidor confirmou OU se a rota rembg está configurada.
            this.imageStudioBgRemoval = driver === 'imgly'
                || !!embedded.available
                || (driver === 'rembg' && !!url);

            if (this.imageStudioEngine) {
                this.imageStudioEngine.bgRemovalDriver = driver;
                this.imageStudioEngine.bgRemovalUrl = url || null;
            }
        },

        resolveImageStudioImageObject() {
            const candidates = [
                this._bgRemoveTarget,
                this.resolveImageStudioActiveObject(),
                this._imageStudioStickyObject,
                this._imageStudioActiveObject,
            ];

            for (const obj of candidates) {
                if (isFabricImage(obj)) {
                    return obj;
                }
                if (obj && normalizeFabricType(obj) === 'activeselection') {
                    const images = (obj.getObjects?.() || []).filter((o) => isFabricImage(o));
                    if (images.length === 1) {
                        return images[0];
                    }
                }
            }

            const layerImg = (this.imageStudioLayers || [])
                .map((l) => l.object)
                .find((o) => isFabricImage(o) && (o === this._imageStudioStickyObject || o === this._imageStudioActiveObject));
            return layerImg || null;
        },

        async fabricImageToPngBlob(obj) {
            if (!isFabricImage(obj)) {
                throw new Error('Objeto não é uma imagem.');
            }

            // Bitmap original (melhor qualidade p/ rembg) — evita canvas “tainted” do toDataURL em alguns casos.
            const el = typeof obj.getElement === 'function' ? obj.getElement() : (obj._element || null);
            if (el && (el.naturalWidth || el.width)) {
                const c = document.createElement('canvas');
                c.width = el.naturalWidth || el.width;
                c.height = el.naturalHeight || el.height;
                const ctx = c.getContext('2d');
                ctx.drawImage(el, 0, 0);
                const blob = await new Promise((resolve) => c.toBlob(resolve, 'image/png'));
                if (blob) {
                    return blob;
                }
            }

            const dataUrl = obj.toDataURL({ format: 'png', multiplier: 1 });
            const res = await fetch(dataUrl);
            return res.blob();
        },

        seedImageStudioFromEmbedded(meta = {}) {
            this.seedImageStudioPresetsFromEmbedded();
            let fonts = meta.imageStudioFonts;
            let icons = meta.imageStudioIconGlyphs;
            let iconFonts = meta.imageStudioIconFonts;
            let elements = meta.imageStudioElements;
            let elementGroups = meta.imageStudioElementGroups;

            if (!Array.isArray(fonts) || !fonts.length) {
                fonts = ImageStudioEngine.readEmbeddedJson('criasys-image-studio-fonts');
            }
            if (!Array.isArray(icons) || !icons.length) {
                icons = ImageStudioEngine.readEmbeddedJson('criasys-image-studio-icons');
            }
            if (!Array.isArray(iconFonts) || !iconFonts.length) {
                iconFonts = ImageStudioEngine.readEmbeddedJson('criasys-image-studio-icon-fonts');
            }
            if (!Array.isArray(elements) || !elements.length) {
                elements = ImageStudioEngine.readEmbeddedJson('criasys-image-studio-elements');
            }
            if (!elementGroups || !Object.keys(elementGroups).length) {
                elementGroups = ImageStudioEngine.readEmbeddedJson('criasys-image-studio-element-groups');
            }

            if (Array.isArray(fonts) && fonts.length) {
                this.imageStudioFonts = fonts;
            } else if (!this.imageStudioFonts?.length) {
                this.imageStudioFonts = FALLBACK_FONTS;
            }
            if (Array.isArray(icons) && icons.length) {
                this.imageStudioIconGlyphs = icons;
            }
            if (Array.isArray(elements) && elements.length) {
                this.imageStudioElements = elements;
            }
            if (elementGroups && typeof elementGroups === 'object') {
                this.imageStudioElementGroups = elementGroups;
            }
            this.syncImageStudioElementsCatalog();
            this.buildImageStudioFontMap();
            preloadIconFontCdns(iconFonts || []);
            preloadStarterGoogleFonts(this.imageStudioFonts);
            if (!this.imageStudioFontMap[this.imageStudioTextFontSlug]) {
                this.imageStudioTextFontSlug = this.imageStudioFonts[0]?.slug || 'bebas_neue';
            }
        },

        imageStudioFilterFontList() {
            const q = (this.imageStudioFontFilter || '').trim().toLowerCase();
            const list = this.$refs.imageStudioFontList;
            if (!list) {
                return;
            }
            list.querySelectorAll('.is-font-row').forEach((btn) => {
                const label = (btn.dataset.fontLabel || '').toLowerCase();
                const group = (btn.dataset.fontGroup || '').toLowerCase();
                const slug = (btn.dataset.fontSlug || '').toLowerCase();
                const show = !q || label.includes(q) || group.includes(q) || slug.includes(q);
                btn.style.display = show ? '' : 'none';
            });
        },

        async loadImageStudioCatalog() {
            this.seedImageStudioPresetsFromEmbedded();
            try {
                const { data } = await api.get('/image-studio/catalog');
                if (data.presets?.length) {
                    this.imageStudioPresets = data.presets;
                }
                this.imageStudioGroups = data.groups || {};
                this.imageStudioExportFormats = data.export_formats || [];
                this.imageStudioTemplates = normalizeTemplatesList(data.templates || []);
                this.imageStudioPacks = data.packs || this.imageStudioPacks || [];
                this.imageStudioPackCategories = data.pack_categories || this.imageStudioPackCategories || [];
                this.imageStudioBrand = data.brand || this.imageStudioBrand || null;
                this.imageStudioElements = this.normalizeImageStudioElementList(data.elements);
                this.imageStudioElementGroups = data.element_groups || {};
                this.imageStudioFonts = data.fonts?.length ? data.fonts : FALLBACK_FONTS;
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
                if (data.defaults?.preset) {
                    this.imageStudioPreset = 'custom';
                }
                if (data.defaults?.width) {
                    this.imageStudioCustomWidth = data.defaults.width;
                }
                if (data.defaults?.height) {
                    this.imageStudioCustomHeight = data.defaults.height;
                }
                if (!this.imageStudioFontMap[this.imageStudioTextFontSlug]) {
                    this.imageStudioTextFontSlug = this.imageStudioFonts[0]?.slug || 'bebas_neue';
                }
                this.syncImageStudioElementsCatalog();
            } catch (e) {
                console.error('Image Studio catalog:', e);
                if (!this.imageStudioPresets?.length) {
                    this.imageStudioPresets = FALLBACK_PRESETS;
                }
                if (!this.imageStudioFonts?.length) {
                    this.imageStudioFonts = FALLBACK_FONTS;
                    this.buildImageStudioFontMap();
                }
                this.error = e.response?.data?.message || 'Catálogo remoto indisponível — formatos locais carregados.';
            }
        },

        async initImageStudio() {
            installStudioRangeScrollLock();
            if (!this.imageStudioElements?.length) {
                const embedded = ImageStudioEngine.readEmbeddedJson('criasys-image-studio-elements');
                if (Array.isArray(embedded) && embedded.length) {
                    this.imageStudioElements = embedded;
                }
                const embeddedGroups = ImageStudioEngine.readEmbeddedJson('criasys-image-studio-element-groups');
                if (embeddedGroups && typeof embeddedGroups === 'object') {
                    this.imageStudioElementGroups = embeddedGroups;
                }
                const embeddedIcons = ImageStudioEngine.readEmbeddedJson('criasys-image-studio-icons');
                if (Array.isArray(embeddedIcons) && embeddedIcons.length) {
                    this.imageStudioIconGlyphs = embeddedIcons;
                }
                this.syncImageStudioElementsCatalog();
            }

            await this.loadImageStudioCatalog();
            if (this.imageStudioReady && this.imageStudioEngine?.canvas) {
                this.imageStudioEngine.setScaleWrapper(this.$refs.imageStudioCanvasScaler);
                this.imageStudioEngine.setFormatGuidesVisible(this.imageStudioShowFormatGuides);
                this.bindImageStudioEngineOnChange();
                this.refreshImageStudioLayers();
                this.fitImageStudioCanvas();
                return;
            }
            await this.$nextTick();

            const el = this.$refs.imageStudioCanvas;
            if (!el) {
                return;
            }

            if (!this.imageStudioEngine) {
                this.imageStudioEngine = new ImageStudioEngine(el);
            }

            this.imageStudioEngine.setScaleWrapper(this.$refs.imageStudioCanvasScaler);
            this.imageStudioEngine.setFormatGuidesVisible(this.imageStudioShowFormatGuides);
            this.imageStudioEngine.bgRemovalDriver = this.imageStudioBgRemovalDriver
                || document.querySelector('meta[name="studio-bg-driver"]')?.getAttribute('content')
                || 'rembg';
            this.imageStudioEngine.bgRemovalUrl = document.querySelector('meta[name="studio-remove-bg-url"]')?.getAttribute('content') || null;
            this.bindImageStudioEngineOnChange();

            await this.loadImageStudioDesign();

            if (!this.imageStudioEngine?.canvas) {
                const p = this.resolveImageStudioPresetMeta();
                this.imageStudioEngine.init(p.width, p.height, this.imageStudioBgColor);
                this.imageStudioEngine.setBackgroundColor(this.imageStudioBgColor, this.imageStudioBgTransparency);
            }

            this.syncImageStudioBackgroundFromEngine();
            this.syncImageStudioUnderlayFromEngine();
            this.syncImageStudioUnderlayToEngine();
            this.imageStudioEngine.pushHistory();
            this.imageStudioReady = true;
            this.setupImageStudioKeyboard();
            this.setupImageStudioContextMenu();
            this.setupImageStudioLocalWatch();
            this.setupImageStudioWheelZoom();
            this.fitImageStudioCanvas();
            this.syncImageStudioCustomDimensionsFromEngine();
        },

        async loadImageStudioDesign() {
            let w = this.imageStudioCustomWidth || 1920;
            let h = this.imageStudioCustomHeight || 1080;
            let canvasJson = null;
            try {
                const { data } = await api.get(`/projects/${this.projectId}/image-studio`, {
                    params: { preset: 'custom' },
                });
                if (data.width && data.height) {
                    w = data.width;
                    h = data.height;
                }
                canvasJson = data.canvas;
                this.imageStudioPreset = 'custom';
            } catch (e) {
                this.error = e.response?.data?.message || null;
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
            this.imageStudioEngine.setSize(w, h);
            this.syncImageStudioCustomDimensionsFromEngine();
            this.syncImageStudioBackgroundFromEngine();
            this.syncImageStudioUnderlayFromEngine();
            this.syncImageStudioUnderlayToEngine();
            this.refreshImageStudioLayers();
        },

        syncImageStudioBackgroundFromEngine() {
            const bg = this.imageStudioEngine?.getBackgroundState();
            if (!bg) {
                return;
            }
            this.imageStudioBgColor = bg.color;
            this.imageStudioBgTransparency = bg.transparency;
        },

        syncImageStudioUnderlayFromEngine() {
            const underlay = this.imageStudioEngine?.getUnderlayState();
            if (!underlay) {
                return;
            }
            this.imageStudioUnderlaySlideIndex = underlay.slideIndex ?? -1;
            this.imageStudioUnderlayEnabled = underlay.enabled !== false;
        },

        syncImageStudioUnderlayToEngine() {
            const slideIndex = Number(this.imageStudioUnderlaySlideIndex) >= 0
                ? Number(this.imageStudioUnderlaySlideIndex)
                : null;
            this.imageStudioEngine?.setUnderlayState(
                slideIndex,
                this.imageStudioUnderlayEnabled
            );
        },

        resolveImageStudioUnderlaySlideIndex() {
            if (Number(this.imageStudioUnderlaySlideIndex) >= 0) {
                const idx = Number(this.imageStudioUnderlaySlideIndex);
                return Math.max(0, Math.min((this.slides?.length || 1) - 1, Number.isFinite(idx) ? idx : 0));
            }
            const selectedIdx = (this.slides || []).findIndex((s) => s.id === this.selectedSlide?.id);
            return selectedIdx >= 0 ? selectedIdx : 0;
        },

        resolveImageStudioUnderlaySlide() {
            const idx = this.resolveImageStudioUnderlaySlideIndex();
            return this.slides?.[idx] ?? null;
        },

        getImageStudioUnderlayImageUrl() {
            if (!this.imageStudioUnderlayEnabled) {
                return null;
            }
            const slide = this.resolveImageStudioUnderlaySlide();
            return slide?.image_url || null;
        },

        getImageStudioUnderlayVideoUrl() {
            if (!this.imageStudioUnderlayEnabled) {
                return null;
            }
            const slide = this.resolveImageStudioUnderlaySlide();
            if (slide?.image_url) {
                return null;
            }
            return slide?.video_url || null;
        },

        imageStudioShowUnderlayMedia() {
            return this.imageStudioUnderlayEnabled
                && !!(this.getImageStudioUnderlayImageUrl() || this.getImageStudioUnderlayVideoUrl());
        },

        buildImageStudioExportOptions(format = null) {
            const slide = this.resolveImageStudioUnderlaySlide();
            const underlayUrl = this.imageStudioUnderlayEnabled
                ? (slide?.image_url || slide?.video_url || null)
                : null;
            const pngLike = format === 'png' || format === 'png_zip' || format === 'zip';
            const transparency = Number(this.imageStudioBgTransparency) || 0;

            return {
                underlayUrl,
                underlayIsVideo: !!(underlayUrl && slide?.video_url && !slide?.image_url),
                omitCanvasBackground: pngLike && transparency >= 100,
            };
        },

        syncImageStudioBackgroundBeforeExport() {
            if (!this.imageStudioEngine) {
                return;
            }
            this.imageStudioEngine.setBackgroundColor(
                this.imageStudioBgColor,
                this.imageStudioBgTransparency,
            );
        },

        ensureImageStudioDeck() {
            if (!Array.isArray(this.imageStudioDeckPages) || !this.imageStudioDeckPages.length) {
                this.imageStudioDeckPages = [this.newImageStudioDeckPage(this.imageStudioDeckPageLabel(0))];
                this.imageStudioDeckPageIndex = 0;
            }
            if (this.imageStudioDeckPageIndex < 0 || this.imageStudioDeckPageIndex >= this.imageStudioDeckPages.length) {
                this.imageStudioDeckPageIndex = 0;
            }
        },

        imageStudioDeckKindDefs() {
            return [
                {
                    id: 'presentation',
                    label: 'Apresentação',
                    hint: 'PowerPoint / PDF',
                    unit: 'Slide',
                    zipPrefix: 'slide',
                    presets: [
                        { slug: 'ppt_16_9_hd', label: '16:9 Full HD' },
                        { slug: 'ppt_16_9', label: '16:9 1280' },
                        { slug: 'ppt_4_3', label: '4:3 clássico' },
                    ],
                },
                {
                    id: 'social',
                    label: 'Redes sociais',
                    hint: 'Carrossel IG / LinkedIn',
                    unit: 'Card',
                    zipPrefix: 'card',
                    presets: [
                        { slug: 'ig_carousel_square', label: 'IG 1:1' },
                        { slug: 'ig_carousel_portrait', label: 'IG 4:5' },
                        { slug: 'li_carousel', label: 'LinkedIn' },
                        { slug: 'ig_feed_square', label: 'Feed 1:1' },
                    ],
                },
                {
                    id: 'web',
                    label: 'Carrossel web',
                    hint: 'Frames do site',
                    unit: 'Frame',
                    zipPrefix: 'frame',
                    presets: [
                        { slug: 'web_carousel_hd', label: 'Site 16:9' },
                        { slug: 'web_carousel_wide', label: 'Site wide' },
                        { slug: 'web_carousel_card', label: 'Site card' },
                        { slug: 'web_hero', label: 'Hero 1920' },
                    ],
                },
            ];
        },

        imageStudioDeckKindMeta() {
            return this.imageStudioDeckKindDefs().find((k) => k.id === this.imageStudioDeckKind)
                || this.imageStudioDeckKindDefs()[0];
        },

        imageStudioDeckUnitLabel() {
            return this.imageStudioDeckKindMeta()?.unit || 'Slide';
        },

        imageStudioDeckPageLabel(index = 0) {
            return `${this.imageStudioDeckUnitLabel()} ${Number(index) + 1}`;
        },

        imageStudioDeckZipPrefix() {
            return this.imageStudioDeckKindMeta()?.zipPrefix || 'frame';
        },

        imageStudioDeckIsAutoName(name) {
            return !name || /^(Slide|Card|Frame)\s+\d+(\s*\(cópia\))?$/i.test(String(name).trim());
        },

        async setImageStudioDeckKind(kind) {
            const next = this.imageStudioDeckKindDefs().find((k) => k.id === kind);
            if (!next) {
                return;
            }
            this.imageStudioDeckKind = next.id;
            this.ensureImageStudioDeck();
            this.imageStudioDeckPages.forEach((page, idx) => {
                if (this.imageStudioDeckIsAutoName(page.name)) {
                    page.name = this.imageStudioDeckPageLabel(idx);
                }
            });
            const firstPreset = next.presets?.[0]?.slug;
            if (firstPreset) {
                await this.switchImageStudioPreset?.(firstPreset);
            }
            this.scheduleImageStudioSave?.();
            this.message = `Sequência: ${next.label} — exporte ZIP, PDF ou PPTX.`;
        },

        newImageStudioDeckPage(name = null, canvas = null) {
            const n = (this.imageStudioDeckPages?.length || 0);

            return {
                id: `slide-${Date.now()}-${Math.random().toString(36).slice(2, 7)}`,
                name: name || this.imageStudioDeckPageLabel(n),
                canvas,
            };
        },

        flushImageStudioDeckPage() {
            if (!this.imageStudioEngine?.canvas) {
                return;
            }
            this.ensureImageStudioDeck();
            const idx = this.imageStudioDeckPageIndex;
            const page = this.imageStudioDeckPages[idx];
            if (!page) {
                return;
            }
            const json = this.imageStudioEngine.toJSON();
            json.width = this.imageStudioEngine.designWidth;
            json.height = this.imageStudioEngine.designHeight;
            page.canvas = json;
        },

        async loadImageStudioDeckPage(index) {
            this.ensureImageStudioDeck();
            const page = this.imageStudioDeckPages[index];
            if (!page || !this.imageStudioEngine) {
                return;
            }
            this.imageStudioDeckPageIndex = index;
            if (page.canvas) {
                try {
                    await this.imageStudioEngine.loadFromJSON(page.canvas);
                } catch {
                    this.imageStudioEngine.canvas?.clear();
                    this.imageStudioEngine.setBackgroundColor(this.imageStudioBgColor, this.imageStudioBgTransparency);
                }
            } else {
                this.imageStudioEngine.canvas?.discardActiveObject?.();
                this.imageStudioEngine.canvas?.clear();
                this.imageStudioEngine.setBackgroundColor(this.imageStudioBgColor, this.imageStudioBgTransparency);
                this.imageStudioEngine.pushHistory?.();
            }
            this.refreshImageStudioLayers?.();
            this.$nextTick?.(() => this.fitImageStudioCanvas?.());
        },

        async selectImageStudioDeckPage(index) {
            this.ensureImageStudioDeck();
            if (index === this.imageStudioDeckPageIndex || this.imageStudioDeckBusy) {
                return;
            }
            if (index < 0 || index >= this.imageStudioDeckPages.length) {
                return;
            }
            this.imageStudioDeckBusy = true;
            try {
                this.flushImageStudioDeckPage();
                await this.loadImageStudioDeckPage(index);
                this.scheduleImageStudioSave?.();
            } finally {
                this.imageStudioDeckBusy = false;
            }
        },

        async imageStudioDeckAddPage() {
            if (this.imageStudioDeckBusy) {
                return;
            }
            this.imageStudioDeckBusy = true;
            try {
                this.ensureImageStudioDeck();
                this.flushImageStudioDeckPage();
                this.imageStudioDeckPages.push(this.newImageStudioDeckPage());
                await this.loadImageStudioDeckPage(this.imageStudioDeckPages.length - 1);
                this.scheduleImageStudioSave?.();
                this.message = `${this.imageStudioDeckPageLabel(this.imageStudioDeckPages.length - 1)} adicionado.`;
            } finally {
                this.imageStudioDeckBusy = false;
            }
        },

        async imageStudioDeckDuplicatePage() {
            if (this.imageStudioDeckBusy) {
                return;
            }
            this.imageStudioDeckBusy = true;
            try {
                this.ensureImageStudioDeck();
                this.flushImageStudioDeckPage();
                const src = this.imageStudioDeckPages[this.imageStudioDeckPageIndex];
                const copyCanvas = src?.canvas ? JSON.parse(JSON.stringify(src.canvas)) : null;
                const page = this.newImageStudioDeckPage(`${src?.name || this.imageStudioDeckUnitLabel()} (cópia)`, copyCanvas);
                const insertAt = this.imageStudioDeckPageIndex + 1;
                this.imageStudioDeckPages.splice(insertAt, 0, page);
                await this.loadImageStudioDeckPage(insertAt);
                this.scheduleImageStudioSave?.();
                this.message = `${this.imageStudioDeckUnitLabel()} duplicado.`;
            } finally {
                this.imageStudioDeckBusy = false;
            }
        },

        async imageStudioDeckDeletePage() {
            this.ensureImageStudioDeck();
            if (this.imageStudioDeckPages.length <= 1 || this.imageStudioDeckBusy) {
                return;
            }
            if (!confirm(`Excluir este ${this.imageStudioDeckUnitLabel().toLowerCase()} da sequência?`)) {
                return;
            }
            this.imageStudioDeckBusy = true;
            try {
                const idx = this.imageStudioDeckPageIndex;
                this.imageStudioDeckPages.splice(idx, 1);
                this.imageStudioDeckPages.forEach((page, i) => {
                    if (this.imageStudioDeckIsAutoName(page.name)) {
                        page.name = this.imageStudioDeckPageLabel(i);
                    }
                });
                const next = Math.min(idx, this.imageStudioDeckPages.length - 1);
                await this.loadImageStudioDeckPage(next);
                this.scheduleImageStudioSave?.();
                this.message = `${this.imageStudioDeckUnitLabel()} excluído.`;
            } finally {
                this.imageStudioDeckBusy = false;
            }
        },

        async imageStudioDeckMovePage(delta) {
            this.ensureImageStudioDeck();
            if (this.imageStudioDeckBusy) {
                return;
            }
            this.flushImageStudioDeckPage();
            const from = this.imageStudioDeckPageIndex;
            const to = from + delta;
            if (to < 0 || to >= this.imageStudioDeckPages.length) {
                return;
            }
            const pages = this.imageStudioDeckPages;
            const [item] = pages.splice(from, 1);
            pages.splice(to, 0, item);
            this.imageStudioDeckPageIndex = to;
            this.scheduleImageStudioSave?.();
        },

        async collectImageStudioDeckExportUrls(kind = 'png') {
            this.ensureImageStudioDeck();
            this.flushImageStudioDeckPage();
            const restoreIdx = this.imageStudioDeckPageIndex;
            const exportFormat = kind === 'jpg' ? 'jpg' : 'png';
            this.syncImageStudioBackgroundBeforeExport();
            const opts = this.buildImageStudioExportOptions(exportFormat);
            const urls = [];
            for (let i = 0; i < this.imageStudioDeckPages.length; i += 1) {
                await this.loadImageStudioDeckPage(i);
                const url = await this.imageStudioEngine.exportDesignDataUrl(
                    kind === 'jpg' ? 'jpg' : 'png',
                    0.92,
                    null,
                    true,
                    opts,
                );
                urls.push(url);
            }
            await this.loadImageStudioDeckPage(restoreIdx);

            return urls;
        },

        onImageStudioUnderlayChange() {
            this.syncImageStudioUnderlayToEngine();
            this.scheduleImageStudioSave();
        },

        fitImageStudioCanvas() {
            const wrap = this.$refs.imageStudioCanvasWrap;
            if (!wrap || !this.imageStudioEngine?.canvas) {
                return;
            }
            if (wrap.clientWidth < 8 || wrap.clientHeight < 8) {
                requestAnimationFrame(() => this.fitImageStudioCanvas());
                return;
            }
            const z = this.imageStudioEngine.zoomToFit(wrap.clientWidth, wrap.clientHeight);
            this.imageStudioZoom = Math.round(z * 100);
            this.$nextTick(() => {
                this.imageStudioEngine?.canvas?.calcOffset();
            });
        },

        imageStudioSetZoomPercent(percent) {
            if (!this.imageStudioEngine?.canvas) {
                return;
            }
            const z = this.imageStudioEngine.applyViewportZoom(percent / 100);
            this.imageStudioZoom = Math.round(z * 100);
            this.$nextTick(() => {
                this.imageStudioEngine?.canvas?.calcOffset();
            });
        },

        imageStudioZoomIn() {
            this.imageStudioSetZoomPercent(Math.min(400, this.imageStudioZoom + 10));
        },

        imageStudioZoomOut() {
            this.imageStudioSetZoomPercent(Math.max(8, this.imageStudioZoom - 10));
        },

        imageStudioZoomReset() {
            this.imageStudioSetZoomPercent(100);
        },

        setupImageStudioWheelZoom() {
            const wrap = this.$refs.imageStudioCanvasWrap;
            if (!wrap || wrap._criasysWheelZoom) {
                return;
            }
            wrap._criasysWheelZoom = true;
            wrap.addEventListener('wheel', (e) => {
                if (!this.isImageStudioViewportActive()) {
                    return;
                }
                e.preventDefault();
                const delta = e.deltaY > 0 ? -8 : 8;
                this.imageStudioSetZoomPercent(this.imageStudioZoom + delta);
            }, { passive: false });

            if (!wrap._criasysResizeFit) {
                wrap._criasysResizeFit = true;
                const ro = new ResizeObserver(() => {
                    if (this.isImageStudioViewportActive()) {
                        this.fitImageStudioCanvas();
                    }
                });
                ro.observe(wrap);
            }
        },

        onImageStudioFormatGuidesChange() {
            this.imageStudioEngine?.setFormatGuidesVisible(this.imageStudioShowFormatGuides);
        },

        refreshImageStudioLayers() {
            this.imageStudioLayers = this.imageStudioEngine?.getLayers() || [];
            this.syncImageStudioGroupBagFromCanvas();
            if (this._imageStudioGroupBag?.length >= 2) {
                const bag = new Set(this._imageStudioGroupBag);
                this.imageStudioLayers = this.imageStudioLayers.map((layer) => ({
                    ...layer,
                    active: !!(layer.active || bag.has(layer.object)),
                }));
            }
            // Sempre o objeto ativo real do Fabric (não o texto aninhado de uma seleção).
            const active = this.imageStudioEngine?.getActiveObject() || null;
            const usable = active && !active.criasysGuide && !active.criasysCropGuide ? active : null;

            if (usable) {
                this._imageStudioStickyObject = usable;
            } else if (this._imageStudioStickyObject) {
                const canvas = this.imageStudioEngine?.canvas;
                const stillThere = !!canvas?.getObjects?.().some((o) => o === this._imageStudioStickyObject);
                if (!stillThere) {
                    this._imageStudioStickyObject = null;
                }
            }

            const raw = usable || this._imageStudioStickyObject || null;
            this._imageStudioActiveObject = raw;
            this.imageStudioSelectedObject = raw
                ? { type: normalizeFabricType(raw), opacity: raw.opacity ?? 1 }
                : null;

            const activeLayer = (this.imageStudioLayers || []).find((layer) => layer.active)
                || (this.imageStudioLayers || []).find((layer) => layer.object === raw)
                || null;
            this.imageStudioActiveLayerId = activeLayer?.id || null;
            this.imageStudioActiveLayerName = activeLayer?.name || (raw?.name || '');

            // Com várias camadas, ao clicar na prancheta abre Camadas e destaca a linha.
            // Não tira o usuário do painel Texto/Mídia se o tipo bate com o que está editando.
            if (usable && this.imageStudioSidebarTab !== 'layers' && !this._imageStudioSelectingFromLayersPanel) {
                const layersCount = this.imageStudioLayers?.length || 0;
                const stayOnText = this.imageStudioSidebarTab === 'text' && isFabricText(usable);
                const stayOnMedia = this.imageStudioSidebarTab === 'media' && isFabricImage(usable);
                if (layersCount >= 2 && !stayOnText && !stayOnMedia) {
                    this.setImageStudioSidebarTab?.('layers');
                }
            }

            if (raw && isFabricText(raw)) {
                this._syncingTextUi = true;
                const st = this.imageStudioEngine.getTextStyleFromObject(raw);
                if (st) {
                    this.imageStudioTextFontSlug = st.fontSlug;
                    this.imageStudioTextContent = st.content;
                    this.imageStudioTextSize = st.fontSize;
                    this.imageStudioTextFill = st.fill;
                    this.imageStudioTextStroke = st.stroke;
                    this.imageStudioTextStrokeWidth = st.strokeWidth;
                    this.imageStudioTextBold = st.bold;
                    this.imageStudioTextItalic = st.italic;
                    this.imageStudioTextUnderline = st.underline;
                    this.imageStudioTextLinethrough = st.linethrough;
                    this.imageStudioTextAlign = st.align;
                    this.imageStudioTextLineHeight = st.lineHeight;
                    this.imageStudioTextCharSpacing = st.charSpacing;
                    this.imageStudioTextShadow = st.shadow;
                    this.imageStudioTextShadowColor = st.shadowColor;
                    this.imageStudioTextShadowBlur = st.shadowBlur;
                }
                this.$nextTick(() => {
                    this._syncingTextUi = false;
                });
            }
            this.imageStudioObjectScale = this.imageStudioEngine?.getActiveObjectScalePercent() ?? 100;
            this.imageStudioObjectAngle = this.imageStudioEngine?.getActiveObjectAngle() ?? 0;
            this.imageStudioCanUndo = this.imageStudioEngine?.canUndo() ?? false;
            this.imageStudioCanRedo = this.imageStudioEngine?.canRedo() ?? false;
            if (raw && isFabricImage(raw)) {
                this.imageStudioFilters = this.imageStudioEngine.getFilterState(raw);
            }
            if (raw && isRecolorableObject(raw)) {
                const shapeStyle = this.imageStudioEngine.getShapeStyleFromObject(raw);
                if (shapeStyle) {
                    this.imageStudioShapeFill = shapeStyle.fill || '#ffffff';
                    this.imageStudioShapeStroke = shapeStyle.stroke || '#ffffff';
                    this.imageStudioShapeStrokeWidth = shapeStyle.strokeWidth;
                    this.imageStudioShapeIsLine = shapeStyle.isLine;
                    this.imageStudioFillMode = shapeStyle.fillMode || 'solid';
                    this.imageStudioGradientColorA = shapeStyle.gradientColorA || shapeStyle.fill || '#0d9488';
                    this.imageStudioGradientColorB = shapeStyle.gradientColorB || '#0f172a';
                    this.imageStudioGradientAngle = shapeStyle.gradientAngle ?? 90;
                }
            }
            this.imageStudioCanUngroup = this.imageStudioEngine?.canUngroupActiveObject?.() ?? false;
            this.imageStudioCanRecolorSelection = !!(raw && isRecolorableObject(raw));
            // NÃO scrollIntoView aqui — joga a sidebar inteira e estraga o arraste dos sliders.
        },

        /**
         * Só ajusta scroll DENTRO da lista de camadas (nunca dos pais).
         * Usar só ao escolher outra camada — nunca após slider.
         */
        scrollImageStudioActiveLayerIntoView() {
            if (this._imageStudioControlDragging || this._imageStudioSkipLayerScroll) {
                return;
            }
            const root = this.$refs?.imageStudioLayersList;
            if (!root) {
                return;
            }
            const el = root.querySelector('[data-layer-active="1"]');
            if (!el) {
                return;
            }
            const rootRect = root.getBoundingClientRect();
            const elRect = el.getBoundingClientRect();
            if (elRect.top < rootRect.top) {
                root.scrollTop -= rootRect.top - elRect.top;
            } else if (elRect.bottom > rootRect.bottom) {
                root.scrollTop += elRect.bottom - rootRect.bottom;
            }
        },

        bindImageStudioEngineOnChange() {
            if (!this.imageStudioEngine) {
                return;
            }
            this.imageStudioEngine.onChange = () => {
                if (this._imageStudioControlDragging || this._imageStudioSkipLayerScroll) {
                    return;
                }
                this.refreshImageStudioLayers();
                this.scheduleImageStudioSave();
            };
        },

        imageStudioBeginControlDrag(event) {
            this._imageStudioControlDragging = true;
            this._imageStudioSkipLayerScroll = true;
            if (this.imageStudioEngine) {
                this.imageStudioEngine.historyPaused = true;
            }
            const target = event?.target;
            if (target) {
                try {
                    target.setPointerCapture?.(event.pointerId);
                } catch {
                    /* ignore */
                }
                lockStudioScrollDuringDrag(target);
            }
        },

        imageStudioEndControlDrag() {
            if (!this._imageStudioControlDragging) {
                return;
            }
            this._imageStudioControlDragging = false;
            if (this.imageStudioEngine) {
                this.imageStudioEngine.historyPaused = false;
                this.imageStudioEngine.pushHistory();
            }
            // Atualiza só números — sem refresh/scroll da lista (era isso que te matava de raiva)
            this.imageStudioObjectScale = this.imageStudioEngine?.getActiveObjectScalePercent() ?? 100;
            this.imageStudioObjectAngle = this.imageStudioEngine?.getActiveObjectAngle() ?? 0;
            const raw = this._imageStudioActiveObject;
            if (raw) {
                this.imageStudioSelectedObject = {
                    type: normalizeFabricType(raw),
                    opacity: raw.opacity ?? 1,
                };
            }
            this.scheduleImageStudioSave?.();
            unlockStudioScrollDuringDrag();
            // Libera a trava no próximo tick, depois de qualquer re-render residual
            this.$nextTick?.(() => {
                this._imageStudioSkipLayerScroll = false;
            });
        },

        /**
         * Snapshot da multi-seleção — o clique no painel limpa o Fabric antes do @click.
         * Usar sempre este bag em Agrupar (mousedown.prevent).
         */
        syncImageStudioGroupBagFromCanvas() {
            const engine = this.imageStudioEngine;
            if (!engine?.canvas) {
                this._imageStudioGroupBag = [];
                this.imageStudioGroupBagCount = 0;
                this.imageStudioCanGroup = false;
                return;
            }
            const live = engine.resolveObjectsForGrouping?.() || [];
            // Se o canvas ainda tem multi-seleção, atualiza o bag.
            // Se o clique no painel zerar a seleção, mantém o bag anterior (>=2).
            if (live.length >= 1) {
                this._imageStudioGroupBag = live;
            } else if (!Array.isArray(this._imageStudioGroupBag)) {
                this._imageStudioGroupBag = [];
            } else {
                // Limpa refs mortas
                const canvasObjs = engine.canvas.getObjects?.() || [];
                this._imageStudioGroupBag = this._imageStudioGroupBag.filter((o) => canvasObjs.includes(o));
            }
            this.imageStudioGroupBagCount = this._imageStudioGroupBag.length;
            this.imageStudioCanGroup = this.imageStudioGroupBagCount >= 2
                || (engine.canGroupActiveSelection?.(this._imageStudioGroupBag) ?? false);
            this.imageStudioCanUngroup = engine.canUngroupActiveObject?.() ?? false;
        },

        imageStudioOnShapeFillChange() {
            if (this._imageStudioSkipShapePaint) {
                return;
            }
            const obj = this.imageStudioEngine?.getActiveObject();
            if (!isRecolorableObject(obj)) {
                return;
            }
            this.imageStudioEngine.applyShapePaint(obj, {
                fill: this.imageStudioShapeFill,
                fillMode: this.imageStudioFillMode || 'solid',
                gradientColorA: this.imageStudioGradientColorA,
                gradientColorB: this.imageStudioGradientColorB,
                gradientAngle: this.imageStudioGradientAngle,
                stroke: this.imageStudioShapeStroke,
                strokeWidth: this.imageStudioShapeStrokeWidth,
                isLine: this.imageStudioShapeIsLine,
            });
        },

        imageStudioSetFillMode(mode) {
            const next = ['solid', 'linear', 'radial'].includes(mode) ? mode : 'solid';
            this.imageStudioFillMode = next;
            if (next !== 'solid' && !this.imageStudioGradientColorA) {
                this.imageStudioGradientColorA = this.imageStudioShapeFill || '#0d9488';
            }
            this.imageStudioOnShapeFillChange();
        },

        imageStudioOnGradientChange() {
            if (this.imageStudioFillMode === 'solid') {
                this.imageStudioFillMode = 'linear';
            }
            this.imageStudioOnShapeFillChange();
        },

        imageStudioOnShapeStrokeChange() {
            this.imageStudioOnShapeFillChange();
        },

        imageStudioOnShapeStrokeWidthChange() {
            if (this.imageStudioShapeStrokeWidth > 0 && !this.imageStudioShapeStroke) {
                this.imageStudioShapeStroke = '#ffffff';
            }
            this.imageStudioOnShapeFillChange();
        },

        imageStudioClearShapeFill() {
            this.imageStudioFillMode = 'solid';
            this.imageStudioShapeFill = '';
            this.imageStudioOnShapeFillChange();
        },

        imageStudioGroupSelection() {
            this.syncImageStudioGroupBagFromCanvas();
            const bag = Array.isArray(this._imageStudioGroupBag) ? [...this._imageStudioGroupBag] : [];
            // Evita o painel de cor disparar paint no meio do agrupamento
            this._imageStudioSkipShapePaint = true;
            const group = this.imageStudioEngine?.groupObjects?.(bag.length >= 2 ? bag : null);
            this._imageStudioSkipShapePaint = false;
            if (!group || !isFabricGroup(group)) {
                this.error = 'Selecione 2 ou mais camadas (arraste na prancheta, Shift+clique ou Ctrl+clique na lista)';
                return;
            }
            this._imageStudioGroupBag = [group];
            this.imageStudioGroupBagCount = 1;
            this._imageStudioStickyObject = group;
            this.refreshImageStudioLayers();
            this.scheduleImageStudioSave?.();
            const n = typeof group.size === 'function' ? group.size() : (bag.length || 0);
            this.message = `Grupo com ${n} camadas`;
            this.error = '';
        },

        imageStudioUngroupSelection() {
            const sticky = this._imageStudioStickyObject;
            const selection = this.imageStudioEngine?.ungroupActiveObject?.(
                sticky && normalizeFabricType(sticky) === 'group' ? sticky : null,
            );
            if (!selection) {
                this.error = 'Selecione um grupo para desagrupar';
                return;
            }
            const kids = typeof selection.getObjects === 'function' ? selection.getObjects() : [];
            this._imageStudioGroupBag = [...kids];
            this.imageStudioGroupBagCount = kids.length;
            this.refreshImageStudioLayers();
            this.scheduleImageStudioSave?.();
            this.message = 'Grupo desfeito — itens ainda selecionados';
            this.error = '';
        },

        imageStudioSetObjectScale(percent) {
            this.imageStudioEngine?.setActiveObjectScalePercent(percent);
            this.imageStudioObjectScale = Math.round(Number(percent) || 100);
            if (!this._imageStudioControlDragging) {
                this.refreshImageStudioLayers();
            }
        },

        imageStudioNudgeObjectScale(delta) {
            this.imageStudioEngine?.nudgeActiveObjectScale(delta);
            this.imageStudioObjectScale = this.imageStudioEngine?.getActiveObjectScalePercent() ?? 100;
            this.refreshImageStudioLayers();
        },

        imageStudioSetObjectAngle(degrees) {
            this.imageStudioEngine?.setActiveObjectAngle(degrees);
            this.imageStudioObjectAngle = this.imageStudioEngine?.getActiveObjectAngle() ?? 0;
            if (!this._imageStudioControlDragging) {
                this.refreshImageStudioLayers();
            }
        },

        imageStudioNudgeObjectAngle(delta) {
            this.imageStudioEngine?.nudgeActiveObjectAngle(delta);
            this.imageStudioObjectAngle = this.imageStudioEngine?.getActiveObjectAngle() ?? 0;
            this.refreshImageStudioLayers();
        },

        imageStudioApplyFilters() {
            const obj = this.imageStudioEngine?.getActiveObject();
            if (isFabricImage(obj)) {
                this.imageStudioEngine.applyFiltersToObject(obj, this.imageStudioFilters);
            }
        },

        imageStudioClearFilters() {
            const obj = this.imageStudioEngine?.getActiveObject();
            if (isFabricImage(obj)) {
                this.imageStudioEngine.clearFilters(obj);
                this.imageStudioFilters = { ...DEFAULT_FILTER_STATE };
            }
        },

        setupImageStudioKeyboard() {
            if (this._imageStudioKeyHandler) {
                return;
            }
            this._imageStudioKeyHandler = (e) => {
                if (!this.isImageStudioKeyboardActive()) {
                    return;
                }
                // No shell mobile puro, teclado físico ainda funciona; mas não força tool UI.
                const tag = (e.target?.tagName || '').toLowerCase();
                if (tag === 'input' || tag === 'textarea' || tag === 'select' || e.target?.isContentEditable) {
                    return;
                }
                const active = this.imageStudioEngine?.getActiveObject();
                if (active?.isEditing) {
                    return;
                }

                const mod = e.ctrlKey || e.metaKey;
                const shift = e.shiftKey;
                const key = e.key;
                const lower = String(key || '').toLowerCase();

                if (key === 'Escape') {
                    if (this.imageStudioContextMenu?.open) {
                        e.preventDefault();
                        this.closeImageStudioContextMenu();
                        return;
                    }
                    if (this.imageStudioMobileSheetOpen) {
                        e.preventDefault();
                        this.closeImageStudioMobileSheet();
                        return;
                    }
                    if (this.imageStudioExpanded && !this.imageStudioElementsModalOpen && !this.imageStudioDimensionsModalOpen && !this.imageStudioTemplatesModalOpen && !this.imageStudioPacksModalOpen) {
                        e.preventDefault();
                        this.closeImageStudioExpanded();
                        return;
                    }
                    e.preventDefault();
                    this.imageStudioDeselectAll();
                    return;
                }

                // Undo / Redo
                if (mod && lower === 'z' && !shift) {
                    e.preventDefault();
                    this.imageStudioUndo();
                    return;
                }
                if (mod && (lower === 'y' || (lower === 'z' && shift))) {
                    e.preventDefault();
                    this.imageStudioRedo();
                    return;
                }

                // Seleção
                if (mod && lower === 'a') {
                    e.preventDefault();
                    this.imageStudioSelectAll();
                    return;
                }
                if (mod && lower === 'd') {
                    e.preventDefault();
                    this.imageStudioDeselectAll();
                    return;
                }

                // Clipboard + duplicar (Ctrl+J = Photoshop)
                if (mod && lower === 'c') {
                    e.preventDefault();
                    this.imageStudioClipboardCopy();
                    return;
                }
                if (mod && lower === 'x') {
                    e.preventDefault();
                    this.imageStudioClipboardCut();
                    return;
                }
                if (mod && lower === 'v' && !shift) {
                    e.preventDefault();
                    this.imageStudioClipboardPaste();
                    return;
                }
                if (mod && lower === 'j') {
                    e.preventDefault();
                    this.imageStudioDuplicateSelection();
                    return;
                }

                // Agrupar
                if (mod && lower === 'g' && shift) {
                    e.preventDefault();
                    this.imageStudioUngroupSelection();
                    return;
                }
                if (mod && lower === 'g') {
                    e.preventDefault();
                    this.imageStudioGroupSelection();
                    return;
                }

                // Empilhar camadas Ctrl+[ ] / Ctrl+Shift+[ ]
                if (mod && key === '[') {
                    e.preventDefault();
                    this.imageStudioArrangeSelection(shift ? 'bottom' : 'down');
                    return;
                }
                if (mod && key === ']') {
                    e.preventDefault();
                    this.imageStudioArrangeSelection(shift ? 'top' : 'up');
                    return;
                }

                // Zoom
                if (mod && (key === '0' || key === ')')) {
                    e.preventDefault();
                    this.fitImageStudioCanvas();
                    return;
                }
                if (mod && (key === '1' || key === '!')) {
                    e.preventDefault();
                    this.imageStudioZoomReset();
                    return;
                }
                if (mod && (key === '=' || key === '+' || key === 'Add')) {
                    e.preventDefault();
                    this.imageStudioZoomIn();
                    return;
                }
                if (mod && (key === '-' || key === '_' || key === 'Subtract')) {
                    e.preventDefault();
                    this.imageStudioZoomOut();
                    return;
                }

                // Excluir
                if (!mod && (key === 'Delete' || key === 'Backspace')) {
                    if (this.resolveImageStudioActiveObject()) {
                        e.preventDefault();
                        this.imageStudioDeleteSelection();
                    }
                    return;
                }

                // Setas = mover (Shift = 10px)
                if (!mod && ['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(key)) {
                    if (!this.imageStudioEngine?.canvas?.getActiveObject()) {
                        return;
                    }
                    e.preventDefault();
                    const step = shift ? 10 : 1;
                    const dx = key === 'ArrowLeft' ? -step : key === 'ArrowRight' ? step : 0;
                    const dy = key === 'ArrowUp' ? -step : key === 'ArrowDown' ? step : 0;
                    this.imageStudioNudgeSelection(dx, dy);
                    return;
                }

                // Rotação sem Ctrl: [ ]
                if (!mod && this.imageStudioSelectedObject && key === '[') {
                    e.preventDefault();
                    this.imageStudioNudgeObjectAngle(shift ? -15 : -5);
                    return;
                }
                if (!mod && this.imageStudioSelectedObject && key === ']') {
                    e.preventDefault();
                    this.imageStudioNudgeObjectAngle(shift ? 15 : 5);
                }
            };

            window.addEventListener('keydown', this._imageStudioKeyHandler);
        },

        imageStudioSelectAll() {
            this.imageStudioEngine?.selectAllObjects?.();
            this.refreshImageStudioLayers();
            this.message = 'Tudo selecionado (Ctrl+A)';
        },

        imageStudioDeselectAll() {
            this.imageStudioEngine?.discardSelection?.();
            this.refreshImageStudioLayers();
            this.message = 'Seleção limpa (Ctrl+D)';
        },

        imageStudioNudgeSelection(dx, dy) {
            if (this.imageStudioEngine?.nudgeActiveObjects?.(dx, dy)) {
                this.refreshImageStudioLayers();
                this.scheduleImageStudioSave?.();
            }
        },

        imageStudioArrangeSelection(direction) {
            const obj = this.resolveImageStudioActiveObject();
            if (!obj) {
                this.error = 'Selecione um objeto';
                return;
            }
            // ActiveSelection: aplica em cada membro
            const targets = (typeof obj.getObjects === 'function' && String(obj.type || '').toLowerCase().includes('activeselection'))
                ? (obj.getObjects() || [])
                : [obj];
            targets.forEach((item) => this.imageStudioEngine?.moveLayer?.(item, direction));
            this.refreshImageStudioLayers();
            this.scheduleImageStudioSave?.();
            const labels = { up: 'Frente', down: 'Trás', top: 'Topo', bottom: 'Fundo' };
            this.message = `Camada: ${labels[direction] || direction}`;
        },

        async imageStudioClipboardCopy() {
            const items = await this.imageStudioEngine?.serializeActiveObjectsForClipboard?.();
            if (!items?.length) {
                this.error = 'Nada selecionado para copiar';
                return;
            }
            this._imageStudioClipboard = items;
            this.message = items.length > 1 ? `${items.length} objetos copiados (Ctrl+C)` : 'Copiado (Ctrl+C)';
        },

        async imageStudioClipboardCut() {
            await this.imageStudioClipboardCopy();
            if (this._imageStudioClipboard?.length) {
                this.imageStudioDeleteSelection();
                this.message = 'Recortado (Ctrl+X)';
            }
        },

        async imageStudioClipboardPaste() {
            const items = this._imageStudioClipboard || [];
            if (!items.length) {
                this.error = 'Área de transferência vazia';
                return;
            }
            await this.imageStudioEngine?.pasteObjectsFromClipboard?.(items, 28);
            this.refreshImageStudioLayers();
            this.scheduleImageStudioSave?.();
            this.message = 'Colado (Ctrl+V)';
        },

        isImageStudioKeyboardActive() {
            if (!this.imageStudioReady) {
                return false;
            }
            // Painel do blog não usa abas do CriaSys; no host antigo exige a aba ativa.
            if (this.activeTab === undefined || this.activeTab === null || this.projectId === null) {
                return true;
            }

            return this.activeTab === 'image_studio';
        },

        isImageStudioViewportActive() {
            if (!this.imageStudioReady) {
                return false;
            }
            if (typeof this.isImageStudioMobileShell === 'function' && this.isImageStudioMobileShell()) {
                return true;
            }

            return this.isImageStudioKeyboardActive();
        },

        /**
         * Elemento a excluir/duplicar: ativo no Fabric, ou o último selecionado
         * (sticky) — o clique no botão do painel pode limpar a seleção do canvas.
         */
        resolveImageStudioActiveObject() {
            const canvas = this.imageStudioEngine?.canvas;
            if (!canvas) {
                return null;
            }

            const candidates = [
                this.imageStudioEngine.getActiveObject(),
                this._imageStudioStickyObject,
                this._imageStudioActiveObject,
            ];

            for (const obj of candidates) {
                if (!obj || obj.criasysGuide || obj.criasysCropGuide) {
                    continue;
                }
                const type = normalizeFabricType(obj);
                if (type === 'activeselection') {
                    return obj;
                }
                // Aceita se ainda está no canvas OU se ainda é o activeObject
                // (includes falha em alguns casos do Fabric 6 com grupos).
                if (canvas.getActiveObject() === obj) {
                    return obj;
                }
                try {
                    if (canvas.getObjects().some((o) => o === obj)) {
                        return obj;
                    }
                } catch {
                    return obj;
                }
            }

            return null;
        },

        imageStudioDeleteSelection() {
            if (this.imageStudioCropping) {
                this.imageStudioCancelCrop();
                return;
            }

            const canvas = this.imageStudioEngine?.canvas;
            if (!canvas) {
                this.error = 'Canvas não está pronto';
                return;
            }

            const obj = this.resolveImageStudioActiveObject();
            if (!obj) {
                this.error = 'Selecione um elemento no canvas para excluir';
                return;
            }

            try {
                const type = normalizeFabricType(obj);
                if (type === 'activeselection') {
                    const children = typeof obj.getObjects === 'function'
                        ? [...obj.getObjects()]
                        : [];
                    canvas.discardActiveObject();
                    children.forEach((child) => {
                        try { canvas.remove(child); } catch { /* ignore */ }
                    });
                } else {
                    canvas.discardActiveObject();
                    canvas.remove(obj);
                }
            } catch (e) {
                this.error = e?.message || 'Não foi possível excluir o elemento';
                return;
            }

            this._imageStudioStickyObject = null;
            this._imageStudioActiveObject = null;
            this.imageStudioSelectedObject = null;
            canvas.requestRenderAll();
            this.imageStudioEngine.emitChange?.(true);
            this.refreshImageStudioLayers();
            this.closeImageStudioContextMenu();
            this.scheduleImageStudioSave?.();
            this.message = 'Elemento excluído';
            this.error = '';
        },

        imageStudioDeleteLayer(layer) {
            if (layer?.object) {
                this._imageStudioStickyObject = layer.object;
                this._imageStudioActiveObject = layer.object;
                try {
                    this.imageStudioEngine?.selectLayer(layer.object);
                } catch {
                    /* segue com sticky */
                }
            }
            this.imageStudioDeleteSelection();
        },

        openImageStudioContextMenu(event, hasSelection = false) {
            const wrap = this.$refs.imageStudioCanvasWrap;
            const rect = wrap?.getBoundingClientRect?.();
            const x = rect ? event.clientX - rect.left : event.offsetX || 0;
            const y = rect ? event.clientY - rect.top : event.offsetY || 0;
            this.imageStudioContextMenu = {
                open: true,
                x: Math.max(8, x),
                y: Math.max(8, y),
                hasSelection: !!hasSelection,
            };
        },

        closeImageStudioContextMenu() {
            if (this.imageStudioContextMenu?.open) {
                this.imageStudioContextMenu = { ...this.imageStudioContextMenu, open: false };
            }
        },

        setupImageStudioContextMenu() {
            if (this._imageStudioContextBound || !this.imageStudioEngine?.canvas) {
                return;
            }
            const upper = this.imageStudioEngine.canvas.upperCanvasEl;
            if (!upper) {
                return;
            }
            this._imageStudioContextBound = true;
            upper.addEventListener('contextmenu', (e) => {
                e.preventDefault();
                e.stopPropagation();
                let target = null;
                try {
                    target = this.imageStudioEngine.canvas.findTarget(e, false);
                } catch {
                    target = this.imageStudioEngine.getActiveObject();
                }
                if (target && !target.criasysGuide && !target.criasysCropGuide) {
                    this.imageStudioEngine.canvas.setActiveObject(target);
                    this.imageStudioEngine.canvas.requestRenderAll();
                    this.refreshImageStudioLayers();
                    this.openImageStudioContextMenu(e, true);
                } else {
                    this.imageStudioEngine.canvas.discardActiveObject();
                    this.imageStudioEngine.canvas.requestRenderAll();
                    this.refreshImageStudioLayers();
                    this.openImageStudioContextMenu(e, false);
                }
            });
            if (!this._imageStudioContextCloseBound) {
                this._imageStudioContextCloseBound = true;
                document.addEventListener('pointerdown', (ev) => {
                    if (!this.imageStudioContextMenu?.open) {
                        return;
                    }
                    const menu = document.querySelector('.studio-context-menu');
                    if (menu && menu.contains(ev.target)) {
                        return;
                    }
                    this.closeImageStudioContextMenu();
                });
            }
        },

        async imageStudioUndo() {
            const ok = await this.imageStudioEngine?.undo();
            if (ok) {
                this.refreshImageStudioLayers();
            }
        },

        async imageStudioRedo() {
            const ok = await this.imageStudioEngine?.redo();
            if (ok) {
                this.refreshImageStudioLayers();
            }
        },

        onImageStudioGridChange() {
            this.imageStudioEngine?.setGridOptions({
                showGrid: this.imageStudioShowGrid,
                snapToGrid: this.imageStudioSnapGrid,
                gridSize: this.imageStudioGridSize,
            });
        },

        imageStudioAlignObject(mode) {
            this.imageStudioEngine?.alignActiveObject(mode);
            this.refreshImageStudioLayers();
        },

        async imageStudioPickLocalFolder() {
            if (!window.criasys?.pickWatchFolder) {
                this.error = 'Disponível apenas no app desktop (Electron)';
                return;
            }
            const folder = await window.criasys.pickWatchFolder();
            if (!folder) {
                return;
            }
            await window.criasys.watchFolder(folder);
            this.imageStudioLocalWatch = folder;
            this.message = `Monitorando: ${folder}`;
        },

        setupImageStudioLocalWatch() {
            if (!window.criasys?.onFolderChanged || this._imageStudioWatchSetup) {
                return;
            }
            this._imageStudioWatchSetup = true;
            window.criasys.onFolderChanged(async (data) => {
                if (this.activeTab !== 'image_studio' || !data?.filePath) {
                    return;
                }
                const ext = (data.filePath.split('.').pop() || '').toLowerCase();
                if (!['png', 'jpg', 'jpeg', 'webp', 'gif'].includes(ext)) {
                    return;
                }
                if (!window.criasys.readLocalFile) {
                    return;
                }
                try {
                    const file = await window.criasys.readLocalFile(data.filePath);
                    if (file?.dataUrl) {
                        const name = data.filePath.split(/[/\\]/).pop();
                        await this.imageStudioEngine?.addImageFromUrl(file.dataUrl, name);
                        this.refreshImageStudioLayers();
                        this.message = `Importado: ${name}`;
                    }
                } catch {
                    /* arquivo pode ainda estar sendo gravado */
                }
            });
        },

        scheduleImageStudioSave() {
            clearTimeout(this.imageStudioSaveTimeout);
            this.imageStudioSaveTimeout = setTimeout(() => this.saveImageStudioDesign(), 1500);
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
                await api.put(`/projects/${this.projectId}/image-studio`, {
                    preset: 'custom',
                    canvas: json,
                });
            } catch (e) {
                this.error = e.response?.data?.message || 'Erro ao salvar design';
            } finally {
                this.imageStudioSaving = false;
            }
        },

        async ensureImageStudioFormatForThumbnailPlatform() {
            const platform = this.selectedThumbnailPlatform || 'youtube_landscape';
            const presetMap = {
                youtube_landscape: 'yt_thumb_hd',
                youtube_shorts: 'yt_shorts',
                instagram_feed_square: 'ig_feed_square',
                instagram_reels: 'ig_story',
                tiktok: 'tt_video',
            };
            const targetSlug = presetMap[platform];
            if (!targetSlug) {
                return;
            }
            const current = this.resolveImageStudioPresetMeta();
            if (current.width === current.height && platform === 'youtube_landscape') {
                await this.switchImageStudioPreset('yt_thumb_hd');
                return;
            }
            if (this.imageStudioPreset !== targetSlug && !this._imageStudioPresetLocked) {
                await this.switchImageStudioPreset(targetSlug);
            }
        },

        async switchImageStudioPreset(slug) {
            const preset = this.resolveImageStudioPresetMeta(slug);
            if (!preset?.width || !preset?.height) {
                return;
            }

            if (!this.imageStudioEngine?.canvas) {
                await this.initImageStudio();
            }
            if (!this.imageStudioEngine?.canvas) {
                return;
            }

            // Aplica o tamanho no canvas atual (não recarrega o rascunho antigo).
            this.imageStudioPreset = slug || preset.slug || 'custom';
            this._imageStudioPresetLocked = true;
            this.imageStudioCustomWidth = preset.width;
            this.imageStudioCustomHeight = preset.height;
            this.imageStudioEngine.setSize(preset.width, preset.height);
            this.fitImageStudioCanvas();
            await this.saveImageStudioDesign();
            this.refreshImageStudioLayers();
            this.message = `Formato: ${preset.name || slug} — ${preset.width}×${preset.height}px`;
        },

        onImageStudioBgChange() {
            this.imageStudioEngine?.setBackgroundColor(this.imageStudioBgColor, this.imageStudioBgTransparency);
            this.syncImageStudioUnderlayToEngine();
            this.scheduleImageStudioSave();
        },

        imageStudioApplyTransparentCanvasIfDefaultWhite() {
            const bg = this.imageStudioEngine?.getBackgroundState?.();
            const bgColor = (bg?.color || '').toLowerCase();
            const bgTransparency = Number(bg?.transparency) || 0;
            if (bgTransparency < 100 && (bgColor === '#ffffff' || bgColor === '#fff')) {
                this.imageStudioBgTransparency = 100;
                this.onImageStudioBgChange();
                return true;
            }
            return false;
        },

        async imageStudioSelectFont(slug) {
            this.imageStudioTextFontSlug = slug;
            const fontMeta = this.imageStudioFontMap[slug];
            if (fontMeta) {
                await ensureFontLoaded(fontMeta, {
                    bold: this.imageStudioTextBold,
                    italic: this.imageStudioTextItalic,
                });
            }
            await this.imageStudioOnTextControlChange();
        },

        setImageStudioSidebarTab(tab) {
            const allowed = ['tools', 'text', 'media', 'bg', 'layers', 'slides', 'export'];
            if (allowed.includes(tab)) {
                this.imageStudioSidebarTab = tab;
            }
        },

        isImageStudioMobileShell() {
            return isImageStudioMobileShell();
        },

        openImageStudioMobileSheet(tab) {
            this.setImageStudioSidebarTab(tab);
            this.imageStudioMobileSheetOpen = true;
        },

        closeImageStudioMobileSheet() {
            this.imageStudioMobileSheetOpen = false;
        },

        imageStudioMobileSheetTitle() {
            const labels = {
                tools: 'Ferramentas',
                text: 'Texto',
                media: 'Mídia',
                bg: 'Fundo',
                layers: 'Camadas',
                slides: 'Sequência',
                export: 'Exportar',
            };

            return labels[this.imageStudioSidebarTab] || 'Studio';
        },

        async imageStudioOnTextControlChange() {
            if (this._syncingTextUi) {
                return;
            }
            const obj = this.imageStudioEngine?.getActiveTextObject();
            if (!isFabricText(obj)) {
                return;
            }
            await this.imageStudioEngine.applyTextStyle(
                obj,
                this.imageStudioTextStylePayload(),
                this.imageStudioFontMap
            );
            this._syncingTextUi = true;
            const st = this.imageStudioEngine.getTextStyleFromObject(obj);
            if (st) {
                this.imageStudioTextFill = st.fill;
                this.imageStudioTextStroke = st.stroke;
                this.imageStudioTextStrokeWidth = st.strokeWidth;
            }
            this.$nextTick(() => {
                this._syncingTextUi = false;
            });
            this.imageStudioObjectScale = this.imageStudioEngine?.getActiveObjectScalePercent() ?? 100;
            this.imageStudioCanUndo = this.imageStudioEngine?.canUndo() ?? false;
            this.imageStudioCanRedo = this.imageStudioEngine?.canRedo() ?? false;
        },

        imageStudioOnTextFillChange() {
            this.imageStudioTextFill = normalizeColorInput(this.imageStudioTextFill, '#ffffff');
            this.imageStudioOnTextControlChange();
        },

        imageStudioOnTextStrokeChange() {
            this.imageStudioTextStroke = normalizeColorInput(this.imageStudioTextStroke, '#000000');
            if ((this.imageStudioTextStrokeWidth || 0) <= 0) {
                this.imageStudioTextStrokeWidth = 2;
            }
            this.imageStudioOnTextControlChange();
        },

        imageStudioRemoveTextOutline() {
            this.imageStudioTextStrokeWidth = 0;
            this.imageStudioOnTextControlChange();
        },

        async imageStudioAddText() {
            if (!this.imageStudioEngine?.canvas) {
                this.error = 'Abra o Image Studio e aguarde o canvas carregar';
                return;
            }
            const fontMeta = this.imageStudioFontMap[this.imageStudioTextFontSlug]
                || findFontBySlug(this.imageStudioFonts, this.imageStudioTextFontSlug)
                || findFontBySlug(this.imageStudioFonts, 'bebas_neue')
                || this.imageStudioFonts[0];
            if (fontMeta) {
                await ensureFontLoaded(fontMeta, {
                    bold: this.imageStudioTextBold,
                    italic: this.imageStudioTextItalic,
                });
            }
            const style = buildTextStyleFromFont(fontMeta, {
                bold: this.imageStudioTextBold,
                italic: this.imageStudioTextItalic,
            });
            const text = this.imageStudioTextContent || 'Seu título aqui';
            this.imageStudioEngine.addText(text, {
                ...style,
                fontSize: this.imageStudioTextSize,
                fill: this.imageStudioTextFill,
                stroke: this.imageStudioTextStroke,
                strokeWidth: this.imageStudioTextStrokeWidth,
                underline: this.imageStudioTextUnderline,
                linethrough: this.imageStudioTextLinethrough,
                textAlign: this.imageStudioTextAlign,
                lineHeight: this.imageStudioTextLineHeight,
                charSpacing: this.imageStudioTextCharSpacing,
                fontSlug: fontMeta?.slug || this.imageStudioTextFontSlug,
                shadow: this.imageStudioTextShadow,
                shadowColor: this.imageStudioTextShadowColor,
                shadowBlur: this.imageStudioTextShadowBlur,
            });
            this.refreshImageStudioLayers();
            this.message = `Texto adicionado — ${fontMeta?.label || 'fonte'}`;
        },

        async imageStudioAddIconGlyph(glyph) {
            if (!glyph?.char) {
                return;
            }
            if (!this.imageStudioEngine?.canvas) {
                await this.initImageStudio();
            }
            if (!this.imageStudioEngine?.canvas) {
                this.error = 'Canvas não carregou';
                return;
            }
            const fontMeta = this.imageStudioFontMap[glyph.font]
                || findFontBySlug(this.imageStudioFonts, glyph.font);
            if (fontMeta) {
                await ensureFontLoaded(fontMeta);
            }
            const style = buildTextStyleFromFont(fontMeta, { bold: false, italic: false });
            this.imageStudioEngine.addText(glyph.char, {
                ...style,
                fontSize: this.imageStudioTextSize || 96,
                fill: this.imageStudioTextFill || '#ffffff',
                stroke: this.imageStudioTextStroke,
                strokeWidth: this.imageStudioTextStrokeWidth,
                textAlign: 'center',
                fontSlug: glyph.font,
                iconGlyph: glyph.slug,
                name: glyph.label || 'Ícone',
            });
            this.refreshImageStudioLayers();
            this.message = `Ícone "${glyph.label || glyph.slug}" adicionado`;
        },

        imageStudioAddIconGlyphBySlug(slug) {
            if (!slug) {
                return;
            }
            let glyph = (this.imageStudioIconGlyphs || []).find((g) => g.slug === slug);
            if (!glyph) {
                const embedded = ImageStudioEngine.readEmbeddedJson('criasys-image-studio-icons');
                if (Array.isArray(embedded)) {
                    this.imageStudioIconGlyphs = embedded;
                    glyph = embedded.find((g) => g.slug === slug);
                }
            }
            if (!glyph) {
                this.error = `Ícone "${slug}" não encontrado no catálogo`;
                return;
            }
            void this.imageStudioAddIconGlyph(glyph);
        },

        imageStudioFilterIconList() {
            const q = (this.imageStudioIconFilter || '').trim().toLowerCase();
            const list = this.$refs.imageStudioIconList;
            if (!list) {
                return;
            }
            list.querySelectorAll('.is-icon-row').forEach((btn) => {
                const label = (btn.dataset.iconLabel || '').toLowerCase();
                const group = (btn.dataset.iconGroup || '').toLowerCase();
                const show = !q || label.includes(q) || group.includes(q);
                btn.style.display = show ? '' : 'none';
            });
        },

        imageStudioToggleTextBold() {
            this.imageStudioTextBold = !this.imageStudioTextBold;
            void this.imageStudioOnTextControlChange();
        },

        imageStudioToggleTextItalic() {
            this.imageStudioTextItalic = !this.imageStudioTextItalic;
            void this.imageStudioOnTextControlChange();
        },

        imageStudioToggleTextUnderline() {
            this.imageStudioTextUnderline = !this.imageStudioTextUnderline;
            void this.imageStudioOnTextControlChange();
        },

        imageStudioToggleTextLinethrough() {
            this.imageStudioTextLinethrough = !this.imageStudioTextLinethrough;
            void this.imageStudioOnTextControlChange();
        },

        imageStudioSetTextAlign(align) {
            this.imageStudioTextAlign = align;
            this.imageStudioOnTextControlChange();
        },

        imageStudioAddShape(type) {
            if (!this.imageStudioEngine?.canvas) {
                this.error = 'Canvas não carregou — clique Image Studio de novo';
                return;
            }
            if (type === 'circle') {
                this.imageStudioEngine.addCircle();
            } else {
                this.imageStudioEngine.addRect();
            }
            this.refreshImageStudioLayers();
            this.message = 'Forma adicionada';
        },

        async imageStudioAddElement(el) {
            if (!this.imageStudioEngine?.canvas) {
                await this.initImageStudio();
            }
            if (!this.imageStudioEngine?.canvas) {
                this.error = 'Canvas não carregou';
                return;
            }
            if (el?.type === 'icon_glyph') {
                await this.imageStudioAddIconGlyph({
                    char: el.char,
                    font: el.font,
                    slug: el.slug,
                    label: el.name || el.label,
                });
                return;
            }
            try {
                await this.imageStudioEngine.addElementFromCatalog(el);
                this.refreshImageStudioLayers();
                this.message = (el.name || 'Elemento') + ' adicionado';
            } catch (e) {
                this.error = e.message || 'Erro ao adicionar elemento';
            }
        },

        imageStudioIsPsdFile(file) {
            if (!file) {
                return false;
            }
            const type = String(file.type || '').toLowerCase();
            if (type === 'image/vnd.adobe.photoshop' || type === 'application/photoshop' || type === 'application/psd') {
                return true;
            }

            return /\.psd$/i.test(file.name || '');
        },

        imageStudioIsImageFile(file) {
            if (!file) {
                return false;
            }
            if (this.imageStudioIsPsdFile(file)) {
                return true;
            }
            if (file.type && file.type.startsWith('image/')) {
                return true;
            }

            return /\.(png|jpe?g|gif|webp|svg|bmp|avif)$/i.test(file.name || '');
        },

        imageStudioDragHasImageFiles(event) {
            const types = event?.dataTransfer?.types;
            if (!types) {
                return false;
            }
            const list = typeof types.includes === 'function'
                ? types
                : Array.from(types);

            return list.includes('Files');
        },

        imageStudioOnFileDragEnter(event) {
            if (!this.imageStudioDragHasImageFiles(event)) {
                return;
            }
            this.imageStudioFileDragDepth = (this.imageStudioFileDragDepth || 0) + 1;
            this.imageStudioFileDragOver = true;
            if (event.dataTransfer) {
                event.dataTransfer.dropEffect = 'copy';
            }
        },

        imageStudioOnFileDragOver(event) {
            if (!this.imageStudioDragHasImageFiles(event)) {
                return;
            }
            this.imageStudioFileDragOver = true;
            if (event.dataTransfer) {
                event.dataTransfer.dropEffect = 'copy';
            }
        },

        imageStudioOnFileDragLeave(event) {
            if (!this.imageStudioDragHasImageFiles(event)) {
                return;
            }
            this.imageStudioFileDragDepth = Math.max(0, (this.imageStudioFileDragDepth || 0) - 1);
            if (this.imageStudioFileDragDepth === 0) {
                this.imageStudioFileDragOver = false;
            }
        },

        async imageStudioImportPsdFile(file) {
            if (!this.imageStudioIsPsdFile(file)) {
                return false;
            }
            if (!this.imageStudioEngine?.canvas) {
                await this.initImageStudio();
            }
            if (!this.imageStudioEngine?.canvas) {
                this.error = 'Canvas não carregou — recarregue a página (F5)';

                return false;
            }

            const hasObjects = (this.imageStudioEngine.canvas.getObjects?.() || []).length > 0;
            if (hasObjects) {
                const ok = confirm(
                    'Importar o PSD redefine a prancheta para o tamanho do arquivo e substitui o conteúdo atual. Continuar?'
                );
                if (!ok) {
                    return false;
                }
            }

            this.message = 'Lendo PSD…';
            const buffer = await file.arrayBuffer();
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
            this.message = `PSD importado: ${result.layers} camada(s) · ${result.width}×${result.height}px. Camadas entram como imagens editáveis.`;

            return true;
        },

        async imageStudioAddImageFromFile(file) {
            if (!this.imageStudioIsImageFile(file)) {
                return false;
            }
            if (this.imageStudioIsPsdFile(file)) {
                return this.imageStudioImportPsdFile(file);
            }
            if (!this.imageStudioEngine?.canvas) {
                await this.initImageStudio();
            }
            if (!this.imageStudioEngine?.canvas) {
                this.error = 'Canvas não carregou — recarregue a página (F5)';

                return false;
            }
            const localUrl = URL.createObjectURL(file);
            await this.imageStudioEngine.addImageFromUrl(localUrl, file.name || 'Imagem');
            this.refreshImageStudioLayers();

            return true;
        },

        async imageStudioOnFileDrop(event) {
            this.imageStudioFileDragOver = false;
            this.imageStudioFileDragDepth = 0;

            const files = Array.from(event?.dataTransfer?.files || []).filter((file) =>
                this.imageStudioIsImageFile(file)
            );
            if (!files.length) {
                this.error = 'Solte PNG, JPG, WebP, SVG ou PSD (Photoshop)';

                return;
            }

            this.error = '';
            let added = 0;
            let psdCount = 0;
            try {
                for (const file of files) {
                    if (await this.imageStudioAddImageFromFile(file)) {
                        added += 1;
                        if (this.imageStudioIsPsdFile(file)) {
                            psdCount += 1;
                        }
                    }
                }
                if (added > 0 && !psdCount) {
                    this.message = added === 1
                        ? 'Imagem adicionada ao canvas'
                        : `${added} imagens adicionadas ao canvas`;
                }
            } catch (e) {
                this.error = e.message || 'Erro ao adicionar arquivo';
            }
        },

        async imageStudioUploadImage(event) {
            const file = event?.target?.files?.[0];
            if (!file) return;
            try {
                const ok = await this.imageStudioAddImageFromFile(file);
                if (ok && !this.imageStudioIsPsdFile(file)) {
                    this.message = 'Imagem adicionada ao canvas';
                }
            } catch (e) {
                this.error = e.message || 'Erro ao adicionar arquivo';
            } finally {
                if (event?.target) {
                    event.target.value = '';
                }
            }
        },

        async imageStudioRemoveBackground(event) {
            const file = event?.target?.files?.[0];
            if (!file) return;

            this.imageStudioBgRemoving = true;
            this.message = 'Removendo fundo com IA…';

            try {
                if (!this.imageStudioEngine?.canvas) await this.initImageStudio();
                const url = await this.imageStudioEngine.removeBackgroundFromBlob(file);
                await this.imageStudioEngine.addImageFromUrl(url, 'Sem fundo');
                this.refreshImageStudioLayers();
                if (this.imageStudioApplyTransparentCanvasIfDefaultWhite()) {
                    this.message = 'Fundo removido — imagem adicionada. Transparência do canvas em 100% para PNG sem fundo branco.';
                } else {
                    this.message = 'Fundo removido — imagem adicionada';
                }
            } catch (e) {
                this.error = e.message || 'Erro ao remover fundo';
            } finally {
                this.imageStudioBgRemoving = false;
                if (event?.target) event.target.value = '';
            }
        },

        imageStudioCaptureBgRemoveTarget() {
            const obj = this.resolveImageStudioImageObject();
            this._bgRemoveTarget = obj;
            if (obj) {
                this._imageStudioStickyObject = obj;
                this._imageStudioActiveObject = obj;
            }
            return obj;
        },

        async imageStudioRemoveBgFromSelection() {
            // Capture síncrono no pointerdown (antes do Fabric perder a seleção).
            const obj = this.imageStudioCaptureBgRemoveTarget();
            if (!isFabricImage(obj)) {
                this.error = 'Selecione uma imagem no canvas (clique nela primeiro)';
                return;
            }

            if (!this.imageStudioEngine) {
                await this.initImageStudio();
            }

            this.imageStudioBgRemoving = true;
            this.error = '';
            this.message = 'Removendo fundo com IA…';

            try {
                this.imageStudioEngine.canvas?.setActiveObject(obj);
                this._imageStudioStickyObject = obj;
                this._bgRemoveTarget = obj;

                if (!this.imageStudioEngine.bgRemovalUrl) {
                    this.imageStudioEngine.bgRemovalUrl = document.querySelector('meta[name="studio-remove-bg-url"]')?.getAttribute('content') || null;
                }
                if (!this.imageStudioEngine.bgRemovalDriver) {
                    this.imageStudioEngine.bgRemovalDriver = this.imageStudioBgRemovalDriver
                        || document.querySelector('meta[name="studio-bg-driver"]')?.getAttribute('content')
                        || 'rembg';
                }

                const blob = await this.fabricImageToPngBlob(obj);
                const url = await this.imageStudioEngine.removeBackgroundFromBlob(blob);
                const replaced = await this.imageStudioEngine.replaceActiveImageSource(url, obj);
                if (!replaced) {
                    throw new Error('Não foi possível substituir a imagem no canvas.');
                }
                this._imageStudioStickyObject = replaced;
                this._bgRemoveTarget = replaced;
                this.refreshImageStudioLayers();
                this.scheduleImageStudioSave?.();
                if (this.imageStudioApplyTransparentCanvasIfDefaultWhite()) {
                    this.message = 'Fundo removido da imagem. Transparência do canvas em 100% — o PNG sairá sem fundo branco.';
                } else {
                    this.message = 'Fundo removido da imagem selecionada. Para PNG sem fundo do canvas, use Transparência 100% em Fundo.';
                }
            } catch (e) {
                this.error = e.message || 'Erro ao remover fundo';
                this.message = '';
            } finally {
                this.imageStudioBgRemoving = false;
                this._bgRemoveTarget = null;
            }
        },

        async imageStudioDuplicateSelection() {
            const obj = this.resolveImageStudioActiveObject();
            if (!obj) {
                this.error = 'Selecione um objeto no canvas';
                return;
            }
            await this.imageStudioEngine.duplicateObject(obj);
            this.refreshImageStudioLayers();
            this.scheduleImageStudioSave?.();
            this.message = 'Objeto duplicado';
        },

        imageStudioFlipSelection(axis = 'x') {
            const obj = this.resolveImageStudioActiveObject();
            if (!obj) {
                this.error = 'Selecione um objeto no canvas';
                return;
            }
            this.imageStudioEngine.flipObject(obj, axis);
            this.scheduleImageStudioSave?.();
            this.message = axis === 'y' ? 'Espelhado na vertical' : 'Espelhado na horizontal';
        },

        imageStudioStartCrop() {
            const obj = this.resolveImageStudioActiveObject();
            if (!isFabricImage(obj)) {
                this.error = 'Selecione uma imagem para recortar';
                return;
            }
            this.imageStudioEngine.startCropMode(obj);
            this.imageStudioCropping = true;
            this.refreshImageStudioLayers();
            this.message = 'Ajuste a área roxa e confirme o recorte';
        },

        async imageStudioApplyCrop() {
            if (!this.imageStudioEngine?._cropRect) {
                this.error = 'Nenhuma área de recorte ativa';
                return;
            }
            const ok = await this.imageStudioEngine.applyCropMode();
            this.imageStudioCropping = false;
            this.refreshImageStudioLayers();
            this.scheduleImageStudioSave?.();
            this.message = ok ? 'Imagem recortada' : 'Recorte cancelado (área inválida)';
        },

        imageStudioCancelCrop() {
            this.imageStudioEngine?.cancelCropMode();
            this.imageStudioCropping = false;
            this.refreshImageStudioLayers();
            this.message = 'Recorte cancelado';
        },

        async imageStudioApplyBrandKit() {
            const brand = this.imageStudioBrand;
            if (!brand) {
                this.error = 'Marca do blog ainda não carregou';
                return;
            }
            if (!this.imageStudioEngine?.canvas) {
                await this.initImageStudio();
            }

            const colors = brand.colors || {};
            if (colors.surface) {
                this.imageStudioBgColor = colors.surface;
                this.imageStudioBgTransparency = 0;
                this.onImageStudioBgChange();
            }

            if (brand.logo_url) {
                try {
                    await this.imageStudioEngine.addImageFromUrl(brand.logo_url, 'Logo do blog');
                    const logo = this.imageStudioEngine.getActiveObject();
                    if (logo) {
                        const maxW = this.imageStudioEngine.designWidth * 0.28;
                        const scale = Math.min(1, maxW / Math.max(1, logo.width || 1));
                        logo.set({
                            left: this.imageStudioEngine.designWidth * 0.08,
                            top: this.imageStudioEngine.designHeight * 0.08,
                            originX: 'left',
                            originY: 'top',
                            scaleX: scale,
                            scaleY: scale,
                        });
                        this.imageStudioEngine.canvas.requestRenderAll();
                    }
                } catch {
                    /* logo remoto pode falhar por CORS — segue com texto */
                }
            }

            const accent = colors.accent || '#7c3aed';
            const textColor = colors.text || '#111827';
            this.imageStudioEngine.addText(brand.name || 'Meu blog', {
                name: 'Nome do blog',
                fill: textColor,
                fontSize: Math.round(Math.min(72, this.imageStudioEngine.designWidth / 18)),
                fontFamily: brand.font_heading
                    ? `'${brand.font_heading}', sans-serif`
                    : 'Montserrat, Arial, sans-serif',
            });
            const title = this.imageStudioEngine.getActiveObject();
            if (title) {
                title.set({
                    left: this.imageStudioEngine.designWidth * 0.5,
                    top: this.imageStudioEngine.designHeight * 0.42,
                    originX: 'center',
                    originY: 'center',
                    textAlign: 'center',
                });
            }

            const bar = new Rect({
                left: 0,
                top: this.imageStudioEngine.designHeight * 0.88,
                width: this.imageStudioEngine.designWidth,
                height: this.imageStudioEngine.designHeight * 0.12,
                fill: accent,
                selectable: true,
                name: 'Faixa da marca',
                criasysId: 'brand_bar_' + Date.now(),
            });
            this.imageStudioEngine.configureSelectableObject(bar);
            this.imageStudioEngine.canvas.add(bar);
            this.imageStudioEngine.canvas.requestRenderAll();
            this.imageStudioEngine.emitChange();

            this.refreshImageStudioLayers();
            this.scheduleImageStudioSave?.();
            this.message = 'Marca do blog aplicada no canvas';
        },

        imageStudioSelectLayer(layer, event = null) {
            this._imageStudioSelectingFromLayersPanel = true;
            const obj = layer?.object;
            if (!obj) {
                this._imageStudioSelectingFromLayersPanel = false;
                return;
            }

            const additive = !!(event && (event.ctrlKey || event.metaKey || event.shiftKey));
            if (additive) {
                const bag = Array.isArray(this._imageStudioGroupBag) ? [...this._imageStudioGroupBag] : [];
                const idx = bag.indexOf(obj);
                if (idx >= 0 && (event.ctrlKey || event.metaKey)) {
                    bag.splice(idx, 1);
                } else if (idx < 0) {
                    bag.push(obj);
                }
                this._imageStudioGroupBag = bag;
                this.imageStudioGroupBagCount = bag.length;
                this.imageStudioEngine?.setMultiSelection?.(bag);
                this.imageStudioCanGroup = bag.length >= 2;
                this.setImageStudioSidebarTab?.('layers');
                this.refreshImageStudioLayers();
                this.$nextTick?.(() => {
                    this._imageStudioSelectingFromLayersPanel = false;
                });
                return;
            }

            this._imageStudioGroupBag = [obj];
            this.imageStudioGroupBagCount = 1;
            this.imageStudioEngine?.selectLayer(obj);
            this.setImageStudioSidebarTab?.('layers');
            this.refreshImageStudioLayers();
            this.$nextTick?.(() => {
                this._imageStudioSelectingFromLayersPanel = false;
            });
        },

        imageStudioLayerAction(layer, action) {
            if (action === 'visibility') {
                this.imageStudioEngine?.toggleLayerVisibility(layer.object);
            } else if (action === 'lock') {
                this.imageStudioEngine?.toggleLayerLock(layer.object);
            } else if (action === 'delete') {
                this.imageStudioEngine?.removeLayer(layer.object);
            } else {
                this.imageStudioEngine?.moveLayer(layer.object, action);
            }
            this.refreshImageStudioLayers();
        },

        imageStudioObjectOpacity(value) {
            const obj = this.imageStudioEngine?.getActiveObject();
            if (obj) {
                this.imageStudioEngine.applyObjectOpacity(obj, value);
            }
        },

        async imageStudioExport(format, options = {}) {
            if (!this.imageStudioEngine) {
                return;
            }
            const openPreview = options.openPreview !== false;
            try {
                this.syncImageStudioBackgroundBeforeExport();
                const blob = await this.imageStudioEngine.exportBlob(format, 0.92, this.buildImageStudioExportOptions(format));
                if (!blob) {
                    return;
                }
                const form = new FormData();
                const ext = format === 'jpeg' ? 'jpg' : format;
                form.append('file', blob, `design.${ext}`);
                form.append('format', format);
                form.append('preset', this.imageStudioPreset);
                const { data } = await api.post(`/projects/${this.projectId}/image-studio/export`, form, {
                    headers: { 'Content-Type': 'multipart/form-data' },
                });
                this.imageStudioLastExport = data.export;
                this.message = `Exportado: ${format.toUpperCase()}`;
                if (openPreview && (format === 'png' || format === 'jpg')) {
                    window.open(data.export.url, '_blank');
                }
            } catch (e) {
                this.error = e.response?.data?.message || 'Erro ao exportar';
            }
        },

        imageStudioResolveThumbnailPlatform() {
            const mapped = this.imageStudioPresetPlatformMap?.[this.imageStudioPreset];
            return mapped || this.selectedThumbnailPlatform || 'youtube_landscape';
        },

        async imageStudioPushThumbnail() {
            if (!this.imageStudioLastExport?.filename) {
                await this.imageStudioExport('png');
            }
            if (!this.imageStudioLastExport?.filename) {
                return;
            }
            const platform = this.imageStudioResolveThumbnailPlatform();
            try {
                const { data } = await api.post(`/projects/${this.projectId}/image-studio/push-thumbnail`, {
                    filename: this.imageStudioLastExport.filename,
                    platform,
                    preset: this.imageStudioPreset,
                });
                this.message = `Arte do Image Studio aplicada na capa ${platform}`;
                if (data.settings) {
                    this.applyThumbnailSettingsPatch(data.settings);
                }
                if (data.platform) {
                    this.selectedThumbnailPlatform = data.platform;
                    this.thumbnailSettingsByPlatform[data.platform] = {
                        ...(this.thumbnailSettingsByPlatform[data.platform] || {}),
                        ...(data.settings || {}),
                    };
                }
                if (data.thumbnail?.url) {
                    this.thumbnailPreviewUrl = data.thumbnail.url;
                    this.thumbnailPreviewUrls[platform] = data.thumbnail.url;
                }
                this.switchTab('thumbnail');
            } catch (e) {
                this.error = e.response?.data?.message || 'Erro ao enviar para Thumbnail';
            }
        },

        async imageStudioPushLibrary() {
            if (!this.imageStudioLastExport?.filename) {
                await this.imageStudioExport('png', { openPreview: false });
            }
            if (!this.imageStudioLastExport?.filename) {
                this.error = 'Exporte o design antes de salvar na biblioteca.';
                return;
            }
            try {
                const { data } = await api.post(`/projects/${this.projectId}/image-studio/push-library`, {
                    filename: this.imageStudioLastExport.filename,
                    preset: this.imageStudioPreset,
                });
                if (data.asset) {
                    this.upsertProjectLibraryAsset(data.asset);
                } else {
                    await this.loadProjectLibraryAssets();
                }
                this.message = data.message || 'Imagem adicionada à biblioteca do projeto';
                this.switchTab('biblioteca');
            } catch (e) {
                this.error = e.response?.data?.message || 'Erro ao enviar para biblioteca';
            }
        },

        async imageStudioApplyTemplate(template) {
            if (!template?.slug) {
                return false;
            }
            if (!this.imageStudioEngine?.canvas) {
                await this.initImageStudio();
            }
            let full = (this.imageStudioTemplates || []).find((t) => t.slug === template.slug) || null;
            if (!full) {
                for (const pack of (this.imageStudioPacks || [])) {
                    const hit = (pack.items || []).find((i) => i.slug === template.slug && i.type !== 'brand_kit');
                    if (hit) {
                        full = hit;
                        break;
                    }
                }
            }
            full = full || template;
            normalizeTemplateTextFields(full);
            if (this.imageStudioLayers?.length && !confirm('Aplicar layout substitui o conteúdo do canvas. Continuar?')) {
                return false;
            }
            if (full.preset) {
                await this.switchImageStudioPreset(full.preset);
            }
            await this.imageStudioEngine.applyTemplate(full, this.imageStudioFontMap);
            if (full.background?.color !== undefined) {
                this.imageStudioBgColor = full.background.color;
                this.imageStudioBgTransparency = full.background.transparency
                    ?? (full.background.opacity !== undefined
                        ? Math.max(0, 100 - Number(full.background.opacity))
                        : 0);
                this.syncImageStudioBackgroundFromEngine();
            }
            const hookFont = (full.objects || []).find((o) => o.fontSlug)?.fontSlug;
            if (hookFont && this.imageStudioFontMap[hookFont]) {
                this.imageStudioTextFontSlug = hookFont;
            }
            this._imageStudioStickyObject = this.imageStudioEngine.getActiveObject();
            this._imageStudioActiveObject = this._imageStudioStickyObject;
            this.closeImageStudioTemplatesModal();
            this.closeImageStudioPacksModal();
            this.fitImageStudioCanvas();
            this.refreshImageStudioLayers();
            this.scheduleImageStudioSave();
            const layerCount = this.imageStudioLayers?.length || 0;
            const primaryName = this.imageStudioEngine.getActiveObject()?.name;
            this.message = primaryName
                ? `Layout "${full.name}" pronto — ${layerCount} camadas organizadas. Edite “${primaryName}”.`
                : `Layout "${full.name}" pronto — ${layerCount} camadas organizadas e visíveis.`;

            return true;
        },

        async imageStudioImportFromLibrary(item) {
            if (!item?.preview_url && !item?.download_url) {
                return;
            }
            const url = item.preview_url || item.download_url;
            await this.imageStudioEngine?.addImageFromUrl(url, item.title || 'Biblioteca');
            this.refreshImageStudioLayers();
        },

        async imageStudioImportFromLibraryItem(item) {
            if (!this.imageStudioReady) {
                this.switchTab('image_studio');
                await this.$nextTick();
                if (!this.imageStudioReady) {
                    await this.initImageStudio();
                }
            } else {
                this.switchTab('image_studio');
            }
            try {
                await this.imageStudioImportFromLibrary(item);
                this.message = 'Imagem adicionada ao Image Studio';
            } catch (e) {
                this.error = e.message || 'Erro ao importar para o Image Studio';
            }
        },

        async imageStudioImportFromAsset(asset) {
            if (!asset?.id && !asset?.url) {
                return;
            }
            const url = asset.url || `/api/projects/${this.projectId}/assets/${asset.id}`;
            if (!this.imageStudioReady) {
                this.switchTab('image_studio');
                await this.$nextTick();
                if (!this.imageStudioReady) {
                    await this.initImageStudio();
                }
            } else {
                this.switchTab('image_studio');
            }
            await this.imageStudioEngine?.addImageFromUrl(url, asset.item_title || 'Asset');
            this.refreshImageStudioLayers();
            this.message = 'Asset adicionado ao canvas';
        },
    };
}
