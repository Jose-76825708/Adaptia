<section id="plan-panel" class="rounded-[10px] border border-[#e4ebdf] bg-[#f8faf6] p-5 shadow-sm" aria-labelledby="plan-titulo">
    <h2 id="plan-titulo" class="text-xl font-bold text-[#103928]">Plan de mantenimiento</h2>

    @if ($errors->plan->any())
        <div id="plan-errores" class="mt-3 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700" role="alert">
            @foreach ($errors->plan->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('mantenimiento.casos.plan.generar', $caso) }}" class="mt-4">
        @csrf
        <label id="plan-generar" for="plan-generar" class="sr-only">Generar el plan de mantenimiento</label>
        <button id="plan-generar" type="submit" class="rounded-full bg-[#7cb22b] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#6eab26] focus:outline-none focus:ring-2 focus:ring-[#cfe0c2]">Guardar plan</button>
    </form>

    @php($plan = $caso->planes()->latest('version')->latest('id')->first())
    @if ($plan)
        <div id="plan-vigente" class="mt-4 rounded-[10px] border border-[#e4ebdf] bg-white p-5">
            <p><strong>Versión:</strong> {{ $plan->version }}</p>
            <table id="plan-tareas" class="mt-3 w-full border-collapse text-sm text-[#1d352d]">
                <thead>
                    <tr class="bg-[#edf8e7] text-[#3b5d2d]">
                        <th scope="col" class="border border-[#dce6d7] px-3 py-2 text-center">Código</th>
                        <th scope="col" class="border border-[#dce6d7] px-3 py-2 text-center">Tarea</th>
                        <th scope="col" class="border border-[#dce6d7] px-3 py-2 text-center">Prioridad</th>
                        <th scope="col" class="border border-[#dce6d7] px-3 py-2 text-center">Criticidad</th>
                        <th scope="col" class="border border-[#dce6d7] px-3 py-2 text-center">Duración</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($plan->tareas()->orderBy('id')->get() as $tarea)
                        <tr class="even:bg-[#f8faf6]">
                            <td class="border border-[#e4ebdf] px-3 py-2 text-center">{{ $tarea->codigo }}</td>
                            <td class="border border-[#e4ebdf] px-3 py-2 text-center">{{ $tarea->titulo }}</td>
                            <td class="border border-[#e4ebdf] px-3 py-2 text-center">{{ $tarea->prioridad }}</td>
                            <td class="border border-[#e4ebdf] px-3 py-2 text-center">{{ $tarea->criticidad }}</td>
                            <td class="border border-[#e4ebdf] px-3 py-2 text-center">{{ $tarea->duracion_dias }} día(s)</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
