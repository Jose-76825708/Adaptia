<section id="abas-panel" class="rounded-[10px] border border-[#e4ebdf] bg-[#f8faf6] p-5 shadow-sm" aria-labelledby="abas-titulo">
    <h2 id="abas-titulo" class="text-xl font-bold text-[#103928]">Abastecimiento</h2>

    @if ($errors->abastecimiento->any())
        <div id="abas-errores" class="mt-3 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700" role="alert">
            @foreach ($errors->abastecimiento->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div id="abas-requisicion" class="mt-4 rounded-[10px] border border-[#e4ebdf] bg-white p-5">
        <h3 class="font-semibold text-[#0f3c2b]">Recepción de materiales</h3>
        <p class="mt-1 text-sm text-[#5f6d5f]">Registra primero el ítem generado por el sistema y la cantidad recibida.</p>
    </div>

    <div id="abas-recomendaciones" class="mt-4 rounded-[10px] border border-[#e4ebdf] bg-white p-5">
        <h3 class="font-medium text-slate-800">Recomendaciones de compra</h3>
        <p class="mt-1 text-sm text-slate-600">Las recomendaciones se calculan desde los faltantes prioritarios del plan vigente.</p>
    </div>

    <form id="abas-recibir" method="POST" action="{{ route('mantenimiento.casos.requisicion.recibir', $caso) }}" class="mt-4 rounded-[10px] border border-[#e4ebdf] bg-white p-5">
        @csrf
        @php
            $itemsPendientes = \App\Models\RequisicionItem::query()
                ->with(['requisicion', 'recurso'])
                ->whereHas('requisicion', fn ($query) => $query->where('caso_id', $caso->id)->whereIn('estado', ['pendiente', 'parcial']))
                ->where('estado', '!=', 'recibida')
                ->orderBy('id')
                ->get();
        @endphp

        <label for="abas-item-id" class="block text-sm font-semibold text-[#0f3c2b]">Ítem de requisición</label>
        @if ($itemsPendientes->isNotEmpty())
            <table class="mt-3 w-full border-collapse text-sm text-[#1d352d]">
                <thead>
                    <tr class="bg-[#edf8e7] text-[#3b5d2d]">
                        <th scope="col" class="border border-[#dce6d7] px-3 py-2 text-center">Material</th>
                        <th scope="col" class="border border-[#dce6d7] px-3 py-2 text-center">Solicitada</th>
                        <th scope="col" class="border border-[#dce6d7] px-3 py-2 text-center">Recibida</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($itemsPendientes as $item)
                        <tr class="even:bg-[#f8faf6]">
                            <td class="border border-[#e4ebdf] px-3 py-2 text-center">{{ $item->recurso->nombre }}</td>
                            <td class="border border-[#e4ebdf] px-3 py-2 text-center">{{ number_format((float) $item->cantidad_solicitada, 2) }}</td>
                            <td class="border border-[#e4ebdf] px-3 py-2 text-center">{{ number_format((float) $item->cantidad_recibida, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <select id="abas-item-id" name="item_id" class="mt-3 block w-full rounded-2xl border border-[#d8e2d7] bg-[#f8faf6] px-3 py-2.5 text-[#0f3c2b] focus:border-[#7cb22b] focus:outline-none focus:ring-2 focus:ring-[#e4ebdf]">
                @foreach ($itemsPendientes as $item)
                    <option value="{{ $item->id }}">{{ $item->recurso->nombre }} — solicitada {{ number_format((float) $item->cantidad_solicitada, 2) }} / recibida {{ number_format((float) $item->cantidad_recibida, 2) }}</option>
                @endforeach
            </select>
        @else
            <p class="mt-2 rounded-[10px] border border-[#e4ebdf] bg-[#f8faf6] px-3 py-2.5 text-sm text-[#5f6d5f]">No hay ítems pendientes de recepción.</p>
        @endif
        <label for="abas-cantidad" class="mt-3 block text-sm font-medium text-slate-700">Cantidad recibida</label>
        <input id="abas-cantidad" name="cantidad" type="number" min="0.01" step="0.01" class="mt-1 block w-full rounded-2xl border border-[#d8e2d7] bg-[#f8faf6] px-3 py-2.5 text-[#0f3c2b] focus:border-[#7cb22b] focus:outline-none focus:ring-2 focus:ring-[#e4ebdf]">
        <button type="submit" @disabled($itemsPendientes->isEmpty()) class="mt-3 rounded-full bg-[#7cb22b] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#6eab26] focus:outline-none focus:ring-2 focus:ring-[#cfe0c2] disabled:cursor-not-allowed disabled:opacity-50">Registrar recepción</button>
    </form>

    <form method="POST" action="{{ route('mantenimiento.casos.abastecimiento.preparar', $caso) }}" class="mt-4">
        @csrf
        <button type="submit" class="rounded-full bg-[#7cb22b] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#6eab26] focus:outline-none focus:ring-2 focus:ring-[#cfe0c2]">Marcar preparado</button>
    </form>
</section>
