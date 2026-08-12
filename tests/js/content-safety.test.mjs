import test from 'node:test';
import assert from 'node:assert/strict';
import { shouldBlockExplicitContent } from '../../resources/js/image-studio/contentSafety.js';

test('allows bikini/lingerie (Sexy high, Porn/Hentai low)', () => {
    const v = shouldBlockExplicitContent({
        Sexy: 0.82,
        Neutral: 0.1,
        Porn: 0.05,
        Hentai: 0.02,
        Drawing: 0.01,
    });
    assert.equal(v.blocked, false);
});

test('blocks explicit porn', () => {
    const v = shouldBlockExplicitContent({
        Porn: 0.78,
        Sexy: 0.15,
        Neutral: 0.05,
        Hentai: 0.01,
        Drawing: 0.01,
    });
    assert.equal(v.blocked, true);
    assert.ok(v.triggers.some((t) => t.startsWith('porn=')));
});

test('blocks hentai', () => {
    const v = shouldBlockExplicitContent({
        Hentai: 0.71,
        Drawing: 0.2,
        Porn: 0.05,
        Sexy: 0.03,
        Neutral: 0.01,
    });
    assert.equal(v.blocked, true);
});

test('blocks combined porn+hentai near threshold', () => {
    const v = shouldBlockExplicitContent({
        Porn: 0.4,
        Hentai: 0.4,
        Sexy: 0.1,
        Neutral: 0.05,
        Drawing: 0.05,
    });
    assert.equal(v.blocked, true);
    assert.ok(v.triggers.some((t) => t.startsWith('combined=')));
});

test('does not block Sexy alone even if high', () => {
    const v = shouldBlockExplicitContent(
        { Sexy: 0.95, Neutral: 0.03, Porn: 0.01, Hentai: 0.01, Drawing: 0 },
        { blockSexy: false },
    );
    assert.equal(v.blocked, false);
});
