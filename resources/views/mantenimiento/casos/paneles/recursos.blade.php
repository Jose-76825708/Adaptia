<section id="rec-panel" class="rounded-[10px] border border-[#e4ebdf] bg-[#f8faf6] p-5 shadow-sm" aria-labelledby="rec-titulo">
    <h2 id="rec-titulo" class="text-xl font-bold text-[#103928]">Disponibilidad de recursos</h2>

    @php($plan = $caso->planes()->latest('version')->latest('id')->first())
    @php($verificador = new \App\Services\VerificadorDisponibilidadService())
    @php($veredicto = $plan ? $verificador->verificarPlan($plan->id) : ['estado' => 'faltan', 'faltan_materiales' => [], 'faltan_herramientas' => [], 'motivo' => 'No existe un plan vigente.'])

    <p id="rec-veredicto" class="mt-3 font-medium">Veredicto: {{ $veredicto['estado'] }}</p>
    @if ($veredicto['motivo'])
        <p id="rec-motivo" class="mt-1 text-sm text-slate-600">{{ $veredicto['motivo'] }}</p>
    @endif

    <div id="rec-listado" class="mt-4 rounded-[10px] border border-[#e4ebdf] bg-white p-5">
        @if (!$plan)
            <p id="rec-sin-plan" class="text-sm text-slate-600">Genere el plan antes de verificar recursos.</p>
        @else
            <table id="rec-tabla" class="w-full border-collapse text-sm text-[#1d352d]">
                <thead>
                    <tr class="bg-[#edf8e7] text-[#3b5d2d]">
                        <th scope="col" class="border border-[#dce6d7] px-3 py-2 text-center">Código</th>
                        <th scope="col" class="border border-[#dce6d7] px-3 py-2 text-center">Recurso</th>
                        <th scope="col" class="border border-[#dce6d7] px-3 py-2 text-center">Tipo</th>
                        <th scope="col" class="border border-[#dce6d7] px-3 py-2 text-center">Requerido</th>
                        <th scope="col" class="border border-[#dce6d7] px-3 py-2 text-center">Disponible</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($plan->tareas as $tarea)
                        @foreach ($tarea->recursos as $asignacion)
                            <tr class="even:bg-[#f8faf6]">
                                <td class="border border-[#e4ebdf] px-3 py-2 text-center">{{ $asignacion->recurso->codigo }}</td>
                                <td class="border border-[#e4ebdf] px-3 py-2 text-center">{{ $asignacion->recurso->nombre }}</td>
                                <td class="border border-[#e4ebdf] px-3 py-2 text-center">{{ $asignacion->recurso->tipo }}</td>
                                <td class="border border-[#e4ebdf] px-3 py-2 text-center">{{ number_format((float) $asignacion->cantidad, 2) }}</td>
                                <td class="border border-[#e4ebdf] px-3 py-2 text-center">{{ number_format((float) $asignacion->recurso->stock_actual, 2) }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</section>
