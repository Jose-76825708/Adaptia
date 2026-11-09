<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    @vite('resources/css/app.css')
    <title>Bandeja de mantenimiento</title>
</head>

<body class="flex font-sans h-screen">

    @include('partials.sidebar')

    <main class="flex flex-5 flex-col p-8 bg-[#fbfbfb] h-full gap-4 overflow-y-auto">
        <section class="flex items-center justify-between gap-4">
            <div class="flex-4">
                <h1 class="text-[#103928] font-bold text-[2.5em]">Casos de mantenimiento</h1>
                <p class="text-[#8b8d8f] text-[1em]">Revisa y filtra los casos de mantenimiento abiertos por los clientes.</p>
            </div>
        </section>

        @php
            $hayFiltros = collect(['estado', 'urgencia', 'tipo', 'q'])->some(fn ($campo) => filled($filtros[$campo] ?? null));
        @endphp

        <form method="GET" action="{{ route('mantenimiento.casos.index') }}" class="mb-8 rounded-[10px] border border-[#e4ebdf] bg-white p-5 shadow-sm">
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                <div>
                    <label for="filtro-estado" class="mb-2 block text-sm font-semibold text-[#0f3c2b]">Estado</label>
                    <select id="filtro-estado" name="estado" class="w-full rounded-2xl border border-[#d8e2d7] bg-[#f8faf6] px-3 py-2.5 text-[#0f3c2b]">
                        <option value="">Todos</option>
                        @foreach (App\Services\CasoMantenimientoService::ESTADOS as $estado)
                            <option value="{{ $estado }}" @selected(($filtros['estado'] ?? '') === $estado)>{{ ucfirst(str_replace('_', ' ', $estado)) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="filtro-urgencia" class="mb-2 block text-sm font-semibold text-[#0f3c2b]">Urgencia</label>
                    <select id="filtro-urgencia" name="urgencia" class="w-full rounded-2xl border border-[#d8e2d7] bg-[#f8faf6] px-3 py-2.5 text-[#0f3c2b]">
                        <option value="">Todas</option>
                        @foreach (App\Services\SolicitudMantenimientoService::URGENCIAS as $urgencia)
                            <option value="{{ $urgencia }}" @selected(($filtros['urgencia'] ?? '') === $urgencia)>{{ ucfirst($urgencia) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="filtro-tipo" class="mb-2 block text-sm font-semibold text-[#0f3c2b]">Tipo</label>
                    <select id="filtro-tipo" name="tipo" class="w-full rounded-2xl border border-[#d8e2d7] bg-[#f8faf6] px-3 py-2.5 text-[#0f3c2b]">
                        <option value="">Todos</option>
                        @foreach (App\Services\SolicitudMantenimientoService::TIPOS as $tipo)
                            <option value="{{ $tipo }}" @selected(($filtros['tipo'] ?? '') === $tipo)>{{ ucfirst(str_replace('_', ' ', $tipo)) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="xl:col-span-2">
                    <label for="filtro-q" class="mb-2 block text-sm font-semibold text-[#0f3c2b]">Búsqueda</label>
                    <div class="flex gap-2">
                        <input id="filtro-q" type="text" name="q" value="{{ $filtros['q'] ?? '' }}" class="w-full rounded-2xl border border-[#d8e2d7] bg-[#f8faf6] px-3 py-2.5 text-[#0f3c2b]" placeholder="Código o área verde">
                        <button type="submit" class="rounded-full bg-[#7cb22b] px-5 py-2.5 text-sm font-semibold text-white">Filtrar</button>
                        @if ($hayFiltros)
                            <a href="{{ route('mantenimiento.casos.index') }}" class="rounded-full border border-[#dce6d7] px-5 py-2.5 text-sm font-medium text-[#3b5b42]">Limpiar</a>
                        @endif
                    </div>
                </div>
            </div>
        </form>

        @if ($casos->isEmpty())
            <div class="rounded-[10px] border border-dashed border-[#cfe0c2] bg-[#f8faf6] px-6 py-12 text-center">
                <h2 class="text-xl font-bold text-[#0f3c2b]">
                    {{ $hayFiltros ? 'No hay casos que coincidan con los filtros' : 'No hay casos registrados' }}
                </h2>
                <p class="mt-2 text-sm text-[#5f6d5f]">
                    {{ $hayFiltros ? 'Prueba con otros criterios de búsqueda.' : 'Todavía no hay solicitudes de mantenimiento de clientes.' }}
                </p>
            </div>
        @else
            <section class="flex p-8 bg-[#fefdfe] text-[#304e42] rounded-[10px] shadow-xl overflow-x-auto">
                <table class="w-full min-w-[900px] text-left">
                    <thead class="bg-[#f3f5f3]">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Código</th>
                            <th class="px-4 py-3 font-semibold">Solicitante</th>
                            <th class="px-4 py-3 font-semibold">Área verde</th>
                            <th class="px-4 py-3 font-semibold">Tipo</th>
                            <th class="px-4 py-3 font-semibold">Urgencia</th>
                            <th class="px-4 py-3 font-semibold">Estado</th>
                            <th class="px-4 py-3 font-semibold">Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($casos as $caso)
                            <tr class="border-t-2 border-[#c2c4c7]">
                                <td class="px-4 py-3 font-semibold text-[#0f3c2b]">
                                    <a href="{{ route('mantenimiento.casos.show', $caso) }}" class="text-[#0f3c2b] underline underline-offset-2">{{ $caso->codigo }}</a>
                                </td>
                                <td class="px-4 py-3">
                                    {{ $caso->solicitante?->name ?? 'Sin solicitante' }}
                                    <div class="text-xs text-[#5f6d5f]">{{ $caso->solicitante?->email ?? '' }}</div>
                                </td>
                                <td class="px-4 py-3">{{ $caso->area_verde }}</td>
                                <td class="px-4 py-3">{{ ucfirst(str_replace('_', ' ', $caso->tipo_mantenimiento)) }}</td>
                                <td class="px-4 py-3">{{ ucfirst($caso->urgencia) }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full bg-[#edf8e7] px-2.5 py-1 text-xs font-semibold text-[#3b5d2d]">
                                        {{ ucfirst(str_replace('_', ' ', $caso->estado)) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">{{ $caso->created_at?->format('d/m/Y') ?? 'Sin fecha' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>

            @if ($casos->hasPages())
                <div class="mt-6">
                    {{ $casos->links() }}
                </div>
            @endif
        @endif
    </main>
</body>

</html>
