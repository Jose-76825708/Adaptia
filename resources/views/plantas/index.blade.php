<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    @vite('resources/css/app.css')
    <title>Gestión de Plantas</title>
</head>

<body class="flex font-sans h-screen">

    @include('partials.sidebar')

    <main class="flex flex-5 flex-col p-25 bg-[#fbfbfb] h-full gap-4">
        <section class="flex items-center justify-between">
            <div class="flex-4">
                <h1 class="text-[#103928] font-bold text-[2.5em]">Gestión de Plantas</h1>
                <p class="text-[#8b8d8f] text-[1em]">Administra el catálogo completo de plantas y su disponibilidad en Adaptia.</p>
            </div>
            <div class="flex-1">
                <a href="{{ route('plantas.create') }}"
                    class="flex items-center justify-between py-3 px-6 bg-[#629f22] text-[#fbfbfb] rounded-[10px] gap-3 hover:scale-110 transition duration-300">
                    <img class="w-[10%] h-auto" src="{{ asset('images/anadir.png') }}" alt="Añadir">
                    Crear Planta
                </a>
            </div>
        </section>

        @if ($plantasStockBajo->isNotEmpty())
            <section class="flex p-6 bg-[#fff8f8] text-[#991b1b] rounded-[10px] shadow-lg mb-6">
                <div class="flex-4">
                    <h2 class="text-[1.8em] font-bold flex items-center">
                        ⚠️ Plantas con Stock Bajo
                    </h2>
                    <p class="text-[0.9em] mt-1">
                        {{ $plantasStockBajo->count() }} planta{{ $plantasStockBajo->count() > 1 ? 's' : '' }} requieren atención inmediata
                    </p>
                </div>
                <div class="flex-1 flex items-end justify-end">
                    <a href="{{ route('movimientos-inventario.create') }}"
                       class="bg-[#dc2626] text-[#fbfbfb] px-5 py-2 rounded-[8px] text-[0.9em] hover:bg-[#b91c1c] transition">
                        Registrar Entrada de Stock
                    </a>
                </div>
            </section>

            <section class="bg-[#fff8f8] border border-[#fee2e2] rounded-[10px] p-6 mb-6">
                <h3 class="text-[1.2em] font-bold text-[#991b1b] mb-4">Detalle de Plantas con Stock Bajo</h3>
                <div class="space-y-4">
                    @foreach ($plantasStockBajo as $planta)
                        <div class="flex p-4 bg-[#fefefe] rounded-[8px] border border-[#fecaca]">
                            <div class="flex-3">
                                <div class="font-medium">{{ $planta->nombre }}</div>
                                <div class="text-[0.9em] text-[#64748b] mt-1">
                                    Tipo: {{ $planta->tipoPlanta->nombre }}
                                </div>
                            </div>
                            <div class="flex-2 flex items-center justify-between">
                                <div class="text-center">
                                    <div class="text-[0.9em] font-medium">Stock Actual</div>
                                    <div class="text-[1.3em] font-bold bg-red-100 text-red-600 px-3 py-1 rounded">
                                        {{ $planta->stock_actual }}
                                    </div>
                                    <div class="text-[0.8em] text-red-500 mt-1">und.</div>
                                </div>
                                <div class="w-1"></div>
                                <div class="text-center">
                                    <div class="text-[0.9em] font-medium">Stock Mínimo</div>
                                    <div class="text-[1.3em] font-bold bg-blue-100 text-blue-600 px-3 py-1 rounded">
                                        {{ $planta->stock_minimo }}
                                    </div>
                                    <div class="text-[0.8em] text-blue-500 mt-1">und.</div>
                                </div>
                            </div>
                            <div class="flex-1 flex items-center justify-end">
                                <span class="bg-red-100 text-red-600 px-3 py-1 rounded-[6px] text-[0.9em]">
                                    {{ $planta->stock_minimo - $planta->stock_actual }} und. faltantes
                                </span>
                            </div>
                        </div>
                        @if (!$loop->last)
                            <div class="h-1"></div>
                        @endif
                    @endforeach
                </div>
                @if ($plantasStockBajo->count() > 0)
                    <div class="mt-4 text-center">
                        <a href="{{ route('movimientos-inventario.create') }}"
                           class="bg-[#629f22] text-[#fbfbfb] px-6 py-2 rounded-[8px] text-[0.9em] hover:bg-[#50801b] transition">
                            Ir a Registrar Movimiento de Inventario
                        </a>
                    </div>
                @endif
            </section>
        @endif

        <section class="flex p-8 bg-[#fefdfe] text-[#304e42] rounded-[10px] shadow-xl overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-[#f3f5f3]">
                    <tr>
                        <th class="text-center rounded-tl-[20px] p-4 w-20">#</th>
                        <th class="p-4">Nombre</th>
                        <th class="p-4">Tipo</th>
                        <th class="p-4">Precio</th>
                        <th class="p-4">Stock</th>
                        <th class="p-4">Cuidado</th>
                        <th class="rounded-tr-[20px] p-4 w-64 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($posts as $planta)
                        <tr class="border-t-2 border-[#c2c4c7]">
                            <td class="p-4 font-bold text-center">{{ $loop->iteration }}</td>
                            <td class="p-4 font-medium">{{ $planta->nombre }}</td>
                            <td class="p-4">{{ $planta->tipoPlanta->nombre }}</td>
                            <td class="p-4">S/. {{ number_format($planta->precio, 2) }}</td>
                            <td class="p-4">
                                <span class="px-3 py-1 rounded-full text-xs font-bold {{ $planta->stock_actual <= $planta->stock_minimo ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-600' }}">
                                    {{ $planta->stock_actual }} und.
                                </span>
                            </td>
                            <td class="p-4">{{ ucfirst($planta->nivel_cuidado) }}</td>
                            <td class="flex gap-4 p-4 justify-center">
                                <a class="flex flex-1 items-center justify-center gap-2 bg-[#f5f9f0] py-2 text-[#77a856] font-bold rounded-[10px] hover:scale-110 transition duration-300"
                                    href="{{ route('plantas.edit', $planta->id) }}">
                                    <img class="w-4 h-auto" src="{{ asset('images/editar.png') }}" alt=""> Editar
                                </a>
                                <form action="{{ route('plantas.destroy', $planta->id) }}" method="POST" class="flex flex-1">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="flex flex-1 items-center justify-center gap-2 bg-[#fdebeb] py-2 text-[#f06f73] font-bold rounded-[10px] hover:scale-110 transition duration-300">
                                        <img class="w-4 h-auto" src="{{ asset('images/eliminar.png') }}" alt=""> Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    </main>
</body>

</html>
