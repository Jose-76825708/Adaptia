import assert from 'node:assert/strict';
import test from 'node:test';
import { FakeDocument, FakeElement, instalarEntornoDom } from './fake-dom.js';

test('envía solo texto de usuario, muestra respuestas sin HTML y maneja fallos amablemente', async () => {
    const form = new FakeElement({ id: 'asistente-form', dataset: { endpoint: '/asistente/chat' } });
    const input = new FakeElement({ id: 'asistente-mensaje', value: '  ¿Cómo va la campaña?  ' });
    const messages = new FakeElement({ id: 'asistente-mensajes', textContent: 'Conversación:' });
    const status = new FakeElement({ id: 'asistente-estado' });
    const submitButton = new FakeElement({ id: 'asistente-enviar' });
    const csrfToken = new FakeElement({ id: 'asistente-csrf', value: 'csrf-de-prueba' });
    const document = new FakeDocument({ elements: [form, input, messages, status, submitButton, csrfToken] });
    const requests = [];
    const responses = [
        { ok: true, json: async () => ({ respuesta: '<b>La campaña va al 80%.</b>' }) },
        { ok: false, json: async () => ({ message: 'El asistente no pudo responder ahora.' }) },
    ];
    const environment = instalarEntornoDom(document, {
        fetch: async (...argumentos) => {
            requests.push(argumentos);
            const response = responses.shift();
            if (!response) {
                throw new Error('red privada de prueba');
            }
            return response;
        },
    });

    try {
        await import('../../public/js/asistente.js');
        const enviar = form.listeners.get('submit')[0];

        await enviar({ preventDefault() {} });

        assert.deepEqual(JSON.parse(requests[0][1].body), { message: '¿Cómo va la campaña?' });
        assert.equal(requests[0][1].headers['X-CSRF-TOKEN'], 'csrf-de-prueba');
        assert.equal(requests[0][1].credentials, 'same-origin');
        assert.equal(messages.textContent, 'Conversación:\nTú: ¿Cómo va la campaña?\nAsistente: <b>La campaña va al 80%.</b>');
        assert.equal(status.textContent, 'Respuesta recibida.');
        assert.equal(input.value, '');
        assert.equal(submitButton.disabled, false);

        input.value = 'Consulta siguiente';
        await enviar({ preventDefault() {} });
        assert.equal(status.textContent, 'El asistente no pudo responder ahora.');
        assert.equal(submitButton.disabled, false);

        input.value = 'Consulta con error de red';
        await enviar({ preventDefault() {} });
        assert.equal(status.textContent, 'No se pudo conectar con el asistente. Inténtalo nuevamente.');
        assert.equal(submitButton.disabled, false);
    } finally {
        environment.restore();
    }
});
