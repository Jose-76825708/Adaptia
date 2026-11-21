<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    @vite('resources/css/app.css')
    <script src="{{ asset('js/asistente.js') }}" defer></script>
    <title>Asistente interno</title>
</head>
<body class="flex h-screen font-sans">
    @include('partials.sidebar')

    <main class="flex flex-5 flex-col gap-5 overflow-y-auto bg-[#fbfbfb] p-8">
        <section>
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#7a965f]">Consultas de solo lectura</p>
            <h1 class="text-[2.5em] font-bold text-[#103928]">Asistente interno</h1>
            <p class="mt-2 max-w-3xl text-sm text-[#5f6d5f]">Consulta stock, campañas, disponibilidad, demanda y avance de metas. El asistente no modifica datos y responde usando solo consultas disponibles.</p>
        </section>

        <section class="flex min-h-[360px] flex-1 flex-col rounded-[10px] border border-[#e4ebdf] bg-white p-5 shadow-sm" aria-labelledby="conversacion-titulo">
            <h2 id="conversacion-titulo" class="text-lg font-bold text-[#103928]">Conversación</h2>
            <div id="asistente-mensajes" class="mt-4 flex-1 overflow-y-auto whitespace-pre-wrap rounded-[10px] bg-[#f8faf6] p-4 text-sm leading-6 text-[#1d352d]" role="log" aria-live="polite">Escribe una consulta para empezar.</div>
            <p id="asistente-estado" class="mt-3 min-h-5 text-sm text-[#5f6d5f]" role="status"></p>

            <form id="asistente-form" data-endpoint="{{ route('asistente.chat') }}" method="POST" class="mt-4 flex flex-col gap-3 sm:flex-row">
                @csrf
                <input id="asistente-csrf" type="hidden" value="{{ csrf_token() }}">
                <label for="asistente-mensaje" class="sr-only">Escribe tu consulta</label>
                <textarea id="asistente-mensaje" name="message" rows="2" maxlength="1000" required placeholder="Ejemplo: ¿Qué plantas están bajo su stock mínimo?" class="min-h-12 flex-1 resize-y rounded-2xl border border-[#d8e2d7] bg-[#f8faf6] px-4 py-3 text-[#0f3c2b]"></textarea>
                <button id="asistente-enviar" type="submit" class="self-end rounded-full bg-[#7cb22b] px-6 py-3 text-sm font-semibold text-white transition hover:bg-[#6eab26] disabled:cursor-not-allowed disabled:opacity-60">Enviar</button>
            </form>
            <p class="mt-2 text-xs text-[#68766b]">El modelo se conecta al Ollama local configurado en el servidor. No envíes contraseñas ni datos personales de clientes.</p>
        </section>
    </main>
</body>
</html>
