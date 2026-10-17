import assert from 'node:assert/strict';
import test from 'node:test';
import {
    crearFragmentoMonitoreo,
    FakeDocument,
    instalarEntornoDom,
} from './fake-dom.js';

const fetchResponses = [
    {
        ok: true,
        text: async () => `
            <section id="monitoring-fragment" class="fresh"
                data-monitoring-poll-url="/poll"
                data-monitoring-poll-interval="1000">Actualizado</section>
        `,
    },
    { ok: false, status: 503 },
    {
        ok: true,
        text: async () => `
            <section id="unexpected-fragment"
                data-monitoring-poll-url="/poll"
                data-monitoring-poll-interval="1000">Inesperado</section>
        `,
    },
    {
        ok: true,
        text: async () => `
            <section id="monitoring-fragment"
                data-monitoring-poll-url="/poll"
                data-monitoring-poll-interval="1000">Visible de nuevo</section>
        `,
    },
];
const initialFragment = crearFragmentoMonitoreo({ className: 'show' });
const document = new FakeDocument({
    elements: [initialFragment],
    pollingFragment: initialFragment,
});
const fetchCalls = [];
const environment = instalarEntornoDom(document, {
    fetch: async (...args) => {
        fetchCalls.push(args);
        return fetchResponses.shift();
    },
});

await import('../../resources/js/monitoreo-polling.js');
document.dispatchEvent({ type: 'DOMContentLoaded' });

test('refresca el fragmento visible y conserva la vista previa cuando falla la siguiente consulta', async () => {
    assert.equal(environment.intervals.length, 1);
    await environment.intervals[0]();

    const refreshed = document.getElementById('monitoring-fragment');
    assert.equal(refreshed.textContent, 'Actualizado');
    assert.equal(refreshed.classList.contains('show'), true);
    assert.equal(fetchCalls[0][0], '/poll');
    assert.equal(fetchCalls[0][1].cache, 'no-store');
    assert.equal(fetchCalls[0][1].credentials, 'same-origin');

    const originalError = console.error;
    const loggedErrors = [];
    console.error = (...args) => {
        loggedErrors.push(args);
    };

    try {
        await environment.intervals[0]();
        assert.equal(document.getElementById('monitoring-fragment'), refreshed);
        await environment.intervals[0]();
        console.error = originalError;
        assert.equal(document.getElementById('monitoring-fragment'), refreshed);
        assert.equal(loggedErrors.length, 2);
        assert.equal(loggedErrors[0][0], 'No se pudo actualizar el monitoreo; se mantienen los datos visibles.');
        assert.equal(loggedErrors[1][1].name, 'TypeError');
        assert.equal(fetchCalls.length, 3);

        document.hidden = true;
        await environment.intervals[0]();
        assert.equal(fetchCalls.length, 3);

        document.hidden = false;
        document.dispatchEvent({ type: 'visibilitychange' });
        await new Promise(resolve => setImmediate(resolve));
        assert.equal(fetchCalls.length, 4);
        assert.equal(document.getElementById('monitoring-fragment').textContent, 'Visible de nuevo');

        let resolvePendingResponse;
        fetchResponses.push(new Promise(resolve => {
            resolvePendingResponse = resolve;
        }));
        const pendingRefresh = environment.intervals[0]();
        await Promise.resolve();
        await environment.intervals[0]();
        assert.equal(fetchCalls.length, 5);
        resolvePendingResponse({
            ok: true,
            text: async () => `
                <section id="monitoring-fragment"
                    data-monitoring-poll-url="/poll"
                    data-monitoring-poll-interval="1000">Final</section>
            `,
        });
        await pendingRefresh;
        assert.equal(document.getElementById('monitoring-fragment').textContent, 'Final');
    } finally {
        console.error = originalError;
        environment.restore();
    }
});
