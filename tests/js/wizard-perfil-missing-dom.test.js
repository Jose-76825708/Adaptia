import assert from 'node:assert/strict';
import test from 'node:test';
import {
    crearDocumentoWizard,
    instalarEntornoDom,
} from './fake-dom.js';

test('no inicializa el wizard si falta la barra de progreso', async () => {
    const document = crearDocumentoWizard();
    document.elements.delete('wizard-progress-bar');
    const environment = instalarEntornoDom(document, { fetch: async () => {} });

    try {
        await import('../../resources/js/wizard-perfil.js');

        assert.equal(document.getElementById('wizard-next').listeners.has('click'), false);
        assert.equal(document.getElementById('wizard-current-step').textContent, '');
    } finally {
        environment.restore();
    }
});
