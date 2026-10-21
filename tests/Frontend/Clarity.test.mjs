import assert from 'node:assert/strict';
import {readFile} from 'node:fs/promises';
import vm from 'node:vm';
import test from 'node:test';

const source = await readFile(new URL('../../public/js/clarity.js', import.meta.url), 'utf8');

function browser(projectId = 'abc123', existingClarity) {
    const scripts = [];
    const window = {clarity: existingClarity};
    Object.defineProperty(window, 'localStorage', {get() {throw new Error('Removed consent storage must not be accessed');}});
    const document = {
        currentScript: {dataset: {clarityProjectId: projectId}},
        getElementById() {throw new Error('Tracker must not require consent UI');},
        querySelector(selector) {return scripts.find(s => selector === `script[src="${s.src}"]`);},
        createElement() {return {};},
        head: {appendChild(script) {scripts.push(script);}},
    };
    const context = {window, document};
    const run = () => vm.runInNewContext(source, context);
    run();
    return {window, scripts, run};
}

test('loads immediately without consent controls, local storage or a synthetic decision', () => {
    const page = browser();
    assert.equal(page.scripts.length, 1);
    assert.equal(page.scripts[0].src, 'https://www.clarity.ms/tag/abc123');
    assert.equal(page.scripts[0].async, true);
    assert.equal(page.window.clarity.q, undefined);
    page.window.clarity('event', 'example');
    assert.deepEqual(Array.from(page.window.clarity.q[0]), ['event', 'example']);
});

test('loading the integration again does not append another Microsoft tag', () => {
    const page = browser();
    page.run();
    assert.equal(page.scripts.length, 1);
});

test('preserves an existing Clarity function and its queued events', () => {
    const existing = () => {};
    existing.q = [['event', 'before']];
    const page = browser('abc123', existing);
    assert.equal(page.window.clarity, existing);
    assert.deepEqual(existing.q, [['event', 'before']]);
});

test('missing and invalid project identifiers do not load the remote tag', () => {
    for (const id of [null, '', 'example/id', '"><script>', 'abc 123']) {
        const page = browser(id);
        assert.equal(page.scripts.length, 0);
        assert.equal(page.window.clarity, undefined);
    }
});
