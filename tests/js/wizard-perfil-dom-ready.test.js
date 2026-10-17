import assert from 'node:assert/strict';
import test from 'node:test';
import {
    crearDocumentoWizard,
    instalarEntornoDom,
} from './fake-dom.js';

test('inicializa el wizard cuando el documento termina de cargar', async () => {
    const document = crearDocumentoWizard();
    document.readyState = 'loading';
    const environment = instalarEntornoDom(document, { fetch: async () => {} });

    try {
        await import('../../resources/js/wizard-perfil.js');

        assert.equal(document.getElementById('wizard-next').listeners.has('click'), false);
        document.dispatchEvent({ type: 'DOMContentLoaded' });
        assert.equal(document.getElementById('wizard-next').listeners.has('click'), true);
        assert.equal(document.getElementById('wizard-current-step').textContent, '1');
    } finally {
        environment.restore();
    }
});
