import assert from 'node:assert/strict';
import test from 'node:test';
import {
    FakeDocument,
    instalarEntornoDom,
} from './fake-dom.js';

test('omite el sondeo en páginas sin fragmento de monitoreo', async () => {
    const document = new FakeDocument();
    const environment = instalarEntornoDom(document, { fetch: async () => {} });

    try {
        await import('../../resources/js/monitoreo-polling.js');
        document.dispatchEvent({ type: 'DOMContentLoaded' });

        assert.equal(environment.intervals.length, 0);
    } finally {
        environment.restore();
    }
});
