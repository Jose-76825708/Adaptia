<section id="ejec-panel" class="rounded-[10px] border border-[#e4ebdf] bg-[#f8faf6] p-5 shadow-sm" aria-labelledby="ejec-titulo">
    <h2 id="ejec-titulo" class="text-xl font-bold text-[#103928]">Ejecución</h2>
    <p class="mt-2 text-sm text-[#5f6d5f]">{{ $caso->estado === 'preparado' ? 'Registra el inicio para comenzar la ejecución.' : ($caso->estado === 'en_ejecucion' ? 'Registra avances, incidencias o notas durante la ejecución.' : 'La ejecución estará disponible después de marcar el caso como preparado.') }}</p>

    @if ($errors->ejecucion->any())
        <div id="ejec-errores" class="mt-3 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700" role="alert">
            @foreach ($errors->ejecucion->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form id="ejec-evento" method="POST" action="{{ route('mantenimiento.casos.ejecucion.evento', $caso) }}" class="mt-4 rounded-[10px] border border-[#e4ebdf] bg-white p-5">
        @csrf
        <label for="ejec-evento-tipo" class="block text-sm font-medium text-slate-700">Tipo de evento</label>
        <select id="ejec-evento-tipo" name="tipo" class="mt-1 block w-full rounded-2xl border border-[#d8e2d7] bg-[#f8faf6] px-3 py-2.5 text-[#0f3c2b] focus:border-[#7cb22b] focus:outline-none focus:ring-2 focus:ring-[#e4ebdf]">
            @if ($caso->estado === 'preparado')
                <option value="inicio">Inicio</option>
            @elseif ($caso->estado === 'en_ejecucion')
                <option value="avance">Avance</option>
                <option value="incidencia">Incidencia</option>
                <option value="nota">Nota</option>
            @else
                <option value="nota">Nota</option>
            @endif
        </select>
        <label for="ejec-evento-descripcion" class="mt-2 block text-sm font-medium text-slate-700">Descripción</label>
        <textarea id="ejec-evento-descripcion" name="descripcion" rows="3" class="mt-1 block w-full rounded-2xl border border-[#d8e2d7] bg-[#f8faf6] px-3 py-2.5 text-[#0f3c2b] focus:border-[#7cb22b] focus:outline-none focus:ring-2 focus:ring-[#e4ebdf]"></textarea>
        <label for="ejec-evento-tarea" class="mt-3 block text-sm font-semibold text-[#0f3c2b]">Tarea relacionada (opcional)</label>
        <input id="ejec-evento-tarea" name="tarea" type="text" placeholder="Ej.: Revisar humedad del sustrato" class="mt-1 block w-full rounded-2xl border border-[#d8e2d7] bg-[#f8faf6] px-3 py-2.5 text-[#0f3c2b] focus:border-[#7cb22b] focus:outline-none focus:ring-2 focus:ring-[#e4ebdf]">
        <button type="submit" class="mt-3 rounded-full bg-[#7cb22b] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#6eab26] focus:outline-none focus:ring-2 focus:ring-[#cfe0c2]">Registrar evento</button>
    </form>

    <div id="ejec-checklist" class="mt-4 rounded-[10px] border border-[#e4ebdf] bg-white p-5">
        <h3 class="font-medium text-slate-800">Checklist por tarea</h3>
        @php($plan = $caso->planes()->latest('version')->latest('id')->first())
        @if ($plan)
            @foreach ($plan->tareas()->orderBy('id')->get() as $tarea)
                <div id="ejec-tarea-{{ $tarea->id }}" class="mt-3">
                    <p><strong>{{ $tarea->codigo }}</strong> {{ $tarea->titulo }} — {{ $tarea->estado }}</p>
                    <table class="mt-3 w-full border-collapse text-sm text-[#1d352d]">
                        <thead>
                            <tr class="bg-[#edf8e7] text-[#3b5d2d]">
                                <th scope="col" class="border border-[#dce6d7] px-3 py-2 text-center">Ítem</th>
                                <th scope="col" class="border border-[#dce6d7] px-3 py-2 text-center">Estado</th>
                                <th scope="col" class="border border-[#dce6d7] px-3 py-2 text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tarea->checklist as $item)
                                <tr class="even:bg-[#f8faf6]">
                                    <td class="border border-[#e4ebdf] px-3 py-2 text-center">
                                        <label for="ejec-check-{{ $item->id }}">{{ $item->descripcion }}</label>
                                    </td>
                                    <td class="border border-[#e4ebdf] px-3 py-2 text-center">
                                        <input id="ejec-check-{{ $item->id }}" name="cumplido" form="ejec-check-form-{{ $item->id }}" type="checkbox" value="1" @checked($item->cumplido)>
                                    </td>
                                    <td class="border border-[#e4ebdf] px-3 py-2 text-center">
                                        <form id="ejec-check-form-{{ $item->id }}" method="POST" action="{{ route('mantenimiento.casos.ejecucion.checklist', ['caso' => $caso, 'item' => $item]) }}">
                                            @csrf
                                            <button type="submit" class="rounded-full border border-[#dce6d7] bg-[#f1f7ee] px-3 py-1 text-xs font-semibold text-[#3b5b42] shadow-sm transition hover:bg-[#edf8e7]">Actualizar</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        @endif
    </div>

    @php($incidencias = \App\Models\EventoEjecucion::query()->where('caso_id', $caso->id)->where('tipo', 'incidencia')->whereNull('resuelta_en')->orderBy('fecha')->orderBy('id')->get())
    <div id="ejec-incidencias" class="mt-4">
        <h3 class="font-medium text-slate-800">Incidencias abiertas</h3>
        @if ($incidencias->isNotEmpty())
            <table class="mt-3 w-full border-collapse text-sm text-[#1d352d]">
                <thead>
                    <tr class="bg-[#edf8e7] text-[#3b5d2d]">
                        <th scope="col" class="border border-[#dce6d7] px-3 py-2 text-center">Incidencia</th>
                        <th scope="col" class="border border-[#dce6d7] px-3 py-2 text-center">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($incidencias as $incidencia)
                        <tr class="even:bg-[#f8faf6]">
                            <td id="ejec-incidencia-{{ $incidencia->id }}" class="border border-[#e4ebdf] px-3 py-2 text-center whitespace-pre-line">{{ $incidencia->descripcion }}</td>
                            <td class="border border-[#e4ebdf] px-3 py-2 text-center">
                                <form method="POST" action="{{ route('mantenimiento.casos.ejecucion.incidencia.resolver', ['caso' => $caso, 'evento' => $incidencia]) }}">
                                    @csrf
                                    <button type="submit" class="rounded-full border border-[#dce6d7] bg-[#f1f7ee] px-3 py-1 text-xs font-semibold text-[#3b5b42] shadow-sm transition hover:bg-[#edf8e7]">Resolver incidencia</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="mt-2 text-sm text-[#5f6d5f]">No hay incidencias abiertas.</p>
        @endif
    </div>
</section>
