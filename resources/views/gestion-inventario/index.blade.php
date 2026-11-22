<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    @vite('resources/css/app.css')
    <title>Gestión de inventario</title>
</head>
<body class="flex h-screen font-sans">
    @include('partials.sidebar')

    <main class="flex flex-5 flex-col gap-5 overflow-y-auto bg-[#fbfbfb] p-8">
        <section class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-[2.5em] font-bold text-[#103928]">Gestión de inventario</h1>
                <p class="text-[#68766b]">Revisión de existencias, reposición, recepción, almacenamiento y movimientos de plantas e insumos.</p>
            </div>
            <a href="{{ route('gestion-inventario.create') }}" class="rounded-full bg-[#7cb22b] px-5 py-2.5 text-sm font-semibold text-white">Nueva revisión de existencias</a>
        </section>

        @if (session('success'))
            <div class="rounded-[10px] border border-[#cfe0c2] bg-[#edf8e7] p-4 text-sm font-semibold text-[#3b5d2d]" role="status">{{ session('success') }}</div>
        @endif

        <form method="GET" action="{{ route('gestion-inventario.index') }}" class="rounded-[10px] border border-[#e4ebdf] bg-white p-5 shadow-sm">
            <div class="grid gap-4 md:grid-cols-[1fr_1fr_2fr_auto]">
                <div>
                    <label for="filtro-estado" class="mb-2 block text-sm font-semibold text-[#0f3c2b]">Estado</label>
                    <select id="filtro-estado" name="estado" class="w-full rounded-2xl border border-[#d8e2d7] bg-[#f8faf6] px-3 py-2.5 text-[#0f3c2b]">
                        <option value="">Todos</option>
                        @foreach ($estados as $estado)
                            <option value="{{ $estado }}" @selected(($filtros['estado'] ?? '') === $estado)>{{ ucfirst(str_replace('_', ' ', $estado)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="filtro-tipo" class="mb-2 block text-sm font-semibold text-[#0f3c2b]">Tipo</label>
                    <select id="filtro-tipo" name="tipo_item" class="w-full rounded-2xl border border-[#d8e2d7] bg-[#f8faf6] px-3 py-2.5 text-[#0f3c2b]">
                        <option value="">Plantas e insumos</option>
                        <option value="planta" @selected(($filtros['tipo_item'] ?? '') === 'planta')>Plantas</option>
                        <option value="insumo" @selected(($filtros['tipo_item'] ?? '') === 'insumo')>Insumos</option>
                    </select>
                </div>
                <div>
                    <label for="filtro-q" class="mb-2 block text-sm font-semibold text-[#0f3c2b]">Búsqueda</label>
                    <input id="filtro-q" type="search" name="q" value="{{ $filtros['q'] ?? '' }}" maxlength="100" placeholder="Código, motivo o nombre del producto" class="w-full rounded-2xl border border-[#d8e2d7] bg-[#f8faf6] px-3 py-2.5 text-[#0f3c2b]">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="rounded-full bg-[#7cb22b] px-5 py-2.5 text-sm font-semibold text-white">Filtrar</button>
                    <a href="{{ route('gestion-inventario.index') }}" class="rounded-full border border-[#dce6d7] px-5 py-2.5 text-sm font-medium text-[#3b5b42]">Limpiar</a>
                </div>
            </div>
        </form>

        @if ($gestiones->isEmpty())
            <div class="rounded-[10px] border border-dashed border-[#cfe0c2] bg-[#f8faf6] px-6 py-12 text-center">
                <h2 class="text-xl font-bold text-[#0f3c2b]">No hay gestiones de inventario para mostrar</h2>
                <p class="mt-2 text-sm text-[#5f6d5f]">Cambia los filtros o registra una nueva revisión de existencias.</p>
            </div>
        @else
            <section class="overflow-x-auto rounded-[10px] bg-white p-6 shadow-sm">
                <table class="w-full min-w-[850px] text-left text-sm text-[#304e42]">
                    <thead class="bg-[#edf8e7] text-[#3b5d2d]">
                        <tr>
                            <th class="px-4 py-3">Código</th>
                            <th class="px-4 py-3">Producto</th>
                            <th class="px-4 py-3">Tipo</th>
                            <th class="px-4 py-3">Requerido</th>
                            <th class="px-4 py-3">Estado</th>
                            <th class="px-4 py-3">Responsable</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($gestiones as $gestion)
                            <tr class="border-t border-[#e4ebdf]">
                                <td class="px-4 py-3 font-semibold"><a class="underline underline-offset-2" href="{{ route('gestion-inventario.show', $gestion) }}">{{ $gestion->codigo }}</a></td>
                                <td class="px-4 py-3">{{ $gestion->nombreItem() }}</td>
                                <td class="px-4 py-3">{{ $gestion->tipo_item === 'planta' ? 'Planta' : 'Insumo' }}</td>
                                <td class="px-4 py-3">{{ rtrim(rtrim(number_format((float) $gestion->cantidad_requerida, 2, '.', ''), '0'), '.') }} {{ $gestion->unidad() }}</td>
                                <td class="px-4 py-3">{{ ucfirst(str_replace('_', ' ', $gestion->estado)) }}</td>
                                <td class="px-4 py-3">{{ $gestion->creador?->name ?? 'Sin responsable' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="mt-4">{{ $gestiones->links() }}</div>
            </section>
        @endif
    </main>
</body>
</html>
