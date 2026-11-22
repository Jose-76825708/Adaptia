<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    @vite('resources/css/app.css')
    <script src="{{ asset('js/asistente.js') }}" defer></script>
    <title>Asistente interno | Adaptia</title>
</head>
<body class="flex h-screen overflow-hidden bg-[#f4f7f3] font-sans text-[#17372a]">
    @include('partials.sidebar')

    <main class="flex min-w-0 flex-1 flex-col overflow-y-auto">
        <header class="flex flex-col gap-4 border-b border-[#e5ebe2] bg-white px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-8">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#eaf2e4] text-sm font-bold text-[#3d7143]">AI</div>
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#7d9674]">Adaptia · Asistente interno</p>
                    <h1 class="mt-0.5 text-xl font-bold text-[#103928]">Consulta tus procesos y datos</h1>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="hidden items-center gap-2 rounded-full bg-[#eff6ed] px-3 py-2 text-xs font-semibold text-[#477346] sm:inline-flex">
                    <span class="h-2 w-2 rounded-full bg-[#69a84e]"></span>
                    Consultas de solo lectura
                </span>
                <button id="asistente-nueva-conversacion" type="button" data-endpoint="{{ route('asistente.nueva-conversacion') }}" class="rounded-xl border border-[#dce6d9] bg-white px-4 py-2.5 text-sm font-semibold text-[#3d6647] transition hover:bg-[#f6faf3]">Nueva conversación</button>
            </div>
        </header>

        <div class="mx-auto grid min-h-0 w-full max-w-[1500px] flex-1 grid-cols-1 gap-5 p-4 sm:p-6 xl:grid-cols-[minmax(0,1fr)_290px]">
            <section class="flex min-h-[620px] min-w-0 flex-col overflow-hidden rounded-3xl border border-[#e1e9de] bg-white shadow-sm" aria-labelledby="conversacion-titulo">
                <div class="flex items-center gap-3 border-b border-[#edf1ea] px-5 py-4 sm:px-7">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-[#286443] to-[#70a547] text-sm font-bold text-white">A</div>
                    <div>
                        <h2 id="conversacion-titulo" class="font-bold text-[#173d2b]">Asistente Adaptia</h2>
                        <p class="mt-0.5 text-xs text-[#879286]">Inventario · Ventas · Planeación · Mantenimiento · IoT</p>
                    </div>
                    <span class="ml-auto rounded-full bg-[#f3f6f0] px-3 py-1.5 text-[11px] font-semibold text-[#72806f]">{{ ucfirst(auth()->user()->rol) }}</span>
                </div>

                <div class="grid grid-cols-2 gap-2 border-b border-[#edf1ea] bg-white p-3 xl:hidden">
                    <button type="button" data-asistente-sugerencia="¿Qué plantas están por debajo de su stock mínimo y qué recomiendas priorizar?" class="rounded-xl bg-[#f5f8f2] px-3 py-2 text-left text-xs font-semibold text-[#456a3a]">Stock bajo</button>
                    <button type="button" data-asistente-sugerencia="Explícame el flujo de planeación comercial y qué paso corresponde según el estado de una campaña." class="rounded-xl bg-[#f5f8f2] px-3 py-2 text-left text-xs font-semibold text-[#456a3a]">Etapas comerciales</button>
                </div>

                <div id="asistente-mensajes" class="flex-1 space-y-5 overflow-y-auto bg-[#fbfcfa] px-4 py-6 sm:px-7" role="log" aria-live="polite" aria-relevant="additions text">
                    @forelse ($historial as $mensaje)
                        <article class="flex {{ $mensaje['role'] === 'user' ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-[88%] rounded-2xl px-4 py-3 text-sm leading-6 shadow-sm sm:max-w-[78%] {{ $mensaje['role'] === 'user' ? 'rounded-br-md bg-[#18563b] text-white' : 'rounded-bl-md border border-[#e8eee4] bg-white text-[#32473a]' }}">
                                <p class="mb-1 text-[10px] font-bold uppercase tracking-[0.12em] {{ $mensaje['role'] === 'user' ? 'text-white/65' : 'text-[#7a946e]' }}">{{ $mensaje['role'] === 'user' ? 'Tú' : 'Asistente' }}</p>
                                <p class="whitespace-pre-wrap break-words">{{ $mensaje['content'] }}</p>
                            </div>
                        </article>
                    @empty
                        <article class="flex justify-start">
                            <div class="max-w-[88%] rounded-2xl rounded-bl-md border border-[#e8eee4] bg-white px-4 py-3 text-sm leading-6 text-[#32473a] shadow-sm sm:max-w-[78%]">
                                <p class="mb-1 text-[10px] font-bold uppercase tracking-[0.12em] text-[#7a946e]">Asistente</p>
                                <p>Hola, {{ auth()->user()->name }}. Puedo ayudarte a entender los macroprocesos y consultar información operativa según tu rol. Si buscas una sugerencia, revisaré los datos disponibles y te explicaré en qué se basa.</p>
                            </div>
                        </article>
                    @endforelse
                </div>

                <p id="asistente-estado" class="min-h-6 bg-[#fbfcfa] px-5 text-xs text-[#788675] sm:px-7" role="status" aria-live="polite"></p>
                <form id="asistente-form" data-endpoint="{{ route('asistente.chat') }}" method="POST" class="border-t border-[#edf1ea] bg-white p-4 sm:p-6">
                    @csrf
                    <input id="asistente-csrf" type="hidden" value="{{ csrf_token() }}">
                    <label for="asistente-mensaje" class="sr-only">Escribe una consulta</label>
                    <div class="flex items-end gap-3 rounded-2xl border border-[#dce6d9] bg-[#fbfcfa] p-2 transition focus-within:border-[#8eaf76] focus-within:ring-2 focus-within:ring-[#8eaf76]/20">
                        <textarea id="asistente-mensaje" name="message" rows="2" maxlength="1000" required placeholder="Pregunta por un proceso, un indicador o una sugerencia..." class="max-h-36 min-h-12 flex-1 resize-y border-0 bg-transparent px-3 py-2.5 text-sm leading-6 text-[#17372a] outline-none placeholder:text-[#97a194] focus:ring-0"></textarea>
                        <button id="asistente-enviar" type="submit" class="flex h-11 shrink-0 items-center gap-2 rounded-xl bg-[#18563b] px-4 text-sm font-semibold text-white transition hover:bg-[#246b49] disabled:cursor-not-allowed disabled:opacity-50">
                            <span>Enviar</span><span aria-hidden="true">↑</span>
                        </button>
                    </div>
                    <div class="mt-3 flex flex-col gap-1 text-[11px] text-[#929b90] sm:flex-row sm:items-center sm:justify-between">
                        <span>Enter para enviar · Shift + Enter para una nueva línea</span>
                        <span>No compartas contraseñas ni datos personales de clientes</span>
                    </div>
                </form>
            </section>

            <aside class="hidden space-y-4 xl:block">
                <section class="rounded-3xl border border-[#e1e9de] bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#7d9674]">Consultas rápidas</p>
                    <h2 class="mt-2 text-lg font-bold text-[#173d2b]">¿Por dónde empezamos?</h2>
                    <div class="mt-4 space-y-2">
                        <button type="button" data-asistente-sugerencia="¿Qué plantas están por debajo de su stock mínimo y qué recomiendas priorizar?" class="w-full rounded-xl border border-[#edf1ea] p-3 text-left text-xs leading-5 text-[#536454] transition hover:border-[#cbdcc3] hover:bg-[#f8fbf6]">Revisar plantas con stock bajo <span class="mt-1 block font-semibold text-[#3f7043]">Inventario · sugerencia</span></button>
                        <button type="button" data-asistente-sugerencia="Explícame el flujo de planeación comercial y qué paso corresponde según el estado de una campaña." class="w-full rounded-xl border border-[#edf1ea] p-3 text-left text-xs leading-5 text-[#536454] transition hover:border-[#cbdcc3] hover:bg-[#f8fbf6]">Entender una etapa comercial <span class="mt-1 block font-semibold text-[#3f7043]">Planeación comercial</span></button>
                        <button type="button" data-asistente-sugerencia="¿Qué debería revisar primero para atender los casos de mantenimiento activos?" class="w-full rounded-xl border border-[#edf1ea] p-3 text-left text-xs leading-5 text-[#536454] transition hover:border-[#cbdcc3] hover:bg-[#f8fbf6]">Priorizar tareas pendientes <span class="mt-1 block font-semibold text-[#3f7043]">Mantenimiento</span></button>
                        <button type="button" data-asistente-sugerencia="Explícame cómo se generan y resuelven las alertas del monitoreo IoT." class="w-full rounded-xl border border-[#edf1ea] p-3 text-left text-xs leading-5 text-[#536454] transition hover:border-[#cbdcc3] hover:bg-[#f8fbf6]">Consultar monitoreo y alertas <span class="mt-1 block font-semibold text-[#3f7043]">Monitoreo IoT</span></button>
                    </div>
                </section>

                <section class="rounded-3xl bg-[#eaf2e4] p-5">
                    <div class="flex items-center gap-2">
                        <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-white text-xs font-bold text-[#4c7a3d]">i</span>
                        <h2 class="text-sm font-bold text-[#315b38]">Cómo responde</h2>
                    </div>
                    <p class="mt-3 text-xs leading-5 text-[#5f745b]">Puede explicar los flujos definidos y consultar datos permitidos para tu rol. Las recomendaciones son orientativas: el asistente no cambia registros ni ejecuta acciones.</p>
                </section>
            </aside>
        </div>
    </main>
</body>
</html>
