<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    @vite('resources/css/app.css')
    <title>Gestión de Insumos</title>
</head>

<body class="flex font-sans h-screen">

    @include('partials.sidebar')

    <main class="flex flex-5 flex-col p-25 bg-[#fbfbfb] h-full gap-4 overflow-y-auto">
        <section class="flex items-center justify-between">
            <div class="flex-4">
                <h1 class="text-[#103928] font-bold text-[2.5em]">Gestión de Insumos</h1>
                <p class="text-[#8b8d8f] text-[1em]">Administra el catálogo de insumos (abono, tierra, macetas…) y su disponibilidad en Adaptia.</p>
            </div>
            <div class="flex-1">
                <a href="{{ route('insumos.create') }}"
                    class="flex items-center justify-between py-3 px-6 bg-[#629f22] text-[#fbfbfb] rounded-[10px] gap-3 hover:scale-110 transition duration-300">
                    <img class="w-[10%] h-auto" src="{{ asset('images/anadir.png') }}" alt="Añadir">
                    Crear Insumo
                </a>
            </div>
        </section>

        @if (session('success'))
            <div class="rounded-[10px] border border-[#cfe0c2] bg-[#edf8e7] p-4 text-sm font-semibold text-[#3b5d2d]" role="status">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="rounded-[10px] border border-red-200 bg-red-50 p-4 text-sm text-red-700" role="alert">
                @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
            </div>
        @endif

        @if ($insumosStockBajo->isNotEmpty())
            <section class="flex p-6 bg-[#fff8f8] text-[#991b1b] rounded-[10px] shadow-lg mb-6">
                <div class="flex-4">
                    <h2 class="text-[1.8em] font-bold flex items-center">
                        ⚠️ Insumos con Stock Bajo
                    </h2>
                    <p class="text-[0.9em] mt-1">
                        {{ $insumosStockBajo->count() }} insumo{{ $insumosStockBajo->count() > 1 ? 's' : '' }} requieren atención inmediata
                    </p>
                </div>
            </section>

            <section class="bg-[#fff8f8] border border-[#fee2e2] rounded-[10px] p-6 mb-6">
                <h3 class="text-[1.2em] font-bold text-[#991b1b] mb-4">Detalle de Insumos con Stock Bajo</h3>
                <div class="space-y-4">
                    @foreach ($insumosStockBajo as $insumo)
                        <div class="flex p-4 bg-[#fefefe] rounded-[8px] border border-[#fecaca]">
                            <div class="flex-3">
                                <div class="font-medium">{{ $insumo->nombre }}</div>
                                <div class="text-[0.9em] text-[#64748b] mt-1">Código: {{ $insumo->codigo }}</div>
                            </div>
                            <div class="flex-2 flex items-center justify-between">
                                <div class="text-center">
                                    <div class="text-[0.9em] font-medium">Stock Actual</div>
                                    <div class="text-[1.3em] font-bold bg-red-100 text-red-600 px-3 py-1 rounded">
                                        {{ rtrim(rtrim(number_format((float) $insumo->stock_actual, 2, '.', ''), '0'), '.') }}
                                    </div>
                                    <div class="text-[0.8em] text-red-500 mt-1">{{ $insumo->unidad }}</div>
                                </div>
                                <div class="w-1"></div>
                                <div class="text-center">
                                    <div class="text-[0.9em] font-medium">Stock Mínimo</div>
                                    <div class="text-[1.3em] font-bold bg-blue-100 text-blue-600 px-3 py-1 rounded">
                                        {{ rtrim(rtrim(number_format((float) $insumo->stock_minimo, 2, '.', ''), '0'), '.') }}
                                    </div>
                                    <div class="text-[0.8em] text-blue-500 mt-1">{{ $insumo->unidad }}</div>
                                </div>
                            </div>
                            <div class="flex-1 flex items-center justify-end">
                                <a href="{{ route('gestion-inventario.create', ['tipo_item' => 'insumo', 'recurso_id' => $insumo->id]) }}"
                                   class="bg-[#629f22] text-[#fbfbfb] px-4 py-2 rounded-[8px] text-[0.9em] hover:bg-[#50801b] transition">
                                    Revisar existencias
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="flex p-8 bg-[#fefdfe] text-[#304e42] rounded-[10px] shadow-xl overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-[#f3f5f3]">
                    <tr>
                        <th class="text-center rounded-tl-[20px] p-4 w-20">#</th>
                        <th class="p-4">Código</th>
                        <th class="p-4">Nombre</th>
                        <th class="p-4">Unidad</th>
                        <th class="p-4">Stock</th>
                        <th class="p-4">Stock mínimo</th>
                        <th class="rounded-tr-[20px] p-4 w-64 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($posts as $insumo)
                        <tr class="border-t-2 border-[#c2c4c7]">
                            <td class="p-4 font-bold text-center">{{ $loop->iteration }}</td>
                            <td class="p-4">{{ $insumo->codigo }}</td>
                            <td class="p-4 font-medium">{{ $insumo->nombre }}</td>
                            <td class="p-4">{{ $insumo->unidad }}</td>
                            <td class="p-4">
                                <span class="px-3 py-1 rounded-full text-xs font-bold {{ $insumo->stock_minimo !== null && (float) $insumo->stock_actual <= (float) $insumo->stock_minimo ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-600' }}">
                                    {{ rtrim(rtrim(number_format((float) $insumo->stock_actual, 2, '.', ''), '0'), '.') }} {{ $insumo->unidad }}
                                </span>
                            </td>
                            <td class="p-4">{{ $insumo->stock_minimo !== null ? rtrim(rtrim(number_format((float) $insumo->stock_minimo, 2, '.', ''), '0'), '.') : '—' }}</td>
                            <td class="flex gap-4 p-4 justify-center">
                                <a class="flex flex-1 items-center justify-center gap-2 bg-[#f5f9f0] py-2 text-[#77a856] font-bold rounded-[10px] hover:scale-110 transition duration-300"
                                    href="{{ route('insumos.edit', $insumo->id) }}">
                                    <img class="w-4 h-auto" src="{{ asset('images/editar.png') }}" alt=""> Editar
                                </a>
                                <form action="{{ route('insumos.destroy', $insumo->id) }}" method="POST" class="flex flex-1"
                                      onsubmit="return confirm('¿Eliminar este insumo?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="flex flex-1 items-center justify-center gap-2 bg-[#fdebeb] py-2 text-[#f06f73] font-bold rounded-[10px] hover:scale-110 transition duration-300">
                                        <img class="w-4 h-auto" src="{{ asset('images/eliminar.png') }}" alt=""> Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr class="border-t-2 border-[#c2c4c7]">
                            <td colspan="7" class="p-8 text-center text-[#8b8d8f]">Todavía no hay insumos. Crea el primero con «Crear Insumo».</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </main>
</body>

</html>
