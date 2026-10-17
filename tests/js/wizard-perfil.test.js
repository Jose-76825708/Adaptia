import assert from 'node:assert/strict';
import test from 'node:test';
import {
    crearDocumentoWizard,
    instalarEntornoDom,
} from './fake-dom.js';

test('valida, navega, envía cambios del perfil y presenta errores del servidor', async () => {
    const document = crearDocumentoWizard({ requiredValue: '' });
    const requests = [];
    const alerts = [];
    const responses = [
        { ok: true, json: async () => ({ success: false, message: 'No se pudo guardar.' }) },
        { ok: true, json: async () => ({ success: true }) },
    ];
    const environment = instalarEntornoDom(document, {
        reducedMotion: false,
        fetch: async (...args) => {
            requests.push(args);
            return responses.shift();
        },
    });
    globalThis.alert = message => alerts.push(message);

    try {
        await import('../../resources/js/wizard-perfil.js');

        const nextButton = document.getElementById('wizard-next');
        const previousButton = document.getElementById('wizard-prev');
        nextButton.click();

        assert.equal(alerts[0], 'Por favor completa todos los campos de este paso antes de continuar');
        assert.equal(document.steps[0].requiredSelects[0].classList.contains('border-red-500'), true);

        document.steps[0].requiredSelects[0].value = 'completo';
        document.steps[1].requiredSelects[0].value = 'completo';
        nextButton.click();
        previousButton.click();
        assert.equal(document.getElementById('wizard-current-step').textContent, '2');
        environment.timeouts.shift()();
        assert.equal(document.getElementById('wizard-current-step').textContent, '2');
        assert.equal(document.steps[1].classList.contains('active'), true);
        previousButton.click();
        environment.timeouts.shift()();
        assert.equal(document.getElementById('wizard-current-step').textContent, '1');

        nextButton.click();
        environment.timeouts.shift()();
        nextButton.click();
        await new Promise(resolve => setImmediate(resolve));

        assert.equal(alerts[1], 'No se pudo guardar.');
        assert.equal(nextButton.disabled, false);
        assert.equal(nextButton.textContent, 'Actualizar perfil');
        assert.equal(JSON.parse(requests[0][1].body).toxicidad, 1);
        assert.equal(requests[0][1].headers['X-CSRF-TOKEN'], 'csrf-de-prueba');

        document.getElementById('wizard-next').click();
        await new Promise(resolve => setImmediate(resolve));

        assert.equal(requests.length, 2);
        assert.equal(JSON.parse(requests[1][1].body).tamaño_adulto, 'mediana');
        assert.equal(globalThis.window.location.href, '/home');
    } finally {
        environment.restore();
    }
});
