<section id="cierre-panel" class="rounded-[10px] border border-[#e4ebdf] bg-[#f8faf6] p-5 shadow-sm" aria-labelledby="cierre-titulo">
    <h2 id="cierre-titulo" class="text-xl font-bold text-[#103928]">Cierre / Reapertura</h2>

    @if ($errors->cierre->any())
        <div id="cierre-errores" class="mt-3 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700" role="alert">
            @foreach ($errors->cierre->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('mantenimiento.casos.cierre.cerrar', $caso) }}" class="mt-4 rounded-[10px] border border-[#e4ebdf] bg-white p-5">
        @csrf
        <button id="cierre-cerrar" type="submit" class="rounded-full bg-[#7cb22b] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#6eab26] focus:outline-none focus:ring-2 focus:ring-[#cfe0c2]">Cerrar caso</button>
    </form>

    <form method="POST" action="{{ route('mantenimiento.casos.cierre.reabrir', $caso) }}" class="mt-4 rounded-[10px] border border-[#e4ebdf] bg-white p-5">
        @csrf
        <label for="cierre-motivo" class="block text-sm font-medium text-slate-700">Motivo de reapertura</label>
        <textarea id="cierre-motivo" name="motivo" rows="3" class="mt-1 block w-full rounded-2xl border border-[#d8e2d7] bg-[#f8faf6] px-3 py-2.5 text-[#0f3c2b] focus:border-[#7cb22b] focus:outline-none focus:ring-2 focus:ring-[#e4ebdf]"></textarea>
        <button type="submit" class="mt-3 rounded-full border border-[#dce6d7] bg-[#f1f7ee] px-5 py-2.5 text-sm font-semibold text-[#3b5b42] shadow-sm transition hover:bg-[#edf8e7] focus:outline-none focus:ring-2 focus:ring-[#cfe0c2]">Reabrir caso</button>
    </form>

    @php($informe = $caso->informes()->latest('version')->latest('id')->first())
    @if ($informe)
        <div id="cierre-informe" class="mt-4 rounded-[10px] border border-[#e4ebdf] bg-white p-5">
            <table class="w-full border-collapse text-sm text-[#1d352d]">
                <thead>
                    <tr class="bg-[#edf8e7] text-[#3b5d2d]">
                        <th scope="col" class="border border-[#dce6d7] px-3 py-2 text-center">Versión</th>
                        <th scope="col" class="border border-[#dce6d7] px-3 py-2 text-center">Estado</th>
                        <th scope="col" class="border border-[#dce6d7] px-3 py-2 text-center">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="even:bg-[#f8faf6]">
                        <td class="border border-[#e4ebdf] px-3 py-2 text-center">{{ $informe->version }}</td>
                        <td class="border border-[#e4ebdf] px-3 py-2 text-center">Informe generado</td>
                        <td class="border border-[#e4ebdf] px-3 py-2 text-center">
                            <a href="{{ route('mantenimiento.casos.informe', $caso) }}" class="font-semibold text-[#3b5d2d] underline">Ver informe final</a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endif
</section>
