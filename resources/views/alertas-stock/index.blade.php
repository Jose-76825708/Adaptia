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

    <main class="flex flex-5 flex-col p-25 bg-[#fbfbfb] h-full gap-4">
        <section class="flex items-center justify-between">
            <div class="flex-4">
                <h1 class="text-[#103928] font-bold text-[2.5em]">Alertas de stock</h1>
                <p class="text-[#8b8d8f] text-[1em]">Seguimiento del stock mínimo de las plantas.</p>
            </div>

            <form method="GET" action="{{ route('alertas-stock.index') }}" class="flex flex-1 flex-col gap-1 text-[#304e42]">
                <label for="estado" class="font-bold">Filtro</label>
                <select id="estado" name="estado" onchange="this.form.submit()"
                    class="p-2 bg-[#f3f5f3] border-2 border-[#ecedea] rounded-[8px] outline-none focus:border-[#629f22]">
                    <option value="abiertas" @selected($estado === 'abiertas')>Abiertas</option>
                    <option value="no_leidas" @selected($estado === 'no_leidas')>No leídas</option>
                    <option value="leidas" @selected($estado === 'leidas')>Leídas</option>
                    <option value="resueltas" @selected($estado === 'resueltas')>Resueltas</option>
                    <option value="todas" @selected($estado === 'todas')>Todas</option>
                </select>
            </form>
        </section>

        @if (session('success'))
            <div class="p-4 bg-green-100 text-green-700 rounded-[10px] border border-green-200 text-center font-medium">
                {{ session('success') }}
            </div>
        @endif

        @if (session('info'))
            <div class="p-4 bg-yellow-100 text-yellow-700 rounded-[10px] border border-green-200 text-center font-medium">
                {{ session('info') }}
            </div>
        @endif

        <section class="flex p-8 bg-[#fefdfe] text-[#304e42] rounded-[10px] shadow-xl overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-[#f3f5f3]">
                    <tr>
                        <th class="rounded-tl-[20px] p-4">Planta</th>
                        <th class="p-4">Stock</th>
                        <th class="p-4">Fecha</th>
                        <th class="p-4">Estado</th>
                        <th class="rounded-tr-[20px] p-4 text-center">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($alertas as $alerta)
                        @php($planta = $alerta->planta)
                        <tr class="border-t-2 border-[#c2c4c7]">
                            <td class="p-4 font-bold">{{ $planta?->nombre ?? 'Planta no disponible' }}</td>
                            <td class="p-4">{{ $planta?->stock_actual ?? '-' }} / {{ $planta?->stock_minimo ?? '-' }}</td>
                            <td class="p-4">{{ $alerta->created_at?->format('d/m/Y H:i') ?? 'Sin fecha' }}</td>
                            <td class="p-4">
                                @if ($alerta->resuelta_en !== null)
                                    <span class="px-2 py-1 rounded-full text-xs font-bold bg-green-100 text-green-600">Resuelta</span>
                                @elseif ($alerta->leida)
                                    <span class="px-2 py-1 rounded-full text-xs font-bold bg-gray-200 text-gray-600">Leída</span>
                                @else
                                    <span class="px-2 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-700">No leída</span>
                                @endif
                            </td>
                            <td class="p-4 text-center">
                                @if ($alerta->resuelta_en === null && ! $alerta->leida)
                                    <form action="{{ route('alertas-stock.marcar-leida', ['alerta' => $alerta->id, 'estado' => $estado]) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="py-2 px-6 bg-[#629f22] text-[#fbfbfb] font-bold rounded-[10px] hover:scale-105 transition duration-200">
                                            Marcar como leída
                                        </button>
                                    </form>
                                @else
                                    <span class="block text-center text-[#8b8d8f]">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-4 text-center text-[#8b8d8f]">
                                No hay alertas de stock {{ $estado === 'todas' ? '' : 'en este estado' }}.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        @if ($alertas->hasPages())
            <div>
                {{ $alertas->appends(['estado' => $estado])->links() }}
            </div>
        @endif
    </main>
</body>

</html>
