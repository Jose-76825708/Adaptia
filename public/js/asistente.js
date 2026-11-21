const form = document.getElementById('asistente-form');
const input = document.getElementById('asistente-mensaje');
const messages = document.getElementById('asistente-mensajes');
const status = document.getElementById('asistente-estado');
const submitButton = document.getElementById('asistente-enviar');
const csrfToken = document.getElementById('asistente-csrf');

if (form && input && messages && status && submitButton && csrfToken) {
    form.addEventListener('submit', async event => {
        event.preventDefault();
        const message = input.value.trim();

        if (!message) {
            status.textContent = 'Escribe una consulta antes de enviarla.';
            return;
        }

        messages.textContent += `\nTú: ${message}`;
        input.value = '';
        status.textContent = 'Consultando los datos disponibles...';
        submitButton.disabled = true;

        try {
            const response = await fetch(form.dataset.endpoint, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken.value,
                },
                credentials: 'same-origin',
                body: JSON.stringify({ message }),
            });
            const result = await response.json();

            if (!response.ok) {
                status.textContent = result.message
                    || result.errors?.message?.[0]
                    || 'No se pudo enviar la consulta. Inténtalo nuevamente.';
                return;
            }

            messages.textContent += `\nAsistente: ${result.respuesta}`;
            status.textContent = 'Respuesta recibida.';
        } catch {
            status.textContent = 'No se pudo conectar con el asistente. Inténtalo nuevamente.';
        } finally {
            submitButton.disabled = false;
        }
    });
}
