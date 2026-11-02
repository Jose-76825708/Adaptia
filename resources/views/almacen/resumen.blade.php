<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    @vite('resources/css/app.css')
    <title>Resumen de almacén - Adaptia</title>
</head>

<body class="flex font-sans h-screen">
    @include('partials.sidebar')

    @php($esAdmin = auth()->user()->rol === 'administrador')

    <main class="flex flex-5 flex-col p-25 bg-[#fbfbfb] h-full gap-4">
        <section class="flex items-center justify-between">
            <div class="flex-4">
                <h1 class="text-[#103928] font-bold text-[2.5em]">Resumen de almacén</h1>
                <p class="text-[#8b8d8f] text-[1em]">Estado general del inventario de plantas.</p>
            </div>
            <div class="flex-1">
                <a href="{{ route('movimientos-inventario.create') }}"
                    class="flex items-center justify-between py-3 px-6 bg-[#629f22] text-[#fbfbfb] rounded-[10px] gap-3 hover:scale-110 transition duration-300">
                    <img class="w-[10%] h-auto" src="{{ asset('images/anadir.png') }}" alt="Añadir">
                    Registrar Movimiento
                </a>
            </div>
        </section>

        <section class="flex gap-4">
            <article id="tarjeta-stock-total"
                class="flex flex-1 flex-col gap-1 p-6 bg-[#fefdfe] text-[#304e42] rounded-[10px] shadow-xl">
                <span class="font-bold">Stock total</span>
                <span class="text-[#103928] font-bold text-[2.5em]">{{ $resumen['stock_total'] }}</span>
                <span class="text-[#8b8d8f]">unidades en el vivero</span>
            </article>

            <article id="tarjeta-bajo-minimo"
                class="flex flex-1 flex-col gap-1 p-6 bg-[#fefdfe] text-[#304e42] rounded-[10px] shadow-xl">
                <span class="font-bold">Plantas bajo el mínimo</span>
                <span class="font-bold text-[2.5em] {{ $resumen['plantas_bajo_minimo'] > 0 ? 'text-red-600' : 'text-[#103928]' }}">
                    {{ $resumen['plantas_bajo_minimo'] }}
                </span>
                <span class="text-[#8b8d8f]">especies por reponer</span>
            </article>

            <article id="tarjeta-alertas"
                class="flex flex-1 flex-col gap-1 p-6 bg-[#fefdfe] text-[#304e42] rounded-[10px] shadow-xl">
                <span class="font-bold">Alertas abiertas</span>
                <span class="text-[#103928] font-bold text-[2.5em]">{{ $resumen['alertas_abiertas'] }}</span>
                <span class="text-[#8b8d8f]">{{ $resumen['alertas_no_leidas'] }} sin leer</span>
                @if ($esAdmin)
                    <a href="{{ route('alertas-stock.index') }}" class="font-bold text-[#629f22]">Ver alertas</a>
                @endif
            </article>
        </section>

        <h2 class="text-[#103928] font-bold text-[1.5em]">Movimientos recientes</h2>

        <section class="flex p-8 bg-[#fefdfe] text-[#304e42] rounded-[10px] shadow-xl overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-[#f3f5f3]">
                    <tr>
                        <th class="rounded-tl-[20px] p-4">Planta</th>
                        <th class="p-4">Tipo</th>
                        <th class="p-4">Cantidad</th>
                        <th class="p-4">Usuario</th>
                        <th class="p-4">Fecha</th>
                        <th class="rounded-tr-[20px] p-4">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($resumen['movimientos_recientes'] as $movimiento)
                        <tr class="border-t-2 border-[#c2c4c7]">
                            <td class="p-4">{{ $movimiento->planta?->nombre ?? 'Planta no disponible' }}</td>
                            <td class="p-4">
                                <span class="px-2 py-1 rounded-full text-xs font-bold
                                    {{ $movimiento->tipo == 'entrada' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                                    {{ ucfirst($movimiento->tipo) }}
                                </span>
                            </td>
                            <td class="p-4">{{ $movimiento->cantidad }}</td>
                            <td class="p-4">{{ $movimiento->user?->name ?? 'Sin usuario' }}</td>
                            <td class="p-4">{{ $movimiento->created_at?->format('d/m/Y H:i') ?? 'Sin fecha' }}</td>
                            <td class="p-4">
                                @if ($movimiento->esAnulacion())
                                    <span class="px-2 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-700">
                                        Anulación del movimiento {{ $movimiento->movimiento_original_id }}
                                    </span>
                                @elseif ($movimiento->anulacion)
                                    <span class="px-2 py-1 rounded-full text-xs font-bold bg-gray-200 text-gray-600">Anulado</span>
                                @else
                                    <span class="px-2 py-1 rounded-full text-xs font-bold bg-green-100 text-green-600">Vigente</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-4 text-center text-[#8b8d8f]">
                                Aún no hay movimientos de inventario registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <a href="{{ route('movimientos-inventario.index') }}" class="font-bold text-[#629f22]">Ver todos los movimientos</a>
    </main>
</body>

</html>
