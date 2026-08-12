/**
 * Moderação de imagens no Studio (NSFWJS no navegador).
 *
 * Política MarkCraft:
 * - Bloqueia conteúdo explícito (Porn / Hentai)
 * - NÃO bloqueia só por "Sexy" (biquíni, lingerie, sunga)
 * - Não detecta pedofilia/zoofilia de forma específica — é barreira geral anti-explícito
 */

export class ContentSafetyError extends Error {
    constructor(message, details = {}) {
        super(message);
        this.name = 'ContentSafetyError';
        this.code = 'CONTENT_BLOCKED';
        this.details = details;
    }
}

const DEFAULT_THRESHOLDS = {
    porn: 0.55,
    hentai: 0.55,
    combined: 0.75,
    blockSexy: false,
    sexy: 0.98, // só se blockSexy=true (quase nunca)
};

let modelPromise = null;
let tfReady = false;

export function readContentSafetyConfig() {
    const enabledRaw = document.querySelector('meta[name="studio-content-safety"]')?.getAttribute('content');
    const enabled = enabledRaw == null || enabledRaw === ''
        ? true
        : !['0', 'false', 'off', 'no'].includes(String(enabledRaw).toLowerCase());

    const jsonRaw = document.querySelector('meta[name="studio-content-safety-thresholds"]')?.getAttribute('content');
    let thresholds = { ...DEFAULT_THRESHOLDS };
    if (jsonRaw) {
        try {
            const parsed = JSON.parse(jsonRaw);
            thresholds = {
                porn: Number(parsed.porn ?? thresholds.porn),
                hentai: Number(parsed.hentai ?? thresholds.hentai),
                combined: Number(parsed.combined ?? thresholds.combined),
                blockSexy: Boolean(parsed.blockSexy ?? thresholds.blockSexy),
                sexy: Number(parsed.sexy ?? thresholds.sexy),
            };
        } catch {
            /* defaults */
        }
    }

    return { enabled, thresholds };
}

/**
 * Decide se scores NSFWJS devem bloquear (puro / testável).
 * @param {Record<string, number>} scores
 * @param {typeof DEFAULT_THRESHOLDS} thresholds
 */
export function shouldBlockExplicitContent(scores, thresholds = DEFAULT_THRESHOLDS) {
    const porn = Number(scores.Porn ?? scores.porn ?? 0);
    const hentai = Number(scores.Hentai ?? scores.hentai ?? 0);
    const sexy = Number(scores.Sexy ?? scores.sexy ?? 0);
    const t = { ...DEFAULT_THRESHOLDS, ...thresholds };

    const triggers = [];
    if (porn >= t.porn) {
        triggers.push(`porn=${porn.toFixed(2)}`);
    }
    if (hentai >= t.hentai) {
        triggers.push(`hentai=${hentai.toFixed(2)}`);
    }
    if (porn + hentai >= t.combined) {
        triggers.push(`combined=${(porn + hentai).toFixed(2)}`);
    }
    if (t.blockSexy && sexy >= t.sexy) {
        triggers.push(`sexy=${sexy.toFixed(2)}`);
    }

    return {
        blocked: triggers.length > 0,
        triggers,
        scores: { porn, hentai, sexy },
    };
}

function predictionsToScores(predictions) {
    const scores = { Drawing: 0, Hentai: 0, Neutral: 0, Porn: 0, Sexy: 0 };
    (predictions || []).forEach((row) => {
        if (row?.className && typeof row.probability === 'number') {
            scores[row.className] = row.probability;
        }
    });

    return scores;
}

async function ensureModel() {
    if (!modelPromise) {
        modelPromise = (async () => {
            const tf = await import('@tensorflow/tfjs');
            if (!tfReady) {
                try {
                    tf.enableProdMode?.();
                } catch {
                    /* ignore */
                }
                await tf.ready();
                tfReady = true;
            }
            const nsfwjs = await import('nsfwjs');
            // MobileNetV2 bundled — sem CDN externo
            return nsfwjs.load('MobileNetV2');
        })().catch((err) => {
            modelPromise = null;
            throw err;
        });
    }

    return modelPromise;
}

function loadHtmlImage(src) {
    return new Promise((resolve, reject) => {
        const img = new Image();
        img.decoding = 'async';
        img.onload = () => resolve(img);
        img.onerror = () => reject(new Error('Não foi possível carregar a imagem para moderação'));
        if (!String(src).startsWith('blob:') && !String(src).startsWith('data:')) {
            img.crossOrigin = 'anonymous';
        }
        img.src = src;
    });
}

/**
 * Redimensiona para acelerar inferência (modelo espera ~224px).
 * @param {CanvasImageSource} source
 */
function toInferenceCanvas(source, maxSide = 320) {
    const w = source.naturalWidth || source.width || 0;
    const h = source.naturalHeight || source.height || 0;
    if (!w || !h) {
        throw new Error('Imagem inválida para moderação');
    }
    const scale = Math.min(1, maxSide / Math.max(w, h));
    const cw = Math.max(1, Math.round(w * scale));
    const ch = Math.max(1, Math.round(h * scale));
    const canvas = document.createElement('canvas');
    canvas.width = cw;
    canvas.height = ch;
    const ctx = canvas.getContext('2d', { willReadFrequently: true });
    ctx.drawImage(source, 0, 0, cw, ch);

    return canvas;
}

const BLOCK_MESSAGE = 'Conteúdo explícito bloqueado. Lingerie, biquíni ou sunga são permitidos; nudez, sexo e pornografia não.';

/**
 * Classifica e lança ContentSafetyError se explícito.
 * @param {CanvasImageSource|string} sourceOrUrl
 */
export async function assertImageContentAllowed(sourceOrUrl, options = {}) {
    const { enabled, thresholds } = readContentSafetyConfig();
    if (!enabled || options.skip) {
        return { skipped: true };
    }

    let source = sourceOrUrl;
    if (typeof sourceOrUrl === 'string') {
        source = await loadHtmlImage(sourceOrUrl);
    }

    const canvas = toInferenceCanvas(source);
    const model = await ensureModel();
    const predictions = await model.classify(canvas, 5);
    const scores = predictionsToScores(predictions);
    const verdict = shouldBlockExplicitContent(scores, thresholds);

    if (verdict.blocked) {
        throw new ContentSafetyError(BLOCK_MESSAGE, verdict);
    }

    return { skipped: false, scores: verdict.scores };
}

/**
 * Escaneia data URL / blob URL / HTMLImage / canvas do design exportado.
 */
export async function assertDesignExportAllowed(canvasOrDataUrl) {
    return assertImageContentAllowed(canvasOrDataUrl);
}

export function isContentSafetyError(err) {
    return err instanceof ContentSafetyError || err?.code === 'CONTENT_BLOCKED' || err?.name === 'ContentSafetyError';
}
