/**
 * Helpers puros do Studio (sem DOM/Fabric) — unit-testáveis via node:test.
 */

const LITERAL_LF = String.fromCharCode(92, 110);
const LITERAL_CR = String.fromCharCode(92, 114);
const LITERAL_CRLF = String.fromCharCode(92, 114, 92, 110);
const REAL_LF = String.fromCharCode(10);
const REAL_CR = String.fromCharCode(13);

/**
 * Converte sequências literais \n / \r\n (dois chars) em Line Feed real.
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

export function parseHexColor(hex) {
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

export function parseCanvasBackgroundState(backgroundColor, fallbackColor = '#ffffff') {
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

/** PNG/ZIP com transparência 100% não deve pintar fundo opaco no export. */
export function shouldOmitCanvasBackground(format, transparency) {
    const pngLike = format === 'png' || format === 'png_zip' || format === 'zip';
    return pngLike && (Number(transparency) || 0) >= 100;
}

export function isFabricPaintObject(value) {
    return !!value && typeof value === 'object';
}
