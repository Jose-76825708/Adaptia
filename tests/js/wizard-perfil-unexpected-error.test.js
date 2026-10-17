import assert from 'node:assert/strict';
import test from 'node:test';
import { crearDocumentoWizard, instalarEntornoDom } from './fake-dom.js';

test('maneja errores inesperados al preparar el envio del perfil', async () => {
    const document = crearDocumentoWizard();
    const alerts = [];
    const loggedErrors = [];
    const environment = instalarEntornoDom(document, { fetch: async () => assert.fail('No debe iniciar fetch') });
    const originalGetElementById = document.getElementById.bind(document);
    const originalError = console.error;
    globalThis.alert = message => alerts.push(message);
    console.error = (...args) => loggedErrors.push(args);

    try {
        await import('../../resources/js/wizard-perfil.js');
        const nextButton = document.getElementById('wizard-next');
        nextButton.click();

        document.getElementById = id => {
            if (id === 'wizard-tamanio') {
                throw new Error('Campo no disponible');
            }

            return originalGetElementById(id);
        };

        nextButton.click();
        await new Promise(resolve => setImmediate(resolve));

        assert.equal(alerts[0], 'Error de conexión. Por favor intenta nuevamente.');
        assert.equal(loggedErrors.length, 1);
        assert.equal(loggedErrors[0][0].message, 'Campo no disponible');
        assert.equal(nextButton.disabled, false);
        assert.equal(nextButton.textContent, 'Actualizar perfil');
    } finally {
        document.getElementById = originalGetElementById;
        console.error = originalError;
        environment.restore();
    }
});
