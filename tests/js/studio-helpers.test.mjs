/**
 * Unit tests — helpers puros do Studio (node:test, sem browser).
 * Rodar: node --test tests/js/studio-helpers.test.mjs
 */
import { describe, it } from 'node:test';
import assert from 'node:assert/strict';

import {
    normalizeMultilineText,
    normalizeTemplateTextFields,
    normalizeTemplatesList,
    parseHexColor,
    parseCanvasBackgroundState,
    shouldOmitCanvasBackground,
    isFabricPaintObject,
} from '../../resources/js/image-studio/studioPure.js';

import {
    normalizeColorInput,
    isTextLikeType,
    resolveFontWeight,
    findFontBySlug,
    FALLBACK_FONTS,
} from '../../resources/js/image-studio/imageStudioTextFonts.js';

import {
    resolveInsertFill,
    resolveInsertStroke,
    regularPolygonPoints,
    starPolygonPoints,
    canvasShapeSize,
    DEFAULT_SHAPE_FILL,
    DEFAULT_SHAPE_STROKE,
} from '../../resources/js/image-studio/imageStudioShapes.js';

describe('normalizeMultilineText', () => {
    it('preserva string vazia e não-string', () => {
        assert.equal(normalizeMultilineText(''), '');
        assert.equal(normalizeMultilineText(null), null);
        assert.equal(normalizeMultilineText(12), 12);
    });

    it('converte \\n literal em LF real', () => {
        const literal = 'a\\nb';
        const out = normalizeMultilineText(literal);
        assert.equal(out, 'a\nb');
        assert.equal(out.includes('\\'), false);
    });

    it('converte \\r\\n literal e CR real', () => {
        assert.equal(normalizeMultilineText('a\\r\\nb'), 'a\nb');
        assert.equal(normalizeMultilineText('a\rb'), 'a\nb');
    });

    it('é idempotente em texto já normalizado', () => {
        const once = normalizeMultilineText('linha1\\nlinha2');
        assert.equal(normalizeMultilineText(once), once);
    });
});

describe('normalizeTemplateTextFields', () => {
    it('normaliza só objetos kind/type text', () => {
        const tpl = {
            objects: [
                { kind: 'text', text: 'Hi\\nThere' },
                { type: 'rect', fill: '#000' },
                { type: 'text', text: 'A\\nB' },
            ],
        };
        normalizeTemplateTextFields(tpl);
        assert.equal(tpl.objects[0].text, 'Hi\nThere');
        assert.equal(tpl.objects[1].fill, '#000');
        assert.equal(tpl.objects[2].text, 'A\nB');
    });

    it('normalizeTemplatesList ignora não-array', () => {
        assert.equal(normalizeTemplatesList(null), null);
        assert.equal(normalizeTemplatesList({}).objects, undefined);
    });
});

describe('normalizeColorInput', () => {
    it('fallback para null/objeto (gradiente)', () => {
        assert.equal(normalizeColorInput(null), '#ffffff');
        assert.equal(normalizeColorInput({ type: 'linear' }, '#111111'), '#111111');
    });

    it('expande #rgb e normaliza #rrggbb', () => {
        assert.equal(normalizeColorInput('#AbC'), '#aabbcc');
        assert.equal(normalizeColorInput('#FF00aa'), '#ff00aa');
    });

    it('converte rgb/rgba', () => {
        assert.equal(normalizeColorInput('rgb(255, 0, 0)'), '#ff0000');
        assert.equal(normalizeColorInput('rgba(0, 128, 255, 0.5)'), '#0080ff');
    });

    it('mapeia nomes comuns e rejeita lixo p/ input color', () => {
        assert.equal(normalizeColorInput('white'), '#ffffff');
        assert.equal(normalizeColorInput('BLACK'), '#000000');
        assert.equal(normalizeColorInput('not-a-color', '#abcdef'), '#abcdef');
    });

    it('aceita #rrggbbaa cortando alpha', () => {
        assert.equal(normalizeColorInput('#ff00aacc'), '#ff00aa');
    });
});

describe('parseHexColor / parseCanvasBackgroundState', () => {
    it('parseHexColor #fff e inválido', () => {
        assert.deepEqual(parseHexColor('#fff'), { r: 255, g: 255, b: 255 });
        assert.deepEqual(parseHexColor('#00ff00'), { r: 0, g: 255, b: 0 });
        assert.deepEqual(parseHexColor('xx'), { r: 255, g: 255, b: 255 });
    });

    it('transparent e rgba → transparency', () => {
        assert.deepEqual(parseCanvasBackgroundState('transparent'), {
            color: '#ffffff',
            transparency: 100,
        });
        const rgba = parseCanvasBackgroundState('rgba(10, 20, 30, 0.5)');
        assert.equal(rgba.color, '#0a141e');
        assert.equal(rgba.transparency, 50);
        assert.deepEqual(parseCanvasBackgroundState('#112233'), {
            color: '#112233',
            transparency: 0,
        });
    });
});

describe('shouldOmitCanvasBackground', () => {
    it('só PNG-like com transparency 100', () => {
        assert.equal(shouldOmitCanvasBackground('png', 100), true);
        assert.equal(shouldOmitCanvasBackground('png_zip', 100), true);
        assert.equal(shouldOmitCanvasBackground('zip', 100), true);
        assert.equal(shouldOmitCanvasBackground('jpg', 100), false);
        assert.equal(shouldOmitCanvasBackground('png', 99), false);
        assert.equal(shouldOmitCanvasBackground('jpeg', 100), false);
    });
});

describe('isTextLikeType / fonts', () => {
    it('reconhece tipos Fabric de texto', () => {
        assert.equal(isTextLikeType('textbox'), true);
        assert.equal(isTextLikeType('i-text'), true);
        assert.equal(isTextLikeType('IText'), true);
        assert.equal(isTextLikeType('image'), false);
    });

    it('findFontBySlug e resolveFontWeight', () => {
        const font = findFontBySlug(FALLBACK_FONTS, 'montserrat');
        assert.ok(font);
        assert.equal(resolveFontWeight(font, true), 700);
        assert.equal(resolveFontWeight(font, false), 400);
        assert.equal(findFontBySlug(FALLBACK_FONTS, 'nope'), null);
    });
});

describe('shapes insert colors / geometry', () => {
    it('branco perto vira fill/stroke padrão', () => {
        assert.equal(resolveInsertFill('#ffffff'), DEFAULT_SHAPE_FILL);
        assert.equal(resolveInsertFill('#fafafa'), DEFAULT_SHAPE_FILL);
        assert.equal(resolveInsertFill('#0d9488'), '#0d9488');
        assert.equal(resolveInsertStroke('white'), DEFAULT_SHAPE_STROKE);
        assert.equal(resolveInsertStroke('#112233'), '#112233');
    });

    it('polígono regular e estrela têm pontos esperados', () => {
        const hex = regularPolygonPoints(6, 10);
        assert.equal(hex.length, 6);
        assert.ok(Math.abs(hex[0].y + 10) < 1e-9);
        const star = starPolygonPoints(10, 5, 5);
        assert.equal(star.length, 10);
    });

    it('canvasShapeSize usa o menor lado', () => {
        assert.equal(canvasShapeSize(1000, 500, 0.28), 140);
    });
});

describe('isFabricPaintObject', () => {
    it('detecta paint objeto vs string', () => {
        assert.equal(isFabricPaintObject({ type: 'linear' }), true);
        assert.equal(isFabricPaintObject('#fff'), false);
        assert.equal(isFabricPaintObject(null), false);
    });
});
