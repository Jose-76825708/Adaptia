import assert from 'node:assert/strict';
import test from 'node:test';
import { FakeDocument, FakeElement, instalarEntornoDom } from './fake-dom.js';

function buscarEtiquetas(elemento, etiqueta) {
    return [
        ...(elemento.tagName === etiqueta ? [elemento] : []),
        ...elemento.children.flatMap(hijo => buscarEtiquetas(hijo, etiqueta)),
    ];
}

test('presenta mensajes como burbujas seguras, permite sugerencias y maneja errores', async () => {
    const form = new FakeElement({ id: 'asistente-form', dataset: { endpoint: '/asistente/chat' } });
    const input = new FakeElement({ id: 'asistente-mensaje', value: '  ¿Cómo va la campaña?  ' });
    const messages = new FakeElement({ id: 'asistente-mensajes' });
    const status = new FakeElement({ id: 'asistente-estado' });
    const submitButton = new FakeElement({ id: 'asistente-enviar' });
    const csrfToken = new FakeElement({ id: 'asistente-csrf', value: 'csrf-de-prueba' });
    const newConversation = new FakeElement({
        id: 'asistente-nueva-conversacion',
        dataset: { endpoint: '/asistente/nueva-conversacion' },
    });
    const suggestion = new FakeElement({
        id: 'sugerencia-stock',
        dataset: { asistenteSugerencia: '¿Qué plantas tienen stock bajo?' },
    });
    const document = new FakeDocument({
        elements: [form, input, messages, status, submitButton, csrfToken, newConversation, suggestion],
    });
    const requests = [];
    const responses = [
        {
            ok: true,
            json: async () => ({
                respuesta: '**Hechos consultados**\n\n* Se consultó el ranking.\n1. **Recomendación**\n   * **Sugerencia:** <img src=x onerror=alert(1)>',
            }),
        },
        { ok: false, json: async () => ({ message: 'El asistente no pudo responder ahora.' }) },
        null,
        { ok: true, json: async () => ({ reiniciada: true }) },
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
        assert.equal(messages.children.length, 2);
        assert.equal(messages.children[0].children[0].children[1].textContent, '¿Cómo va la campaña?');
        const respuesta = messages.children[1].children[0].children[1];
        assert.deepEqual(buscarEtiquetas(respuesta, 'strong').map(elemento => elemento.textContent), [
            'Hechos consultados',
            'Recomendación',
            'Sugerencia:',
        ]);
        assert.equal(buscarEtiquetas(respuesta, 'ul').length, 2);
        assert.equal(buscarEtiquetas(respuesta, 'ol').length, 1);
        assert.equal(buscarEtiquetas(respuesta, 'img').length, 0);
        assert.ok(respuesta.textContent.includes('<img src=x onerror=alert(1)>'));
        assert.ok(!respuesta.textContent.includes('**'));
        assert.equal(status.textContent, 'Respuesta recibida.');
        assert.equal(input.value, '');
        assert.equal(submitButton.disabled, false);

        suggestion.click();
        assert.equal(input.value, '¿Qué plantas tienen stock bajo?');

        input.value = 'Consulta siguiente';
        await enviar({ preventDefault() {} });
        assert.equal(status.textContent, 'El asistente no pudo responder ahora.');
        assert.equal(submitButton.disabled, false);

        input.value = 'Consulta con error de red';
        await enviar({ preventDefault() {} });
        assert.equal(status.textContent, 'No se pudo conectar con el asistente. Inténtalo nuevamente.');
        assert.equal(submitButton.disabled, false);

        await newConversation.click();
        assert.equal(requests[3][0], '/asistente/nueva-conversacion');
        assert.equal(messages.children.length, 1);
        assert.equal(messages.children[0].textContent, 'AsistenteHola. Soy el asistente interno de Adaptia. Puedo explicar los procesos y consultar información operativa según tu rol.');
        assert.equal(status.textContent, 'Conversación nueva.');
        assert.equal(newConversation.disabled, false);
    } finally {
        environment.restore();
    }
});
