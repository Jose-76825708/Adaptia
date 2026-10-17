import assert from 'node:assert/strict';
import test from 'node:test';
import {
    crearFragmentoMonitoreo,
    FakeDocument,
    instalarEntornoDom,
} from './fake-dom.js';

test('no inicia el sondeo cuando el intervalo configurado no es válido', async () => {
    const fragment = crearFragmentoMonitoreo();
    fragment.dataset.monitoringPollInterval = '0';
    const document = new FakeDocument({
        elements: [fragment],
        pollingFragment: fragment,
    });
    const environment = instalarEntornoDom(document, { fetch: async () => {} });
    const originalError = console.error;
    const errors = [];
    console.error = (...args) => errors.push(args);

    try {
        await import('../../resources/js/monitoreo-polling.js');
        document.dispatchEvent({ type: 'DOMContentLoaded' });

        assert.equal(environment.intervals.length, 0);
        assert.equal(errors.length, 1);
        assert.equal(errors[0][0].includes('configuración inválida'), true);
    } finally {
        console.error = originalError;
        environment.restore();
    }
});
