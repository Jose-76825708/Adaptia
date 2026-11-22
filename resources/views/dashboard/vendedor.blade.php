<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    @vite('resources/css/app.css')
    <title>Panel de ventas | Adaptia</title>
</head>
<body class="flex min-h-screen bg-[#f4f7f3] font-sans text-[#17372a]">
    @include('partials.sidebar')

    <main class="min-w-0 flex-1 overflow-y-auto">
        <div class="mx-auto max-w-[1500px] space-y-7 p-5 sm:p-8">
            <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-semibold text-[#6d8c72]">{{ now()->locale('es')->translatedFormat('l, d \d\e F \d\e Y') }}</p>
                    <h1 class="mt-1 text-3xl font-bold tracking-tight text-[#103928] sm:text-4xl">Panel de ventas</h1>
                    <p class="mt-2 text-sm text-[#69776d]">Hola, {{ auth()->user()->name }}. Aquí tienes tu actividad comercial y el estado del inventario.</p>
                </div>
                <a href="{{ route('ventas.create') }}" class="inline-flex items-center justify-center rounded-xl bg-[#0f4933] px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#176243]">+ Registrar venta</a>
            </header>

            <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 2xl:grid-cols-4">
                @foreach ([
                    ['label' => 'Ventas de hoy', 'value' => number_format($resumen['ventas_hoy']), 'detail' => number_format($resumen['unidades_hoy']) . ' unidades', 'icon' => '↗'],
                    ['label' => 'Ventas del mes', 'value' => number_format($resumen['ventas_mes']), 'detail' => number_format($resumen['unidades_mes']) . ' unidades vendidas', 'icon' => '▤'],
                    ['label' => 'Ingreso estimado del mes', 'value' => 'S/ ' . number_format($resumen['ingresos_estimados_mes'], 2), 'detail' => 'Con el precio actual de las plantas', 'icon' => 'S/'],
                    ['label' => 'Plantas bajo mínimo', 'value' => number_format($resumen['plantas_bajo_stock']), 'detail' => 'Verifica disponibilidad antes de vender', 'icon' => '!'],
                ] as $tarjeta)
                    <article class="rounded-2xl border border-[#e2e9df] bg-white p-5 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-medium text-[#748174]">{{ $tarjeta['label'] }}</p>
                                <p class="mt-3 text-3xl font-bold tracking-tight text-[#153d2c]">{{ $tarjeta['value'] }}</p>
                                <p class="mt-2 text-xs text-[#879187]">{{ $tarjeta['detail'] }}</p>
                            </div>
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#eff5eb] font-bold text-[#518034]">{{ $tarjeta['icon'] }}</span>
                        </div>
                    </article>
                @endforeach
            </section>

            <section class="grid grid-cols-1 gap-5 2xl:grid-cols-3">
                <article class="rounded-2xl border border-[#e2e9df] bg-white p-5 shadow-sm 2xl:col-span-2">
                    <div class="mb-5 flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-bold text-[#153d2c]">Mis ventas recientes</h2>
                            <p class="mt-1 text-sm text-[#849084]">Solo se muestran tus ventas registradas</p>
                        </div>
                        <a href="{{ route('ventas.index') }}" class="text-sm font-semibold text-[#4c7b36] hover:underline">Ver todas</a>
                    </div>
                    @if ($resumen['ventas_recientes']->isEmpty())
                        <div class="rounded-xl bg-[#f7f9f6] p-6 text-center">
                            <p class="font-semibold text-[#45634d]">Aún no tienes ventas registradas</p>
                            <p class="mt-1 text-sm text-[#849084]">Cuando registres una venta, el resumen aparecerá aquí.</p>
                            <a href="{{ route('ventas.create') }}" class="mt-4 inline-flex rounded-lg bg-[#0f4933] px-4 py-2 text-sm font-semibold text-white">Registrar primera venta</a>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[540px] text-left text-sm">
                                <thead class="border-b border-[#edf0eb] text-xs uppercase tracking-wide text-[#8a9589]">
                                    <tr><th class="pb-3 font-semibold">Planta</th><th class="pb-3 font-semibold">Unidades</th><th class="pb-3 font-semibold">Fecha</th><th class="pb-3 text-right font-semibold">Ingreso estimado</th></tr>
                                </thead>
                                <tbody class="divide-y divide-[#f0f2ee]">
                                    @foreach ($resumen['ventas_recientes'] as $venta)
                                        <tr>
                                            <td class="py-4 font-semibold text-[#294a36]">{{ $venta['planta'] }}</td>
                                            <td class="py-4 text-[#667366]">{{ number_format($venta['cantidad']) }}</td>
                                            <td class="py-4 text-[#667366]">{{ $venta['fecha']?->format('d/m/Y H:i') }}</td>
                                            <td class="py-4 text-right font-semibold text-[#294a36]">S/ {{ number_format($venta['ingreso_estimado'], 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <p class="mt-4 text-xs text-[#929b90]">Estimación basada en el precio actual; las ventas no conservan el precio histórico.</p>
                    @endif
                </article>

                <article class="rounded-2xl border border-[#e2e9df] bg-white p-5 shadow-sm">
                    <div class="mb-5 flex items-center justify-between gap-3">
                        <div><h2 class="text-lg font-bold text-[#153d2c]">Stock por revisar</h2><p class="mt-1 text-sm text-[#849084]">Bajo el mínimo configurado</p></div>
                        <a href="{{ route('plantas.index') }}" class="text-sm font-semibold text-[#4c7b36] hover:underline">Inventario</a>
                    </div>
                    @forelse ($resumen['plantas_criticas'] as $planta)
                        <div class="flex items-center justify-between gap-3 border-b border-[#f0f2ee] py-3 last:border-0">
                            <div class="min-w-0"><p class="truncate text-sm font-semibold text-[#294a36]">{{ $planta->nombre }}</p><p class="mt-1 text-xs text-[#849084]">Mínimo: {{ $planta->stock_minimo }}</p></div>
                            <span class="rounded-full bg-[#fff3e8] px-3 py-1 text-xs font-bold text-[#a95618]">{{ $planta->stock_actual }} disponibles</span>
                        </div>
                    @empty
                        <p class="rounded-xl bg-[#f7f9f6] p-4 text-sm text-[#748174]">No hay plantas bajo mínimo.</p>
                    @endforelse
                </article>
            </section>

            <section class="grid grid-cols-1 gap-5 xl:grid-cols-2">
                <article class="rounded-2xl border border-[#e2e9df] bg-white p-5 shadow-sm">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div><h2 class="text-lg font-bold text-[#153d2c]">Campañas en curso</h2><p class="mt-1 text-sm text-[#849084]">Planeación y promoción comercial</p></div>
                        <a href="{{ route('planeacion-comercial.campanas.index') }}" class="text-sm font-semibold text-[#4c7b36] hover:underline">Ver campañas</a>
                    </div>
                    @forelse ($resumen['campanas_activas'] as $campana)
                        <a href="{{ route('planeacion-comercial.campanas.show', $campana->id) }}" class="flex items-center justify-between gap-3 border-t border-[#f0f2ee] py-3">
                            <div class="min-w-0"><p class="text-sm font-semibold text-[#294a36]">{{ $campana->codigo }}</p><p class="mt-1 line-clamp-1 text-xs text-[#849084]">{{ $campana->objetivos }}</p></div>
                            <span class="rounded-full bg-[#f0f5eb] px-3 py-1 text-xs font-semibold text-[#567c3d]">{{ str_replace('_', ' ', $campana->estado) }}</span>
                        </a>
                    @empty
                        <p class="rounded-xl bg-[#f7f9f6] p-4 text-sm text-[#748174]">No hay campañas activas.</p>
                    @endforelse
                    <a href="{{ route('planeacion-comercial.campanas.create') }}" class="mt-3 inline-flex rounded-lg border border-[#d7e3d3] px-4 py-2 text-sm font-semibold text-[#456a3a] transition hover:bg-[#f8fbf6]">Crear campaña</a>
                </article>

                <article class="rounded-2xl bg-gradient-to-br from-[#eaf2e4] to-[#f6f8f2] p-6">
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#64834c]">Atajos de trabajo</p>
                    <h2 class="mt-2 text-xl font-bold text-[#153d2c]">¿Qué necesitas hacer?</h2>
                    <div class="mt-5 grid gap-3 sm:grid-cols-2">
                        <a href="{{ route('ventas.create') }}" class="rounded-xl border border-white bg-white p-4 shadow-sm transition hover:-translate-y-0.5"><span class="text-sm font-bold text-[#294a36]">Registrar una venta</span><span class="mt-1 block text-xs text-[#849084]">Selecciona cliente y planta</span></a>
                        <a href="{{ route('asistente.index') }}" class="rounded-xl border border-white bg-white p-4 shadow-sm transition hover:-translate-y-0.5"><span class="text-sm font-bold text-[#294a36]">Consultar al asistente</span><span class="mt-1 block text-xs text-[#849084]">Datos y sugerencias comerciales</span></a>
                        <a href="{{ route('plantas-vendidas.index') }}" class="rounded-xl border border-white bg-white p-4 shadow-sm transition hover:-translate-y-0.5"><span class="text-sm font-bold text-[#294a36]">Asignar sensores</span><span class="mt-1 block text-xs text-[#849084]">Revisa plantas vendidas</span></a>
                        <a href="{{ route('planeacion-comercial.demanda') }}" class="rounded-xl border border-white bg-white p-4 shadow-sm transition hover:-translate-y-0.5"><span class="text-sm font-bold text-[#294a36]">Analizar demanda</span><span class="mt-1 block text-xs text-[#849084]">Explora ventas e inventario</span></a>
                    </div>
                </article>
            </section>
        </div>
    </main>
</body>
</html>
