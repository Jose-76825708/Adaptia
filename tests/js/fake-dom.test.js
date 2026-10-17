import assert from 'node:assert/strict';
import test from 'node:test';
import { FakeDOMParser } from './fake-dom.js';

test('parses attributes after a long malformed attribute name', () => {
    const malformedAttribute = 'x'.repeat(20000);
    const document = new FakeDOMParser().parseFromString(
        `<section ${malformedAttribute} id="target" data-monitoring-poll-url="/poll">contenido</section>`
    );

    const element = document.getElementById('target');

    assert.equal(element.textContent, 'contenido');
    assert.equal(element.dataset.monitoringPollUrl, '/poll');
});
