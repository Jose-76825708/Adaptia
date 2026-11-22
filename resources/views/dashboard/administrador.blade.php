<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    @vite('resources/css/app.css')
    <title>Panel de administración | Adaptia</title>
</head>
<body class="flex min-h-screen bg-[#f4f7f3] font-sans text-[#17372a]">
    @include('partials.sidebar')

    <main class="min-w-0 flex-1 overflow-y-auto">
        <div class="mx-auto max-w-[1600px] space-y-7 p-5 sm:p-8">
            <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-semibold text-[#6d8c72]">{{ now()->locale('es')->translatedFormat('l, d \d\e F \d\e Y') }}</p>
                    <h1 class="mt-1 text-3xl font-bold tracking-tight text-[#103928] sm:text-4xl">Panel de administración</h1>
                    <p class="mt-2 text-sm text-[#69776d]">Una vista general de inventario, ventas, mantenimiento y monitoreo.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('plantas.create') }}" class="rounded-xl bg-[#0f4933] px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#176243]">Registrar planta</a>
                    <a href="{{ route('planeacion-comercial.campanas.index') }}" class="rounded-xl border border-[#d7e3d3] bg-white px-4 py-3 text-sm font-semibold text-[#24543a] transition hover:bg-[#f8fbf6]">Ver campañas</a>
                </div>
            </header>

            <section class="overflow-hidden rounded-3xl bg-gradient-to-r from-[#0d3a29] via-[#145337] to-[#397344] p-6 text-white shadow-lg shadow-[#123d2c]/10 sm:p-8">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                    <div class="max-w-2xl">
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#c9e2b1]">Resumen operativo</p>
                        <h2 class="mt-3 text-2xl font-bold sm:text-3xl">Buenos días, {{ auth()->user()->name }}</h2>
                        <p class="mt-2 text-sm leading-6 text-white/75">Revisa las prioridades de hoy y accede directamente a cada macroproceso.</p>
                    </div>
                    <div class="grid grid-cols-2 gap-3 text-sm sm:min-w-[340px]">
                        <div class="rounded-2xl border border-white/10 bg-white/10 p-4">
                            <p class="text-white/70">Productos registrados</p>
                            <p class="mt-1 text-2xl font-bold">{{ number_format($resumen['total_plantas']) }}</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/10 p-4">
                            <p class="text-white/70">Unidades en inventario</p>
                            <p class="mt-1 text-2xl font-bold">{{ number_format($resumen['unidades_en_stock']) }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 2xl:grid-cols-4">
                @foreach ([
                    ['label' => 'Ventas del mes', 'value' => number_format($resumen['ventas_mes']), 'detail' => number_format($resumen['unidades_vendidas_mes']) . ' unidades vendidas', 'icon' => '↗'],
                    ['label' => 'Ingresos estimados', 'value' => 'S/ ' . number_format($resumen['ingresos_estimados_mes'], 2), 'detail' => 'Estimados con el precio actual', 'icon' => 'S/'],
                    ['label' => 'Plantas bajo mínimo', 'value' => number_format($resumen['plantas_bajo_stock']), 'detail' => 'Requieren revisión de inventario', 'icon' => '!'],
                    ['label' => 'Casos activos', 'value' => number_format($resumen['casos_abiertos']), 'detail' => 'Solicitudes en mantenimiento', 'icon' => '○'],
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
                            <h2 class="text-lg font-bold text-[#153d2c]">Ventas recientes</h2>
                            <p class="mt-1 text-sm text-[#849084]">Actividad registrada en el vivero</p>
                        </div>
                        <a href="{{ route('movimientos-inventario.index') }}" class="text-sm font-semibold text-[#4c7b36] hover:underline">Ver inventario</a>
                    </div>
                    @if ($resumen['ventas_recientes']->isEmpty())
                        <p class="rounded-xl bg-[#f7f9f6] p-5 text-sm text-[#748174]">Todavía no hay ventas registradas.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[560px] text-left text-sm">
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
                        <p class="mt-4 text-xs text-[#929b90]">Los ingresos son aproximados: se calculan con el precio actual, ya que el sistema no guarda el precio histórico de cada venta.</p>
                    @endif
                </article>

                <article class="rounded-2xl border border-[#e2e9df] bg-white p-5 shadow-sm">
                    <div class="mb-5">
                        <h2 class="text-lg font-bold text-[#153d2c]">Inventario prioritario</h2>
                        <p class="mt-1 text-sm text-[#849084]">Plantas por debajo del stock mínimo</p>
                    </div>
                    @forelse ($resumen['plantas_criticas'] as $planta)
                        <div class="flex items-center justify-between gap-3 border-b border-[#f0f2ee] py-3 last:border-0">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-[#294a36]">{{ $planta->nombre }}</p>
                                <p class="mt-1 text-xs text-[#849084]">Mínimo: {{ $planta->stock_minimo }}</p>
                            </div>
                            <span class="rounded-full bg-[#fff3e8] px-3 py-1 text-xs font-bold text-[#a95618]">{{ $planta->stock_actual }} disponibles</span>
                        </div>
                    @empty
                        <p class="rounded-xl bg-[#f7f9f6] p-4 text-sm text-[#748174]">Todo el inventario está sobre el mínimo.</p>
                    @endforelse
                    <a href="{{ route('plantas.index') }}" class="mt-4 inline-flex text-sm font-semibold text-[#4c7b36] hover:underline">Abrir inventario →</a>
                </article>
            </section>

            <section class="grid grid-cols-1 gap-5 xl:grid-cols-2">
                <article class="rounded-2xl border border-[#e2e9df] bg-white p-5 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <div><h2 class="text-lg font-bold text-[#153d2c]">Mantenimiento</h2><p class="mt-1 text-sm text-[#849084]">{{ $resumen['casos_abiertos'] }} casos activos</p></div>
                        <a href="{{ route('mantenimiento.casos.index') }}" class="text-sm font-semibold text-[#4c7b36] hover:underline">Ver casos</a>
                    </div>
                    @forelse ($resumen['casos_recientes'] as $caso)
                        <a href="{{ route('mantenimiento.casos.index') }}" class="flex items-center justify-between gap-3 border-t border-[#f0f2ee] py-3">
                            <div><p class="text-sm font-semibold text-[#294a36]">{{ $caso->codigo }} · {{ $caso->area_verde }}</p><p class="mt-1 text-xs text-[#849084]">{{ str_replace('_', ' ', ucfirst($caso->estado)) }} · {{ ucfirst($caso->urgencia) }}</p></div>
                            <span class="rounded-full bg-[#f0f5eb] px-3 py-1 text-xs font-semibold text-[#567c3d]">{{ ucfirst($caso->tipo_mantenimiento) }}</span>
                        </a>
                    @empty
                        <p class="rounded-xl bg-[#f7f9f6] p-4 text-sm text-[#748174]">No hay casos pendientes.</p>
                    @endforelse
                </article>

                <article class="rounded-2xl border border-[#e2e9df] bg-white p-5 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <div><h2 class="text-lg font-bold text-[#153d2c]">Monitoreo IoT</h2><p class="mt-1 text-sm text-[#849084]">{{ $resumen['alertas_activas'] }} alertas por revisar · {{ $resumen['sensores_activos'] }} sensores activos</p></div>
                        <a href="{{ route('sensores.index') }}" class="text-sm font-semibold text-[#4c7b36] hover:underline">Ver sensores</a>
                    </div>
                    @forelse ($resumen['alertas_recientes'] as $alerta)
                        <div class="flex items-center justify-between gap-3 border-t border-[#f0f2ee] py-3">
                            <div><p class="text-sm font-semibold text-[#294a36]">{{ $alerta['planta'] }}</p><p class="mt-1 text-xs text-[#849084]">{{ ucfirst(str_replace('_', ' ', $alerta['tipo'])) }}{{ $alerta['variable'] ? ' · ' . str_replace('_', ' ', $alerta['variable']) : '' }}</p></div>
                            <span class="rounded-full bg-[#fff3e8] px-3 py-1 text-xs font-semibold text-[#a95618]">Activa</span>
                        </div>
                    @empty
                        <p class="rounded-xl bg-[#f7f9f6] p-4 text-sm text-[#748174]">No hay alertas activas.</p>
                    @endforelse
                </article>
            </section>

            <section class="grid grid-cols-1 gap-5 xl:grid-cols-2">
                <article class="rounded-2xl border border-[#e2e9df] bg-white p-5 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <div><h2 class="text-lg font-bold text-[#153d2c]">Planeación comercial</h2><p class="mt-1 text-sm text-[#849084]">{{ $resumen['campanas_activas'] }} campañas en curso · {{ $resumen['solicitudes_disponibilidad'] }} solicitudes pendientes</p></div>
                        <a href="{{ route('planeacion-comercial.campanas.index') }}" class="text-sm font-semibold text-[#4c7b36] hover:underline">Abrir planeación</a>
                    </div>
                    @forelse ($resumen['campanas_recientes'] as $campana)
                        <a href="{{ route('planeacion-comercial.campanas.show', $campana->id) }}" class="flex items-center justify-between gap-3 border-t border-[#f0f2ee] py-3">
                            <div><p class="text-sm font-semibold text-[#294a36]">{{ $campana->codigo }}</p><p class="mt-1 line-clamp-1 text-xs text-[#849084]">{{ $campana->objetivos }}</p></div>
                            <span class="rounded-full bg-[#f0f5eb] px-3 py-1 text-xs font-semibold text-[#567c3d]">{{ str_replace('_', ' ', $campana->estado) }}</span>
                        </a>
                    @empty
                        <p class="rounded-xl bg-[#f7f9f6] p-4 text-sm text-[#748174]">No hay campañas activas.</p>
                    @endforelse
                </article>
                <article class="rounded-2xl border border-[#e2e9df] bg-white p-5 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <div><h2 class="text-lg font-bold text-[#153d2c]">Movimientos de inventario</h2><p class="mt-1 text-sm text-[#849084]">Últimos movimientos registrados</p></div>
                        <a href="{{ route('movimientos-inventario.index') }}" class="text-sm font-semibold text-[#4c7b36] hover:underline">Ver movimientos</a>
                    </div>
                    @forelse ($resumen['movimientos_recientes'] as $movimiento)
                        <div class="flex items-center justify-between gap-3 border-t border-[#f0f2ee] py-3">
                            <div><p class="text-sm font-semibold text-[#294a36]">{{ $movimiento->planta?->nombre ?? 'Planta no disponible' }}</p><p class="mt-1 text-xs text-[#849084]">{{ $movimiento->created_at->format('d/m/Y H:i') }}</p></div>
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $movimiento->tipo === 'entrada' ? 'bg-[#edf7ed] text-[#397344]' : 'bg-[#fff3e8] text-[#a95618]' }}">{{ ucfirst($movimiento->tipo) }} · {{ $movimiento->cantidad }}</span>
                        </div>
                    @empty
                        <p class="rounded-xl bg-[#f7f9f6] p-4 text-sm text-[#748174]">No hay movimientos recientes.</p>
                    @endforelse
                </article>
            </section>
        </div>
    </main>
</body>
</html>
