<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    @vite('resources/css/app.css')
    <title>Planeación comercial</title>
</head>
<body class="flex h-screen font-sans">
    @include('partials.sidebar')

    <main class="flex flex-5 flex-col gap-5 overflow-y-auto bg-[#fbfbfb] p-8">
        <section class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-[2.5em] font-bold text-[#103928]">Planeación comercial</h1>
                <p class="text-[#68766b]">Campañas, disponibilidad, demanda y evaluación de resultados.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('planeacion-comercial.demanda') }}" class="rounded-full border border-[#dce6d7] bg-white px-5 py-2.5 text-sm font-semibold text-[#3b5b42]">Consultar demanda</a>
                @if (auth()->user()->rol === 'vendedor')
                    <a href="{{ route('planeacion-comercial.campanas.create') }}" class="rounded-full bg-[#7cb22b] px-5 py-2.5 text-sm font-semibold text-white">Nueva campaña</a>
                @endif
            </div>
        </section>

        @if (session('success'))
            <div class="rounded-[10px] border border-[#cfe0c2] bg-[#edf8e7] p-4 text-sm font-semibold text-[#3b5d2d]" role="status">{{ session('success') }}</div>
        @endif

        <form method="GET" action="{{ route('planeacion-comercial.campanas.index') }}" class="rounded-[10px] border border-[#e4ebdf] bg-white p-5 shadow-sm">
            <div class="grid gap-4 md:grid-cols-[1fr_2fr_auto]">
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
                    <label for="filtro-q" class="mb-2 block text-sm font-semibold text-[#0f3c2b]">Búsqueda</label>
                    <input id="filtro-q" type="search" name="q" value="{{ $filtros['q'] ?? '' }}" maxlength="100" placeholder="Código u objetivo" class="w-full rounded-2xl border border-[#d8e2d7] bg-[#f8faf6] px-3 py-2.5 text-[#0f3c2b]">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="rounded-full bg-[#7cb22b] px-5 py-2.5 text-sm font-semibold text-white">Filtrar</button>
                    <a href="{{ route('planeacion-comercial.campanas.index') }}" class="rounded-full border border-[#dce6d7] px-5 py-2.5 text-sm font-medium text-[#3b5b42]">Limpiar</a>
                </div>
            </div>
        </form>

        @if ($campanas->isEmpty())
            <div class="rounded-[10px] border border-dashed border-[#cfe0c2] bg-[#f8faf6] px-6 py-12 text-center">
                <h2 class="text-xl font-bold text-[#0f3c2b]">No hay campañas para mostrar</h2>
                <p class="mt-2 text-sm text-[#5f6d5f]">Cambia los filtros o crea una campaña comercial.</p>
            </div>
        @else
            <section class="overflow-x-auto rounded-[10px] bg-white p-6 shadow-sm">
                <table class="w-full min-w-[850px] text-left text-sm text-[#304e42]">
                    <thead class="bg-[#edf8e7] text-[#3b5d2d]">
                        <tr>
                            <th class="px-4 py-3">Código</th>
                            <th class="px-4 py-3">Objetivos</th>
                            <th class="px-4 py-3">Estado</th>
                            <th class="px-4 py-3">Periodo</th>
                            <th class="px-4 py-3">Plantas</th>
                            <th class="px-4 py-3">Responsable</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($campanas as $campana)
                            <tr class="border-t border-[#e4ebdf]">
                                <td class="px-4 py-3 font-semibold"><a class="underline underline-offset-2" href="{{ route('planeacion-comercial.campanas.show', $campana) }}">{{ $campana->codigo }}</a></td>
                                <td class="max-w-md px-4 py-3">{{ $campana->objetivos }}</td>
                                <td class="px-4 py-3">{{ ucfirst(str_replace('_', ' ', $campana->estado)) }}</td>
                                <td class="whitespace-nowrap px-4 py-3">{{ $campana->fecha_inicio->format('d/m/Y') }} – {{ $campana->fecha_fin->format('d/m/Y') }}</td>
                                <td class="px-4 py-3">{{ $campana->productos_count }}</td>
                                <td class="px-4 py-3">{{ $campana->creador?->name ?? 'Sin responsable' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>
            @if ($campanas->hasPages())
                <div>{{ $campanas->links() }}</div>
            @endif
        @endif
    </main>
</body>
</html>
