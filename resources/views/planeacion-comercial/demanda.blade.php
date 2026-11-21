<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    @vite('resources/css/app.css')
    <title>Demanda comercial</title>
</head>
<body class="flex h-screen font-sans">
    @include('partials.sidebar')

    <main class="flex flex-5 flex-col gap-5 overflow-y-auto bg-[#fbfbfb] p-8">
        <section class="flex items-center justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#7a965f]">Planeación comercial</p>
                <h1 class="text-[2.5em] font-bold text-[#103928]">Demanda y oportunidades</h1>
            </div>
            <a href="{{ route('planeacion-comercial.campanas.index') }}" class="rounded-full border border-[#dce6d7] bg-white px-5 py-2.5 text-sm font-semibold text-[#3b5b42]">Volver a campañas</a>
        </section>

        @if ($errors->any())
            <div class="rounded-[10px] border border-red-200 bg-red-50 p-4 text-sm text-red-700" role="alert">
                @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
            </div>
        @endif

        <form method="GET" action="{{ route('planeacion-comercial.demanda') }}" class="grid gap-4 rounded-[10px] border border-[#e4ebdf] bg-white p-5 shadow-sm md:grid-cols-[1fr_1fr_auto]">
            <div>
                <label for="inicio" class="mb-2 block text-sm font-semibold text-[#0f3c2b]">Desde</label>
                <input id="inicio" name="inicio" type="date" value="{{ $inicio }}" required class="w-full rounded-2xl border border-[#d8e2d7] bg-[#f8faf6] px-3 py-2.5">
            </div>
            <div>
                <label for="fin" class="mb-2 block text-sm font-semibold text-[#0f3c2b]">Hasta</label>
                <input id="fin" name="fin" type="date" value="{{ $fin }}" required class="w-full rounded-2xl border border-[#d8e2d7] bg-[#f8faf6] px-3 py-2.5">
            </div>
            <div class="flex items-end"><button type="submit" class="rounded-full bg-[#7cb22b] px-5 py-2.5 text-sm font-semibold text-white">Consultar</button></div>
        </form>

        <p class="text-sm text-[#5f6d5f]">Periodo consultado: {{ \Carbon\Carbon::parse($inicio)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($fin)->format('d/m/Y') }}. No se muestran métricas de recomendaciones.</p>

        <section class="rounded-[10px] border border-[#e4ebdf] bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-xl font-bold text-[#103928]">Ranking de ventas por planta</h2>
            @if (count($demanda['ranking_ventas']) === 0)
                <p class="text-sm text-[#5f6d5f]">No se registraron ventas en el periodo.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[600px] text-left text-sm text-[#304e42]">
                        <thead class="bg-[#edf8e7] text-[#3b5d2d]"><tr><th class="px-4 py-3">Planta</th><th class="px-4 py-3">Unidades vendidas</th><th class="px-4 py-3">Stock actual</th></tr></thead>
                        <tbody>
                            @foreach ($demanda['ranking_ventas'] as $fila)
                                <tr class="border-t border-[#e4ebdf]"><td class="px-4 py-3">{{ $fila['nombre'] }}</td><td class="px-4 py-3">{{ $fila['cantidad_vendida'] }}</td><td class="px-4 py-3">{{ $fila['stock_actual'] }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <div class="grid gap-5 xl:grid-cols-2">
            <section class="rounded-[10px] border border-[#e4ebdf] bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-xl font-bold text-[#103928]">Bajo stock mínimo</h2>
                @if (count($demanda['stock_bajo']) === 0)
                    <p class="text-sm text-[#5f6d5f]">No hay plantas bajo el mínimo.</p>
                @else
                    <table class="w-full text-left text-sm text-[#304e42]">
                        <thead class="bg-[#edf8e7] text-[#3b5d2d]"><tr><th class="px-3 py-2">Planta</th><th class="px-3 py-2">Actual</th><th class="px-3 py-2">Mínimo</th></tr></thead>
                        <tbody>@foreach ($demanda['stock_bajo'] as $fila)<tr class="border-t border-[#e4ebdf]"><td class="px-3 py-2">{{ $fila['nombre'] }}</td><td class="px-3 py-2">{{ $fila['stock_actual'] }}</td><td class="px-3 py-2">{{ $fila['stock_minimo'] }}</td></tr>@endforeach</tbody>
                    </table>
                @endif
            </section>
            <section class="rounded-[10px] border border-[#e4ebdf] bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-xl font-bold text-[#103928]">Plantas sin ventas en el periodo</h2>
                @if (count($demanda['sin_ventas']) === 0)
                    <p class="text-sm text-[#5f6d5f]">Todas las plantas registran ventas.</p>
                @else
                    <table class="w-full text-left text-sm text-[#304e42]">
                        <thead class="bg-[#edf8e7] text-[#3b5d2d]"><tr><th class="px-3 py-2">Planta</th><th class="px-3 py-2">Stock actual</th></tr></thead>
                        <tbody>@foreach ($demanda['sin_ventas'] as $fila)<tr class="border-t border-[#e4ebdf]"><td class="px-3 py-2">{{ $fila['nombre'] }}</td><td class="px-3 py-2">{{ $fila['stock_actual'] }}</td></tr>@endforeach</tbody>
                    </table>
                @endif
            </section>
        </div>
    </main>
</body>
</html>
