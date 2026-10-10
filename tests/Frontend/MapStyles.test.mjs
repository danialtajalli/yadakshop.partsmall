import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';
import test from 'node:test';
import postcss from 'postcss';
import scopeMapStyles from '../../resources/js/map/scope-styles.js';

const sdkPath = new URL('../../node_modules/@neshan-maps-platform/mapbox-gl/dist/NeshanMapboxGl.css', import.meta.url);

test('map SDK utilities cannot change typography or layout outside the map', async () => {
    const css = await readFile(sdkPath, 'utf8');
    const result = await postcss([scopeMapStyles()]).process(css, { from: sdkPath.pathname });
    const selectors = [];

    result.root.walkRules((rule) => {
        if (rule.parent.type === 'root') {
            selectors.push(...rule.selectors);
        }
    });

    assert.ok(selectors.includes('.mapboxgl-map'));
    assert.ok(selectors.includes('.mapboxgl-map :is(.text-sm)'));
    assert.ok(selectors.includes('.mapboxgl-map :is(.text-xl)'));
    assert.ok(selectors.includes('.mapboxgl-map :is(.row > *)'));
    assert.ok(selectors.every((selector) => selector.startsWith('.mapboxgl-map')));
    assert.match(result.css, /@font-face/);
    assert.match(result.css, /@keyframes/);
});

test('Filament and public site styles are left unchanged', async () => {
    const css = '.text-sm { font-size: 0.875rem; } .text-xl { font-size: 1.25rem; }';
    const result = await postcss([scopeMapStyles()]).process(css, { from: 'resources/css/app.css' });

    assert.equal(result.css, css);
});

test('night styles, nested selectors and animations keep their meaning', async () => {
    const css = 'html[map-style*=Night] .wm-container { color: white; }\n'
        + '.water_mark_container { & a { color: inherit; } }\n'
        + '@media (min-width: 600px) { .text-sm, .row { display: block; } }\n'
        + '@keyframes spin { to { transform: rotate(360deg); } }';
    const result = await postcss([scopeMapStyles()]).process(css, { from: sdkPath.pathname });

    assert.match(result.css, /\.mapboxgl-map :is\(html\[map-style\*=Night\] \.wm-container\)/);
    assert.match(result.css, /& a \{ color: inherit; \}/);
    assert.match(result.css, /\.mapboxgl-map :is\(\.text-sm\), \.mapboxgl-map :is\(\.row\)/);
    assert.match(result.css, /@keyframes spin \{ to \{/);
});
