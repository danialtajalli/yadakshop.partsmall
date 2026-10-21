import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';
import vm from 'node:vm';
import test from 'node:test';

const source = await readFile(new URL('../../public/js/clarity.js', import.meta.url), 'utf8');
const key = 'partsmall.clarity.consent.v1.abc123';

function browser({ stored = null, blockedStorage = false, writeFails = false } = {}) {
    const elements = new Map();
    for (const id of ['consent', 'settings', 'accept', 'reject']) {
        elements.set(`ps-clarity-${id}`, {
            hidden: true,
            listeners: {},
            attributes: {},
            addEventListener(name, handler) { this.listeners[name] = handler; },
            setAttribute(name, value) { this.attributes[name] = value; },
            focus() {},
        });
    }
    const scripts = [];
    const listeners = {};
    let reloads = 0;
    const storage = new Map(stored === null ? [] : [[key, stored]]);
    const window = {
        localStorage: {
            getItem(name) {
                if (blockedStorage) throw new Error('blocked');
                return storage.get(name) ?? null;
            },
            setItem(name, value) {
                if (blockedStorage || writeFails) throw new Error('blocked');
                storage.set(name, value);
            },
            removeItem(name) { storage.delete(name); },
        },
        addEventListener(name, handler) { listeners[name] = handler; },
        location: { reload() { reloads++; } },
        setTimeout(handler) { handler(); },
    };
    const document = {
        currentScript: { dataset: { clarityProjectId: 'abc123' } },
        getElementById(id) { return elements.get(id); },
        createElement() { return {}; },
        head: { appendChild(script) { scripts.push(script); } },
    };
    vm.runInNewContext(source, { window, document });

    return {
        window, elements, scripts, storage, listeners,
        get reloads() { return reloads; },
        click(id) { elements.get(`ps-clarity-${id}`).listeners.click(); },
        consent() { return Array.from(window.clarity.q.at(-1)); },
    };
}

function decision(value, expiresAt = Date.now() + 60_000) {
    return JSON.stringify({ value, expiresAt });
}

test('no consent means no Microsoft request; rejecting keeps tracking unloaded', () => {
    const page = browser();
    assert.equal(page.scripts.length, 0);
    assert.equal(page.elements.get('ps-clarity-consent').hidden, false);
    page.click('reject');
    assert.equal(page.scripts.length, 0);
    assert.equal(JSON.parse(page.storage.get(key)).value, 'denied');
    assert.equal(page.elements.get('ps-clarity-settings').hidden, false);
});

test('acceptance queues analytics consent before loading one tag and denies ads', () => {
    const page = browser();
    page.click('accept');
    assert.equal(page.scripts[0].src, 'https://www.clarity.ms/tag/abc123');
    assert.equal(page.scripts[0].async, true);
    assert.equal(page.consent()[0], 'consentv2');
    assert.equal(page.consent()[1].analytics_Storage, 'granted');
    assert.equal(page.consent()[1].ad_Storage, 'denied');
    page.click('settings');
    assert.equal(page.elements.get('ps-clarity-consent').hidden, false);
    page.click('accept');
    assert.equal(page.scripts.length, 1);
});

test('saved consent loads tracking, saved rejection does not', () => {
    assert.equal(browser({ stored: decision('granted') }).scripts.length, 1);
    const rejected = browser({ stored: decision('denied') });
    assert.equal(rejected.scripts.length, 0);
    assert.equal(rejected.elements.get('ps-clarity-consent').hidden, true);
});

test('expired, malformed and unexpected persisted values never start tracking', () => {
    for (const stored of ['broken', '{}', decision('granted', Date.now() - 1),
        decision('yes'), decision('granted', 'tomorrow'), decision('granted', Date.now() + 365 * 86400_000)]) {
        const page = browser({ stored });
        assert.equal(page.scripts.length, 0);
        assert.equal(page.elements.get('ps-clarity-consent').hidden, false);
    }
});

test('withdrawal sends denied consent and reloads; following page does not load tracker', () => {
    const page = browser({ stored: decision('granted') });
    page.click('settings');
    page.click('reject');
    assert.equal(page.consent()[1].analytics_Storage, 'denied');
    assert.equal(page.consent()[1].ad_Storage, 'denied');
    assert.equal(page.reloads, 1);
    assert.equal(browser({ stored: page.storage.get(key) }).scripts.length, 0);
});

test('unavailable storage does not crash or imply consent', () => {
    const page = browser({ blockedStorage: true });
    assert.equal(page.scripts.length, 0);
    page.click('accept');
    assert.equal(page.scripts.length, 1);
    page.click('reject');
    assert.equal(page.reloads, 1);
});

test('failed withdrawal persistence removes the previous stored grant', () => {
    const page = browser({ stored: decision('granted'), writeFails: true });
    page.click('reject');
    assert.equal(page.storage.has(key), false);
    assert.equal(page.reloads, 1);
});

test('withdrawal in another tab unloads tracking in this tab', () => {
    const page = browser({ stored: decision('granted') });
    page.storage.set(key, decision('denied'));
    page.listeners.storage({ key });
    assert.equal(page.consent()[1].analytics_Storage, 'denied');
    assert.equal(page.reloads, 1);
});
