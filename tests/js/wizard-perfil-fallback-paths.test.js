import assert from 'node:assert/strict';
import test from 'node:test';
import {
    crearDocumentoWizard,
    instalarEntornoDom,
} from './fake-dom.js';

test('maneja campos faltantes, respuestas de error, fallos de red y guardado exitoso', async () => {
    const document = crearDocumentoWizard();
    const alerts = [];
    const requests = [];
    const responses = [
        { ok: false, json: async () => ({ errors: { luz_requerida: ['Luz inválida'] } }) },
        { ok: false, json: async () => ({}) },
        { ok: true, json: async () => ({ success: false }) },
        new Error('Conexión interrumpida'),
        { ok: true, json: async () => ({ success: true }) },
    ];
    const environment = instalarEntornoDom(document, {
        fetch: async (...args) => {
            requests.push(args);
            const response = responses.shift();

            if (response instanceof Error) {
                throw response;
            }

            return response;
        },
    });
    globalThis.alert = message => alerts.push(message);
    const originalError = console.error;
    const loggedErrors = [];
    console.error = (...args) => loggedErrors.push(args);

    const submit = async () => {
        document.getElementById('wizard-next').click();
        await new Promise(resolve => setImmediate(resolve));
    };

    try {
        await import('../../resources/js/wizard-perfil.js');
        const nextButton = document.getElementById('wizard-next');

        document.getElementById('wizard-prev').click();
        assert.equal(document.getElementById('wizard-current-step').textContent, '1');
        nextButton.click();
        assert.equal(document.getElementById('wizard-current-step').textContent, '2');

        for (const field of [
            'wizard-tamanio',
            'wizard-luz',
            'wizard-riego',
            'wizard-ambiente',
            'wizard-nivel',
            'wizard-estetica',
        ]) {
            document.getElementById(field).value = '';
        }
        await submit();
        assert.equal(alerts[0], 'Por favor completa todos los campos requeridos');
        assert.equal(nextButton.disabled, false);
        assert.equal(requests.length, 0);

        for (const [field, value] of [
            ['wizard-tamanio', 'mediana'],
            ['wizard-luz', 'media'],
            ['wizard-riego', 'semanal'],
            ['wizard-ambiente', 'interiores'],
            ['wizard-nivel', 'principiante'],
            ['wizard-estetica', 'follaje'],
        ]) {
            document.getElementById(field).value = value;
        }

        await submit();
        assert.equal(alerts[1], 'Luz inválida');
        await submit();
        assert.equal(alerts[2], 'Error al actualizar el perfil');
        await submit();
        assert.equal(alerts[3], 'Error desconocido');
        await submit();
        assert.equal(alerts[4], 'Error de conexión. Por favor intenta nuevamente.');
        assert.equal(loggedErrors.length, 1);
        assert.equal(loggedErrors[0][0].message, 'Conexión interrumpida');
        assert.equal(nextButton.disabled, false);

        await submit();
        assert.equal(requests.length, 5);
        assert.equal(globalThis.window.location.href, '/home');
    } finally {
        console.error = originalError;
        environment.restore();
    }
});
