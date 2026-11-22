<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    @vite('resources/css/app.css')
    <title>{{ $campana->codigo }} · Planeación comercial</title>
</head>
<body class="flex h-screen font-sans">
    @include('partials.sidebar')

    <main class="flex flex-5 flex-col gap-5 overflow-y-auto bg-[#fbfbfb] p-8">
        <section class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#7a965f]">Planeación comercial / {{ $campana->codigo }}</p>
                <h1 class="text-[2.5em] font-bold text-[#103928]">Detalle de campaña</h1>
            </div>
            <a href="{{ route('planeacion-comercial.campanas.index') }}" class="rounded-full border border-[#dce6d7] bg-white px-5 py-2.5 text-sm font-semibold text-[#3b5b42]">Volver a campañas</a>
        </section>

        @if (session('success'))
            <div class="rounded-[10px] border border-[#cfe0c2] bg-[#edf8e7] p-4 text-sm font-semibold text-[#3b5d2d]" role="status">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="rounded-[10px] border border-red-200 bg-red-50 p-4 text-sm text-red-700" role="alert">
                @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
            </div>
        @endif

        <section class="rounded-[10px] border border-[#e4ebdf] bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#7a965f]">Código de campaña</p>
                    <h2 class="mt-1 text-2xl font-bold text-[#0f3c2b]">{{ $campana->codigo }}</h2>
                </div>
                <span class="rounded-full bg-[#edf8e7] px-3 py-1 text-sm font-semibold text-[#3b5d2d]">{{ ucfirst(str_replace('_', ' ', $campana->estado)) }}</span>
            </div>
            <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div><p class="text-xs font-bold uppercase tracking-wide text-[#7a965f]">Presupuesto</p><p class="mt-1 font-semibold text-[#0f3c2b]">S/ {{ number_format((float) $campana->presupuesto, 2) }}</p></div>
                <div><p class="text-xs font-bold uppercase tracking-wide text-[#7a965f]">Periodo</p><p class="mt-1 font-semibold text-[#0f3c2b]">{{ $campana->fecha_inicio->format('d/m/Y') }} – {{ $campana->fecha_fin->format('d/m/Y') }}</p></div>
                <div><p class="text-xs font-bold uppercase tracking-wide text-[#7a965f]">Responsable</p><p class="mt-1 font-semibold text-[#0f3c2b]">{{ $campana->creador?->name ?? 'Sin responsable' }}</p></div>
                <div><p class="text-xs font-bold uppercase tracking-wide text-[#7a965f]">Evaluada</p><p class="mt-1 font-semibold text-[#0f3c2b]">{{ $campana->evaluada_en?->format('d/m/Y H:i') ?? 'Pendiente' }}</p></div>
            </div>
            <div class="mt-5 border-t border-[#e4ebdf] pt-4">
                <p class="text-xs font-bold uppercase tracking-wide text-[#7a965f]">Objetivos</p>
                <p class="mt-1 whitespace-pre-line text-[#1d352d]">{{ $campana->objetivos }}</p>
            </div>
        </section>

        <section class="rounded-[10px] border border-[#e4ebdf] bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold text-[#103928]">Etapas del macroproceso</h2>
            <div class="mt-4 grid gap-2 sm:grid-cols-2 xl:grid-cols-4">
                @foreach (App\Services\PlaneacionComercialService::ESTADOS as $estado)
                    <div class="rounded-[10px] border {{ $campana->estado === $estado ? 'border-[#7cb22b] bg-[#f4f9ea]' : 'border-[#e4ebdf] bg-[#f8faf6]' }} px-3 py-3 text-sm font-medium text-[#0f3c2b]">
                        {{ ucfirst(str_replace('_', ' ', $estado)) }}
                        @if ($campana->estado === $estado)<span class="ml-1 text-xs font-bold text-[#55801f]">Actual</span>@endif
                    </div>
                @endforeach
            </div>
        </section>

        <section class="rounded-[10px] border border-[#e4ebdf] bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-xl font-bold text-[#103928]">Análisis de demanda</h2>
                    <p class="mt-1 text-sm text-[#5f6d5f]">Consulta el ranking de ventas y el stock para el periodo de la campaña.</p>
                </div>
                <a href="{{ route('planeacion-comercial.demanda', ['inicio' => $campana->fecha_inicio->toDateString(), 'fin' => $campana->fecha_fin->toDateString()]) }}" class="rounded-full border border-[#dce6d7] px-4 py-2 text-sm font-semibold text-[#3b5b42]">Abrir panel de demanda</a>
            </div>
        </section>

        <section class="rounded-[10px] border border-[#e4ebdf] bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-xl font-bold text-[#103928]">Productos y disponibilidad</h2>
                    <p class="mt-1 text-sm text-[#5f6d5f]">Disponibilidad actual: {{ $veredicto['suficiente'] ? 'suficiente' : 'insuficiente' }}. El stock no queda reservado.</p>
                </div>
                @if (auth()->user()->rol === 'administrador' && $campana->estado === 'productos_identificados')
                    <form method="POST" action="{{ route('planeacion-comercial.campanas.transicionar', $campana) }}">
                        @csrf
                        <input type="hidden" name="estado" value="{{ $veredicto['suficiente'] ? 'oferta_disponible' : 'gestion_disponibilidad' }}">
                        <button type="submit" class="rounded-full bg-[#7cb22b] px-5 py-2.5 text-sm font-semibold text-white">{{ $veredicto['suficiente'] ? 'Confirmar disponibilidad' : 'Gestionar faltantes' }}</button>
                    </form>
                @endif
            </div>
            @if (count($veredicto['productos']) === 0)
                <p class="mt-4 text-sm text-[#5f6d5f]">La oferta no tiene productos activos.</p>
            @else
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full min-w-[700px] text-left text-sm text-[#304e42]">
                        <thead class="bg-[#edf8e7] text-[#3b5d2d]"><tr><th class="px-3 py-2">Planta</th><th class="px-3 py-2">Meta</th><th class="px-3 py-2">Stock actual</th><th class="px-3 py-2">Faltante</th><th class="px-3 py-2">Resultado</th></tr></thead>
                        <tbody>
                            @foreach ($veredicto['productos'] as $fila)
                                <tr class="border-t border-[#e4ebdf]">
                                    <td class="px-3 py-2">{{ $fila['planta_nombre'] }}</td>
                                    <td class="px-3 py-2">{{ $fila['requerido'] }}</td>
                                    <td class="px-3 py-2">{{ $fila['disponible'] }}</td>
                                    <td class="px-3 py-2">{{ $fila['faltante'] }}</td>
                                    <td class="px-3 py-2">{{ $fila['suficiente'] ? 'Suficiente' : 'Insuficiente' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        @if ($campana->estado === 'gestion_disponibilidad')
            <section class="rounded-[10px] border border-[#e4ebdf] bg-white p-6 shadow-sm">
                <h2 class="text-xl font-bold text-[#103928]">Solicitudes de disponibilidad</h2>
                <div class="mt-4 space-y-4">
                    @foreach ($campana->productos->where('incluida', true) as $producto)
                        @if ($producto->solicitudDisponibilidad)
                            <article class="rounded-[10px] border border-[#e4ebdf] bg-[#f8faf6] p-4">
                                <div class="flex flex-wrap justify-between gap-2">
                                    <h3 class="font-semibold text-[#0f3c2b]">{{ $producto->planta->nombre }}</h3>
                                    <span class="text-sm text-[#5f6d5f]">Faltante solicitado: {{ $producto->solicitudDisponibilidad->faltante }} · {{ ucfirst(str_replace('_', ' ', $producto->solicitudDisponibilidad->estado)) }}</span>
                                </div>
                                @if (auth()->user()->rol === 'administrador')
                                    <form method="POST" action="{{ route('planeacion-comercial.solicitudes.disponibilidad', $producto->solicitudDisponibilidad) }}" class="mt-3 grid gap-3 md:grid-cols-[1fr_auto_auto]">
                                        @csrf
                                        <input name="notas" type="text" maxlength="2000" value="{{ $producto->solicitudDisponibilidad->notas }}" placeholder="Nota de gestión (opcional)" class="rounded-xl border border-[#d8e2d7] bg-white px-3 py-2">
                                        <button type="submit" name="estado" value="atendida" class="rounded-full border border-[#dce6d7] px-4 py-2 text-sm font-semibold text-[#3b5b42]">Atendida</button>
                                        <button type="submit" name="estado" value="no_lograda" class="rounded-full bg-[#7cb22b] px-4 py-2 text-sm font-semibold text-white">No lograda</button>
                                    </form>
                                @elseif ($producto->solicitudDisponibilidad->notas)
                                    <p class="mt-2 text-sm text-[#5f6d5f]">{{ $producto->solicitudDisponibilidad->notas }}</p>
                                @endif
                            </article>
                        @endif
                    @endforeach
                </div>
                @if (auth()->user()->rol === 'administrador' && $puedeVolverARevision)
                    <form method="POST" action="{{ route('planeacion-comercial.campanas.transicionar', $campana) }}" class="mt-4">
                        @csrf
                        <input type="hidden" name="estado" value="productos_identificados">
                        <button type="submit" class="rounded-full bg-[#7cb22b] px-5 py-2.5 text-sm font-semibold text-white">Volver a revisar disponibilidad</button>
                    </form>
                @elseif (auth()->user()->rol === 'administrador' && $puedeAjustar)
                    <form method="POST" action="{{ route('planeacion-comercial.campanas.transicionar', $campana) }}" class="mt-4">
                        @csrf
                        <input type="hidden" name="estado" value="oferta_por_ajustar">
                        <button type="submit" class="rounded-full bg-[#7cb22b] px-5 py-2.5 text-sm font-semibold text-white">Solicitar ajuste de oferta</button>
                    </form>
                @endif
            </section>
        @endif

        <section class="rounded-[10px] border border-[#e4ebdf] bg-white p-6 shadow-sm">
            <h2 class="text-xl font-bold text-[#103928]">Acciones de la etapa actual</h2>
            <div class="mt-4 flex flex-wrap gap-3">
                @if (auth()->user()->rol === 'vendedor')
                    @if ($campana->estado === 'objetivos_definidos')
                        <form method="POST" action="{{ route('planeacion-comercial.campanas.transicionar', $campana) }}">@csrf<input type="hidden" name="estado" value="demanda_analizada"><button class="rounded-full bg-[#7cb22b] px-5 py-2.5 text-sm font-semibold text-white">Registrar análisis de demanda</button></form>
                    @elseif ($campana->estado === 'demanda_analizada')
                        <form method="POST" action="{{ route('planeacion-comercial.campanas.transicionar', $campana) }}">@csrf<input type="hidden" name="estado" value="productos_identificados"><button class="rounded-full bg-[#7cb22b] px-5 py-2.5 text-sm font-semibold text-white">Identificar productos</button></form>
                    @elseif (in_array($campana->estado, ['oferta_disponible', 'oferta_por_ajustar'], true) && $veredicto['suficiente'])
                        <form method="POST" action="{{ route('planeacion-comercial.campanas.transicionar', $campana) }}">@csrf<input type="hidden" name="estado" value="oferta_planificada"><button class="rounded-full bg-[#7cb22b] px-5 py-2.5 text-sm font-semibold text-white">Planificar oferta</button></form>
                    @elseif ($campana->estado === 'oferta_planificada')
                        <form method="POST" action="{{ route('planeacion-comercial.campanas.transicionar', $campana) }}">@csrf<input type="hidden" name="estado" value="promocion_preparada"><button class="rounded-full bg-[#7cb22b] px-5 py-2.5 text-sm font-semibold text-white">Preparar promoción</button></form>
                    @elseif ($campana->estado === 'promocion_preparada')
                        <form method="POST" action="{{ route('planeacion-comercial.campanas.transicionar', $campana) }}">@csrf<input type="hidden" name="estado" value="promocion_realizada"><button class="rounded-full bg-[#7cb22b] px-5 py-2.5 text-sm font-semibold text-white">Registrar feria o promoción realizada</button></form>
                    @endif

                    @if ($campana->estado === 'oferta_por_ajustar')
                        <form method="POST" action="{{ route('planeacion-comercial.campanas.oferta.ajustar', $campana) }}" class="mt-4 w-full rounded-[10px] border border-[#dce6d7] bg-[#f8faf6] p-4">
                            @csrf
                            <h3 class="font-semibold text-[#0f3c2b]">Reducir o descartar productos</h3>
                            <p class="mt-1 text-sm text-[#5f6d5f]">Quitar una fila descarta esa planta. La oferta debe volver a tener disponibilidad suficiente para planificarse.</p>
                            @include('planeacion-comercial.partials.productos-form', ['productosIniciales' => $campana->productos->where('incluida', true)->map(fn ($producto) => ['planta_id' => $producto->planta_id, 'cantidad_meta' => $producto->cantidad_meta, 'descuento_porcentaje' => $producto->descuento_porcentaje])->values()->all()])
                            <button type="submit" class="mt-4 rounded-full bg-[#7cb22b] px-5 py-2.5 text-sm font-semibold text-white">Guardar oferta ajustada</button>
                        </form>
                    @endif
                @elseif (auth()->user()->rol === 'administrador' && $campana->estado === 'promocion_realizada' && $fechaFinalizada)
                    <form method="POST" action="{{ route('planeacion-comercial.campanas.transicionar', $campana) }}">@csrf<input type="hidden" name="estado" value="resultados_evaluados"><button class="rounded-full bg-[#7cb22b] px-5 py-2.5 text-sm font-semibold text-white">Evaluar resultados</button></form>
                @elseif (auth()->user()->rol === 'administrador' && $campana->estado === 'resultados_evaluados')
                    <form method="POST" action="{{ route('planeacion-comercial.campanas.transicionar', $campana) }}" class="w-full">
                        @csrf
                        <input type="hidden" name="estado" value="cerrada">
                        <label for="ajustes_siguiente_ciclo" class="mb-2 block text-sm font-semibold text-[#0f3c2b]">Ajustes documentados para el siguiente ciclo</label>
                        <textarea id="ajustes_siguiente_ciclo" name="ajustes_siguiente_ciclo" rows="3" maxlength="5000" required class="w-full rounded-2xl border border-[#d8e2d7] bg-[#f8faf6] px-3 py-2.5">{{ old('ajustes_siguiente_ciclo', $campana->ajustes_siguiente_ciclo) }}</textarea>
                        <button type="submit" class="mt-3 rounded-full bg-[#7cb22b] px-5 py-2.5 text-sm font-semibold text-white">Documentar y cerrar ciclo</button>
                    </form>
                @endif
            </div>
            @if (auth()->user()->rol === 'administrador' && $campana->estado === 'promocion_realizada' && !$fechaFinalizada)
                <p class="mt-3 text-sm text-[#5f6d5f]">La evaluación estará disponible después del {{ $campana->fecha_fin->format('d/m/Y') }}.</p>
            @endif
        </section>

        @if ($campana->evaluada_en)
            <section class="rounded-[10px] border border-[#e4ebdf] bg-white p-6 shadow-sm">
                <h2 class="text-xl font-bold text-[#103928]">Resultados de evaluación</h2>
                <p class="mt-1 text-sm text-[#5f6d5f]">Fotografía calculada el {{ $campana->evaluada_en->format('d/m/Y H:i') }}. Los ingresos son aproximados con el precio actual de catálogo.</p>
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full min-w-[700px] text-left text-sm text-[#304e42]">
                        <thead class="bg-[#edf8e7] text-[#3b5d2d]"><tr><th class="px-3 py-2">Planta</th><th class="px-3 py-2">Meta</th><th class="px-3 py-2">Vendidas</th><th class="px-3 py-2">Cumplimiento</th><th class="px-3 py-2">Ingreso estimado</th></tr></thead>
                        <tbody>
                            @foreach ($campana->productos->where('incluida', true) as $producto)
                                <tr class="border-t border-[#e4ebdf]"><td class="px-3 py-2">{{ $producto->planta->nombre }}</td><td class="px-3 py-2">{{ $producto->cantidad_meta }}</td><td class="px-3 py-2">{{ $producto->cantidad_vendida_evaluada }}</td><td class="px-3 py-2">{{ $producto->porcentaje_cumplimiento }}%</td><td class="px-3 py-2">S/ {{ number_format((float) $producto->ingresos_estimados, 2) }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($campana->ajustes_siguiente_ciclo)
                    <p class="mt-4 whitespace-pre-line text-sm text-[#1d352d]"><strong>Ajustes del siguiente ciclo:</strong> {{ $campana->ajustes_siguiente_ciclo }}</p>
                @endif
            </section>
        @endif

        <p class="text-xs text-[#68766b]">Las ventas se atribuyen por planta y periodo porque el registro actual no guarda campaña ni precio cobrado; no se puede confirmar si se aplicó el descuento planificado.</p>
    </main>
</body>
</html>
