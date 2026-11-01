<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    @vite('resources/css/app.css')
    <title>Alertas de stock</title>
</head>

<body class="flex font-sans h-screen">
    @include('partials.sidebar')

    <main class="flex flex-1 flex-col gap-6 bg-[#fbfbfb] p-8">
        <section class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-[#103928] font-bold text-[2.2rem]">Alertas de stock</h1>
                <p class="text-[#8b8d8f] text-base">Seguimiento del stock mínimo de las plantas.</p>
            </div>

            <form method="GET" action="{{ route('alertas-stock.index') }}" class="flex items-center gap-3">
                <label for="estado" class="text-sm font-semibold text-[#304e42]">Filtro</label>
                <select id="estado" name="estado" onchange="this.form.submit()"
                    class="rounded-lg border border-[#d9dfe6] bg-white px-3 py-2 text-sm text-[#304e42] focus:outline-none focus:ring-2 focus:ring-[#6eab26]">
                    <option value="abiertas" @selected($estado === 'abiertas')>Abiertas</option>
                    <option value="no_leidas" @selected($estado === 'no_leidas')>No leídas</option>
                    <option value="leidas" @selected($estado === 'leidas')>Leídas</option>
                    <option value="resueltas" @selected($estado === 'resueltas')>Resueltas</option>
                    <option value="todas" @selected($estado === 'todas')>Todas</option>
                </select>
            </form>
        </section>

        @if (session('success'))
            <div class="rounded-[10px] border border-[#c3e6cb] bg-[#d4edda] p-4 text-[#155724]">
                {{ session('success') }}
            </div>
        @endif

        @if (session('info'))
            <div class="rounded-[10px] border border-[#d6d8db] bg-[#e2e3e5] p-4 text-[#383d41]">
                {{ session('info') }}
            </div>
        @endif

        <section class="overflow-hidden rounded-[18px] border border-[#edf0ef] bg-white shadow-lg">
            <div class="overflow-x-auto">
                <table class="min-w-full table-auto">
                    <thead class="bg-[#f3f5f3] text-left text-[#304e42]">
                        <tr>
                            <th class="px-4 py-3">Planta</th>
                            <th class="px-4 py-3">Stock</th>
                            <th class="px-4 py-3">Fecha</th>
                            <th class="px-4 py-3">Estado</th>
                            <th class="px-4 py-3 text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($alertas as $alerta)
                            @php
                                $planta = $alerta->planta;
                                $estadoAlerta = 'No leída';

                                if ($alerta->resuelta_en !== null) {
                                    $estadoAlerta = 'Resuelta';
                                } elseif ($alerta->leida) {
                                    $estadoAlerta = 'Leída';
                                }
                            @endphp
                            <tr class="border-t border-[#edf0ef] text-[#304e42]">
                                <td class="px-4 py-3 font-semibold">{{ $planta?->nombre ?? 'Planta no disponible' }}</td>
                                <td class="px-4 py-3">
                                    {{ $planta?->stock_actual ?? '-' }} / {{ $planta?->stock_minimo ?? '-' }}
                                </td>
                                <td class="px-4 py-3">{{ $alerta->created_at?->format('d/m/Y H:i') ?? 'Sin fecha' }}</td>
                                <td class="px-4 py-3">
                                    @if ($alerta->resuelta_en !== null)
                                        <span class="inline-flex rounded-full bg-[#d4edda] px-3 py-1 text-xs font-bold text-[#155724]">Resuelta</span>
                                    @elseif ($alerta->leida)
                                        <span class="inline-flex rounded-full bg-[#d1ecf1] px-3 py-1 text-xs font-bold text-[#0c5460]">Leída</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-[#fff3cd] px-3 py-1 text-xs font-bold text-[#856404]">No leída</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @if ($alerta->resuelta_en === null && ! $alerta->leida)
                                        <form action="{{ route('alertas-stock.marcar-leida', ['alerta' => $alerta->id, 'estado' => $estado]) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="rounded-[10px] bg-[#6eab26] px-4 py-2 text-sm font-semibold text-white transition duration-300 hover:scale-105">
                                                Marcar como leída
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-sm text-[#8b8d8f]">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-[#8b8d8f]">
                                    No hay alertas de stock {{ $estado === 'todas' ? '' : 'en este estado' }}.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        @if ($alertas->hasPages())
            <div class="mt-2">
                {{ $alertas->appends(['estado' => $estado])->links() }}
            </div>
        @endif
    </main>
</body>

</html>
