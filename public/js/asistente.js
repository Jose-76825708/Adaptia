const form = document.getElementById('asistente-form');
const input = document.getElementById('asistente-mensaje');
const messages = document.getElementById('asistente-mensajes');
const status = document.getElementById('asistente-estado');
const submitButton = document.getElementById('asistente-enviar');
const csrfToken = document.getElementById('asistente-csrf');
const newConversationButton = document.getElementById('asistente-nueva-conversacion');
const greeting = 'Hola. Soy el asistente interno de Adaptia. Puedo explicar los procesos y consultar información operativa según tu rol.';

function agregarTextoEnriquecido(contenedor, contenido) {
    const partes = contenido.split(/(\*\*[^*\n]+\*\*)/g);

    partes.forEach(parte => {
        if (!parte) {
            return;
        }

        if (parte.startsWith('**') && parte.endsWith('**')) {
            const negrita = document.createElement('strong');
            negrita.className = 'font-bold text-[#183f2c]';
            negrita.textContent = parte.slice(2, -2);
            contenedor.appendChild(negrita);
            return;
        }

        const texto = document.createElement('span');
        texto.textContent = parte;
        contenedor.appendChild(texto);
    });
}

function renderizarRespuesta(contenedor, contenido) {
    const lineas = contenido.replace(/\r\n?/g, '\n').split('\n');
    let lista = null;
    let tipoLista = null;

    const cerrarLista = () => {
        lista = null;
        tipoLista = null;
    };

    lineas.forEach(linea => {
        const texto = linea.trim();
        if (!texto) {
            cerrarLista();
            return;
        }

        const titulo = texto.match(/^#{1,3}\s+(.+)$/);
        if (titulo) {
            cerrarLista();
            const encabezado = document.createElement('h3');
            encabezado.className = 'mt-3 text-base font-bold text-[#183f2c]';
            agregarTextoEnriquecido(encabezado, titulo[1]);
            contenedor.appendChild(encabezado);
            return;
        }

        const viñeta = texto.match(/^[-*+]\s+(.+)$/);
        const numerada = texto.match(/^\d+[.)]\s+(.+)$/);
        const tipoNuevo = viñeta ? 'ul' : numerada ? 'ol' : null;

        if (tipoNuevo) {
            if (!lista || tipoLista !== tipoNuevo) {
                cerrarLista();
                lista = document.createElement(tipoNuevo);
                lista.className = tipoNuevo === 'ul'
                    ? 'my-2 list-disc space-y-1 pl-5'
                    : 'my-2 list-decimal space-y-1 pl-5';
                tipoLista = tipoNuevo;
                contenedor.appendChild(lista);
            }

            const elemento = document.createElement('li');
            agregarTextoEnriquecido(elemento, (viñeta || numerada)[1]);
            lista.appendChild(elemento);
            return;
        }

        cerrarLista();
        const parrafo = document.createElement('p');
        parrafo.className = 'my-2 whitespace-pre-wrap';
        agregarTextoEnriquecido(parrafo, texto);
        contenedor.appendChild(parrafo);
    });
}

function agregarMensaje(rol, contenido) {
    const articulo = document.createElement('article');
    articulo.className = `flex ${rol === 'user' ? 'justify-end' : 'justify-start'}`;

    const burbuja = document.createElement('div');
    burbuja.className = `max-w-[88%] rounded-2xl px-4 py-3 text-sm leading-6 shadow-sm sm:max-w-[78%] ${
        rol === 'user'
            ? 'rounded-br-md bg-[#18563b] text-white'
            : 'rounded-bl-md border border-[#e8eee4] bg-white text-[#32473a]'
    }`;

    const etiqueta = document.createElement('p');
    etiqueta.className = `mb-1 text-[10px] font-bold uppercase tracking-[0.12em] ${
        rol === 'user' ? 'text-white/65' : 'text-[#7a946e]'
    }`;
    etiqueta.textContent = rol === 'user' ? 'Tú' : 'Asistente';

    burbuja.appendChild(etiqueta);
    if (rol === 'assistant') {
        const respuesta = document.createElement('div');
        respuesta.className = 'break-words';
        renderizarRespuesta(respuesta, contenido);
        burbuja.appendChild(respuesta);
    } else {
        const texto = document.createElement('p');
        texto.className = 'whitespace-pre-wrap break-words';
        texto.textContent = contenido;
        burbuja.appendChild(texto);
    }
    articulo.appendChild(burbuja);
    messages.appendChild(articulo);
    messages.scrollTop = messages.scrollHeight;
}

if (form && input && messages && status && submitButton && csrfToken) {
    form.addEventListener('submit', async event => {
        event.preventDefault();
        const message = input.value.trim();

        if (!message) {
            status.textContent = 'Escribe una consulta antes de enviarla.';
            return;
        }

        agregarMensaje('user', message);
        input.value = '';
        status.textContent = 'Revisando los datos y procesos disponibles...';
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

            agregarMensaje('assistant', result.respuesta);
            status.textContent = 'Respuesta recibida.';
        } catch {
            status.textContent = 'No se pudo conectar con el asistente. Inténtalo nuevamente.';
        } finally {
            submitButton.disabled = false;
        }
    });

    input.addEventListener('keydown', event => {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            form.dispatchEvent(new Event('submit', { cancelable: true }));
        }
    });

    document.querySelectorAll('[data-asistente-sugerencia]').forEach(button => {
        button.addEventListener('click', () => {
            input.value = button.dataset.asistenteSugerencia;
            input.focus();
        });
    });
}

if (newConversationButton && messages && status && csrfToken) {
    newConversationButton.addEventListener('click', async () => {
        newConversationButton.disabled = true;
        status.textContent = 'Iniciando una conversación nueva...';

        try {
            const response = await fetch(newConversationButton.dataset.endpoint, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken.value,
                },
                credentials: 'same-origin',
                body: JSON.stringify({}),
            });
            const result = await response.json();

            if (!response.ok || result.reiniciada !== true) {
                status.textContent = result.message || 'No se pudo iniciar una conversación nueva.';
                return;
            }

            messages.replaceChildren();
            agregarMensaje('assistant', greeting);
            status.textContent = 'Conversación nueva.';
        } catch {
            status.textContent = 'No se pudo conectar con el asistente. Inténtalo nuevamente.';
        } finally {
            newConversationButton.disabled = false;
        }
    });
}
