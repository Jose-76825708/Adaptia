<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    @vite('resources/css/app.css')
    <title>Movimientos de Inventario - Adaptia</title>
</head>
<body class="flex font-sans h-screen">
    @include('partials.sidebar')

    <main class="flex flex-5 flex-col p-25 bg-[#fbfbfb] h-full gap-4">
        <section class="flex items-center justify-between">
            <div class="flex-4">
                <h1 class="text-[#103928] font-bold text-[2.5em]">Movimientos de Inventario</h1>
                <p class="text-[#8b8d8f] text-[1em]">Registra las entradas y salidas de stock de tus plantas.</p>
            </div>
            <div class="flex-1">
                <a href="{{ route('movimientos-inventario.create') }}"
                    class="flex items-center justify-between py-3 px-6 bg-[#629f22] text-[#fbfbfb] rounded-[10px] gap-3 hover:scale-110 transition duration-300">
                    <img class="w-[10%] h-auto" src="{{ asset('images/anadir.png') }}" alt="Añadir">
                    Registrar Movimiento
                </a>
            </div>
        </section>

        @if(session('success'))
            <div class="p-4 bg-green-100 text-green-700 rounded-[10px] border border-green-200 text-center font-medium">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 bg-red-100 text-red-700 rounded-[10px] border border-red-200 text-center font-medium">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->has('motivo'))
            <div class="p-4 bg-red-100 text-red-700 rounded-[10px] border border-red-200 text-center font-medium">
                {{ $errors->first('motivo') }}
            </div>
        @endif

        @php($esAdmin = auth()->user()->rol === 'administrador')
        @php($hayFiltros = collect($filtros)->filter()->isNotEmpty())

        @if($errors->hasAny(['planta_id', 'tipo', 'desde', 'hasta', 'page']))
            <div class="p-4 bg-red-100 text-red-700 rounded-[10px] border border-red-200 font-medium">
                @foreach($errors->only(['planta_id', 'tipo', 'desde', 'hasta', 'page']) as $mensajes)
                    @foreach((array) $mensajes as $mensaje)
                        <p>{{ $mensaje }}</p>
                    @endforeach
                @endforeach
            </div>
        @endif

        <form method="GET" action="{{ route('movimientos-inventario.index') }}"
            class="flex flex-wrap items-end gap-4 p-6 bg-[#fefdfe] text-[#304e42] rounded-[10px] shadow-xl">
            <div class="flex flex-col gap-1">
                <label for="filtro-planta" class="font-bold">Planta</label>
                <select id="filtro-planta" name="planta_id"
                    class="p-2 bg-[#f3f5f3] border-2 border-[#ecedea] rounded-[8px] outline-none focus:border-[#629f22]">
                    <option value="">Todas</option>
                    @foreach($plantas as $planta)
                        <option value="{{ $planta->id }}" @selected((string) ($filtros['planta_id'] ?? '') === (string) $planta->id)>
                            {{ $planta->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex flex-col gap-1">
                <label for="filtro-tipo" class="font-bold">Tipo</label>
                <select id="filtro-tipo" name="tipo"
                    class="p-2 bg-[#f3f5f3] border-2 border-[#ecedea] rounded-[8px] outline-none focus:border-[#629f22]">
                    <option value="">Todos</option>
                    <option value="entrada" @selected(($filtros['tipo'] ?? '') === 'entrada')>Entrada</option>
                    <option value="salida" @selected(($filtros['tipo'] ?? '') === 'salida')>Salida</option>
                </select>
            </div>
            <div class="flex flex-col gap-1">
                <label for="filtro-desde" class="font-bold">Desde</label>
                <input id="filtro-desde" type="date" name="desde" value="{{ $filtros['desde'] ?? '' }}"
                    class="p-2 bg-[#f3f5f3] border-2 border-[#ecedea] rounded-[8px] outline-none focus:border-[#629f22]">
            </div>
            <div class="flex flex-col gap-1">
                <label for="filtro-hasta" class="font-bold">Hasta</label>
                <input id="filtro-hasta" type="date" name="hasta" value="{{ $filtros['hasta'] ?? '' }}"
                    class="p-2 bg-[#f3f5f3] border-2 border-[#ecedea] rounded-[8px] outline-none focus:border-[#629f22]">
            </div>
            <div class="flex items-center gap-3">
                <button type="submit"
                    class="py-2 px-6 bg-[#629f22] text-[#fbfbfb] font-bold rounded-[10px] hover:scale-105 transition duration-200">
                    Filtrar
                </button>
                <a href="{{ route('movimientos-inventario.index') }}"
                    class="py-2 px-6 bg-[#f3f5f3] text-[#304e42] font-bold rounded-[10px] hover:scale-105 transition duration-200">
                    Limpiar
                </a>
            </div>
        </form>

        <section class="flex p-8 bg-[#fefdfe] text-[#304e42] rounded-[10px] shadow-xl overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-[#f3f5f3]">
                    <tr>
                        <th class="text-center rounded-tl-[20px] p-4 w-10">#</th>
                        <th class="p-4">Planta</th>
                        <th class="p-4">Tipo</th>
                        <th class="p-4">Cantidad</th>
                        <th class="p-4">Stock</th>
                        <th class="p-4">Motivo</th>
                        <th class="p-4">Usuario</th>
                        <th class="p-4">Fecha</th>
                        <th class="p-4">Estado</th>
                        @if($esAdmin)
                            <th class="rounded-tr-[20px] p-4 w-64 text-center">Acciones</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($movimientos as $movimiento)
                        <tr class="border-t-2 border-[#c2c4c7]">
                            <td class="p-4 font-bold text-center">{{ $movimiento->id }}</td>
                            <td class="p-4">{{ $movimiento->planta->nombre }}</td>
                            <td class="p-4">
                                <span class="px-2 py-1 rounded-full text-xs font-bold
                                    {{ $movimiento->tipo == 'entrada' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                                    {{ ucfirst($movimiento->tipo) }}
                                </span>
                            </td>
                            <td class="p-4">{{ $movimiento->cantidad }}</td>
                            <td class="p-4">
                                @if($movimiento->stock_antes !== null && $movimiento->stock_despues !== null)
                                    {{ $movimiento->stock_antes }} → {{ $movimiento->stock_despues }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="p-4">{{ $movimiento->motivo ?? '—' }}</td>
                            <td class="p-4">{{ $movimiento->user->name }}</td>
                            <td class="p-4">{{ $movimiento->created_at->format('d/m/Y H:i') }}</td>
                            <td class="p-4">
                                @if($movimiento->esAnulacion())
                                    <span class="px-2 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-700">
                                        Anulación del movimiento {{ $movimiento->movimiento_original_id }}
                                    </span>
                                @elseif($movimiento->anulacion)
                                    <span class="px-2 py-1 rounded-full text-xs font-bold bg-gray-200 text-gray-600">Anulado</span>
                                @else
                                    <span class="px-2 py-1 rounded-full text-xs font-bold bg-green-100 text-green-600">Vigente</span>
                                @endif
                            </td>
                            @if($esAdmin)
                                <td class="p-4">
                                    @if(! $movimiento->esAnulacion() && ! $movimiento->anulacion)
                                        <form action="{{ route('movimientos-inventario.anular', $movimiento->id) }}" method="POST" class="flex flex-col gap-2">
                                            @csrf
                                           <label for="motivo-anulacion-{{ $movimiento->id }}" class="sr-only">Motivo de anulación</label>
                                           <input id="motivo-anulacion-{{ $movimiento->id }}" type="text" name="motivo" required maxlength="255" placeholder="Motivo de anulación"
                                               class="p-2 bg-[#f3f5f3] border-2 border-[#ecedea] rounded-[8px] outline-none focus:border-[#629f22]">
                                           <button type="submit"
                                               class="flex items-center justify-center bg-[#fdebeb] py-1 text-[#f06f73] font-bold rounded-[8px] hover:scale-105 transition duration-200"
                                               onclick="return confirm('¿Anular este movimiento? Se creará un movimiento inverso.');">
                                               Anular
                                           </button>
                                       </form>
                                    @else
                                        <span class="block text-center text-[#8b8d8f]">—</span>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $esAdmin ? 10 : 9 }}" class="p-4 text-center text-[#8b8d8f]">
                                @if($hayFiltros)
                                    No hay movimientos que coincidan con los filtros aplicados.
                                @else
                                    No hay movimientos de inventario registrados.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        @if($movimientos->total() > 0)
            <nav class="flex items-center justify-between text-[#304e42]" aria-label="Paginación de movimientos">
                <span>Mostrando {{ $movimientos->firstItem() }}–{{ $movimientos->lastItem() }} de {{ $movimientos->total() }} movimientos (página {{ $movimientos->currentPage() }} de {{ $movimientos->lastPage() }})</span>
                <div class="flex items-center gap-3">
                    @if($movimientos->onFirstPage())
                        <span class="py-2 px-6 bg-[#f3f5f3] text-[#c2c4c7] font-bold rounded-[10px]">Anterior</span>
                    @else
                        <a href="{{ $movimientos->previousPageUrl() }}" rel="prev"
                            class="py-2 px-6 bg-[#629f22] text-[#fbfbfb] font-bold rounded-[10px] hover:scale-105 transition duration-200">Anterior</a>
                    @endif

                    @if($movimientos->hasMorePages())
                        <a href="{{ $movimientos->nextPageUrl() }}" rel="next"
                            class="py-2 px-6 bg-[#629f22] text-[#fbfbfb] font-bold rounded-[10px] hover:scale-105 transition duration-200">Siguiente</a>
                    @else
                        <span class="py-2 px-6 bg-[#f3f5f3] text-[#c2c4c7] font-bold rounded-[10px]">Siguiente</span>
                    @endif
                </div>
            </nav>
        @endif
    </main>
</body>
</html>
