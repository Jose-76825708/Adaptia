@php
    use App\Services\GestionInventarioService as Gestion;

    $num = fn ($valor) => $valor === null ? '—' : rtrim(rtrim(number_format((float) $valor, 2, '.', ''), '0'), '.');
    $etiqueta = fn (string $texto) => ucfirst(str_replace('_', ' ', $texto));
    $unidad = $gestion->unidad();
    $carril = Gestion::CARRIL_POR_ESTADO[$gestion->estado] ?? null;
    $rolesCarril = $carril ? implode(' o ', Gestion::ROLES_POR_CARRIL[$carril]) : null;
    $nombresCarril = ['almacen' => 'Almacén', 'compras' => 'Compras y administración'];
    $estadoActual = $gestion->estado;
    $recepcionPendiente = $gestion->recepciones->firstWhere('conforme', null);
    $boton = 'rounded-full bg-[#7cb22b] px-5 py-2.5 text-sm font-semibold text-white';
    $campo = 'w-full rounded-2xl border border-[#d8e2d7] bg-[#f8faf6] px-3 py-2.5';
    $etiquetaCampo = 'mb-2 block text-sm font-semibold text-[#0f3c2b]';
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    @vite('resources/css/app.css')
    <title>{{ $gestion->codigo }} · Gestión de inventario</title>
</head>
<body class="flex h-screen font-sans">
    @include('partials.sidebar')

    <main class="flex flex-5 flex-col gap-5 overflow-y-auto bg-[#fbfbfb] p-8">
        <section class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#7a965f]">Gestión de inventario / {{ $gestion->codigo }}</p>
                <h1 class="text-[2.5em] font-bold text-[#103928]">Detalle de la gestión</h1>
            </div>
            <a href="{{ route('gestion-inventario.index') }}" class="rounded-full border border-[#dce6d7] bg-white px-5 py-2.5 text-sm font-semibold text-[#3b5b42]">Volver a gestiones</a>
        </section>

        @if (session('success'))
            <div class="rounded-[10px] border border-[#cfe0c2] bg-[#edf8e7] p-4 text-sm font-semibold text-[#3b5d2d]" role="status">{{ session('success') }}</div>
        @endif
        @if (session('stock_warning'))
            <div class="rounded-[10px] border border-amber-200 bg-amber-50 p-4 text-sm font-semibold text-amber-800" role="status">{{ session('stock_warning') }}</div>
        @endif
        @if ($errors->any())
            <div class="rounded-[10px] border border-red-200 bg-red-50 p-4 text-sm text-red-700" role="alert">
                @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
            </div>
        @endif

        <section class="rounded-[10px] border border-[#e4ebdf] bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#7a965f]">{{ $gestion->tipo_item === 'planta' ? 'Planta' : 'Insumo' }}</p>
                    <h2 class="mt-1 text-2xl font-bold text-[#0f3c2b]">{{ $gestion->nombreItem() }}</h2>
                </div>
            </div>
            <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div><p class="text-xs font-bold uppercase tracking-wide text-[#7a965f]">Cantidad requerida</p><p class="mt-1 font-semibold text-[#0f3c2b]">{{ $num($gestion->cantidad_requerida) }} {{ $unidad }}</p></div>
                <div><p class="text-xs font-bold uppercase tracking-wide text-[#7a965f]">Stock en sistema (última revisión)</p><p class="mt-1 font-semibold text-[#0f3c2b]">{{ $num($gestion->stock_sistema) }} {{ $unidad }}</p></div>
                <div><p class="text-xs font-bold uppercase tracking-wide text-[#7a965f]">Responsable</p><p class="mt-1 font-semibold text-[#0f3c2b]">{{ $gestion->creador?->name ?? 'Sin responsable' }}</p></div>
                <div><p class="text-xs font-bold uppercase tracking-wide text-[#7a965f]">Cierre</p><p class="mt-1 font-semibold text-[#0f3c2b]">{{ $gestion->cerrada_en?->format('d/m/Y H:i') ?? 'Pendiente' }}</p></div>
            </div>
            @if ($gestion->motivo)
                <div class="mt-5 border-t border-[#e4ebdf] pt-4">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#7a965f]">Motivo</p>
                    <p class="mt-1 whitespace-pre-line text-[#1d352d]">{{ $gestion->motivo }}</p>
                </div>
            @endif
        </section>

        <section class="rounded-[10px] border border-[#e4ebdf] bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold text-[#103928]">Estado del proceso</h2>
            <p class="mt-2 text-[#1d352d]"><span class="font-semibold">{{ $etiqueta($estadoActual) }}.</span> {{ Gestion::MENSAJES_ESTADO[$estadoActual] ?? '' }}</p>
        </section>

        @if ($gestion->cantidad_fisica !== null)
            <section class="rounded-[10px] border border-[#e4ebdf] bg-white p-6 shadow-sm">
                <h2 class="text-xl font-bold text-[#103928]">Verificación de estado y cantidades</h2>
                <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div><p class="text-xs font-bold uppercase tracking-wide text-[#7a965f]">Conteo físico</p><p class="mt-1 font-semibold text-[#0f3c2b]">{{ $num($gestion->cantidad_fisica) }} {{ $unidad }}</p></div>
                    <div><p class="text-xs font-bold uppercase tracking-wide text-[#7a965f]">En buen estado</p><p class="mt-1 font-semibold text-[#0f3c2b]">{{ $num($gestion->cantidad_buen_estado) }} {{ $unidad }}</p></div>
                    <div><p class="text-xs font-bold uppercase tracking-wide text-[#7a965f]">Disponible</p><p class="mt-1 font-semibold text-[#0f3c2b]">{{ $num($gestion->cantidad_disponible) }} {{ $unidad }}</p></div>
                    <div><p class="text-xs font-bold uppercase tracking-wide text-[#7a965f]">Faltante</p><p class="mt-1 font-semibold text-[#0f3c2b]">{{ $num($gestion->faltante) }} {{ $unidad }}</p></div>
                </div>
                <p class="mt-3 text-sm text-[#5f6d5f]">Se considera disponible lo que está en buen estado y además figura en el sistema. Resultado: stock {{ (float) $gestion->faltante <= 0 ? 'suficiente' : 'insuficiente' }}.</p>
                @if ($gestion->observaciones_verificacion)
                    <p class="mt-2 whitespace-pre-line text-sm text-[#1d352d]">{{ $gestion->observaciones_verificacion }}</p>
                @endif
            </section>
        @endif

        @if ($gestion->cantidad_reposicion !== null)
            <section class="rounded-[10px] border border-[#e4ebdf] bg-white p-6 shadow-sm">
                <h2 class="text-xl font-bold text-[#103928]">Reposición y compra</h2>
                <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div><p class="text-xs font-bold uppercase tracking-wide text-[#7a965f]">Cantidad a reponer</p><p class="mt-1 font-semibold text-[#0f3c2b]">{{ $num($gestion->cantidad_reposicion) }} {{ $unidad }}</p></div>
                    <div><p class="text-xs font-bold uppercase tracking-wide text-[#7a965f]">Proveedor</p><p class="mt-1 font-semibold text-[#0f3c2b]">{{ $gestion->proveedor }}</p></div>
                    <div><p class="text-xs font-bold uppercase tracking-wide text-[#7a965f]">Costo estimado</p><p class="mt-1 font-semibold text-[#0f3c2b]">{{ $gestion->costo_estimado !== null ? 'S/ ' . number_format((float) $gestion->costo_estimado, 2) : '—' }}</p></div>
                    <div><p class="text-xs font-bold uppercase tracking-wide text-[#7a965f]">Orden de compra</p><p class="mt-1 font-semibold text-[#0f3c2b]">{{ $gestion->numero_orden ?? 'Pendiente' }}@if ($gestion->fecha_compra) · {{ $gestion->fecha_compra->format('d/m/Y') }}@endif</p></div>
                </div>
                @if ($gestion->costo_total !== null)
                    <p class="mt-3 text-sm text-[#5f6d5f]">Costo total de la compra: S/ {{ number_format((float) $gestion->costo_total, 2) }}</p>
                @endif
            </section>
        @endif

        <section class="rounded-[10px] border border-[#e4ebdf] bg-white p-6 shadow-sm">
            <h2 class="text-xl font-bold text-[#103928]">Siguiente paso</h2>

            @if ($estadoActual === 'cerrada')
                <p class="mt-3 text-sm text-[#5f6d5f]">La gestión está cerrada ({{ $gestion->resultado === 'stock_suficiente' ? 'el stock era suficiente y se registró la salida' : 'la reposición se recibió, almacenó y registró como entrada' }}).</p>
            @elseif (!$puedeActuar)
                <p class="mt-3 text-sm text-[#5f6d5f]">Este paso le corresponde a «{{ $nombresCarril[$carril] ?? '' }}» (rol: {{ $rolesCarril }}).</p>
            @else
                <div class="mt-4">
                    @if ($estadoActual === 'existencias_revisadas')
                        <form method="POST" action="{{ route('gestion-inventario.verificar', $gestion) }}" class="grid gap-4 md:grid-cols-2">
                            @csrf
                            <div>
                                <label for="cantidad_fisica" class="{{ $etiquetaCampo }}">Cantidad contada físicamente ({{ $unidad }})</label>
                                <input id="cantidad_fisica" name="cantidad_fisica" type="number" min="0" step="0.01" value="{{ old('cantidad_fisica') }}" required class="{{ $campo }}">
                            </div>
                            <div>
                                <label for="cantidad_buen_estado" class="{{ $etiquetaCampo }}">Cantidad en buen estado ({{ $unidad }})</label>
                                <input id="cantidad_buen_estado" name="cantidad_buen_estado" type="number" min="0" step="0.01" value="{{ old('cantidad_buen_estado') }}" required class="{{ $campo }}">
                            </div>
                            <div class="md:col-span-2">
                                <label for="observaciones_verificacion" class="{{ $etiquetaCampo }}">Observaciones del estado (opcional)</label>
                                <textarea id="observaciones_verificacion" name="observaciones_verificacion" rows="2" maxlength="2000" class="{{ $campo }}">{{ old('observaciones_verificacion') }}</textarea>
                            </div>
                            <div class="md:col-span-2"><button type="submit" class="{{ $boton }}">Verificar estado y cantidades</button></div>
                        </form>
                    @elseif ($estadoActual === 'stock_suficiente')
                        <p class="mb-3 text-sm text-[#5f6d5f]">El stock cubre lo requerido. Registra la salida de {{ $num($gestion->cantidad_requerida) }} {{ $unidad }} para actualizar las existencias.</p>
                        <form method="POST" action="{{ route('gestion-inventario.movimiento', $gestion) }}">@csrf<button type="submit" class="{{ $boton }}">Registrar movimiento de salida</button></form>
                    @elseif ($estadoActual === 'stock_insuficiente')
                        <p class="mb-3 text-sm text-[#5f6d5f]">El stock no alcanza. Faltan {{ $num($gestion->faltante) }} {{ $unidad }}; gestiona la reposición.</p>
                        <form method="POST" action="{{ route('gestion-inventario.reposicion', $gestion) }}" class="grid gap-4 md:grid-cols-3">
                            @csrf
                            <div>
                                <label for="cantidad_reposicion" class="{{ $etiquetaCampo }}">Cantidad a reponer ({{ $unidad }})</label>
                                <input id="cantidad_reposicion" name="cantidad_reposicion" type="number" min="0.01" step="0.01" value="{{ old('cantidad_reposicion', $num($gestion->faltante)) }}" required class="{{ $campo }}">
                            </div>
                            <div>
                                <label for="proveedor" class="{{ $etiquetaCampo }}">Proveedor</label>
                                <input id="proveedor" name="proveedor" type="text" maxlength="150" value="{{ old('proveedor') }}" required class="{{ $campo }}">
                            </div>
                            <div>
                                <label for="costo_estimado" class="{{ $etiquetaCampo }}">Costo estimado (S/, opcional)</label>
                                <input id="costo_estimado" name="costo_estimado" type="number" min="0" step="0.01" value="{{ old('costo_estimado') }}" class="{{ $campo }}">
                            </div>
                            <div class="md:col-span-3"><button type="submit" class="{{ $boton }}">Gestionar reposición</button></div>
                        </form>
                    @elseif ($estadoActual === 'reposicion_gestionada')
                        <form method="POST" action="{{ route('gestion-inventario.compra', $gestion) }}" class="grid gap-4 md:grid-cols-3">
                            @csrf
                            <div>
                                <label for="numero_orden" class="{{ $etiquetaCampo }}">N.º de orden de compra</label>
                                <input id="numero_orden" name="numero_orden" type="text" maxlength="60" value="{{ old('numero_orden') }}" required class="{{ $campo }}">
                            </div>
                            <div>
                                <label for="fecha_compra" class="{{ $etiquetaCampo }}">Fecha de compra</label>
                                <input id="fecha_compra" name="fecha_compra" type="date" max="{{ today()->toDateString() }}" value="{{ old('fecha_compra', today()->toDateString()) }}" required class="{{ $campo }}">
                            </div>
                            <div>
                                <label for="costo_total" class="{{ $etiquetaCampo }}">Costo total (S/, opcional)</label>
                                <input id="costo_total" name="costo_total" type="number" min="0" step="0.01" value="{{ old('costo_total') }}" class="{{ $campo }}">
                            </div>
                            <div class="md:col-span-3"><button type="submit" class="{{ $boton }}">Registrar compra o abastecimiento</button></div>
                        </form>
                    @elseif (in_array($estadoActual, ['compra_realizada', 'incidencia_gestionada'], true))
                        @php
                            $aceptadaHastaAhora = (float) $gestion->recepciones->whereNotNull('conforme')->sum('cantidad_aceptada');
                            $pendiente = max(0, (float) $gestion->cantidad_reposicion - $aceptadaHastaAhora);
                        @endphp
                        <p class="mb-3 text-sm text-[#5f6d5f]">Solicitado: {{ $num($gestion->cantidad_reposicion) }} {{ $unidad }} · Aceptado hasta ahora: {{ $num($aceptadaHastaAhora) }} · Pendiente de recibir: {{ $num($pendiente) }}.</p>
                        <form method="POST" action="{{ route('gestion-inventario.recepcion', $gestion) }}" class="grid gap-4 md:grid-cols-2">
                            @csrf
                            <div>
                                <label for="cantidad_recibida" class="{{ $etiquetaCampo }}">Cantidad recibida ({{ $unidad }})</label>
                                <input id="cantidad_recibida" name="cantidad_recibida" type="number" min="0.01" step="0.01" value="{{ old('cantidad_recibida', $num($pendiente)) }}" required class="{{ $campo }}">
                            </div>
                            <div>
                                <label for="observaciones" class="{{ $etiquetaCampo }}">Observaciones (opcional)</label>
                                <input id="observaciones" name="observaciones" type="text" maxlength="2000" value="{{ old('observaciones') }}" class="{{ $campo }}">
                            </div>
                            <div class="md:col-span-2"><button type="submit" class="{{ $boton }}">Registrar recepción</button></div>
                        </form>
                    @elseif ($estadoActual === 'material_recibido')
                        <p class="mb-3 text-sm text-[#5f6d5f]">Recepción por verificar: {{ $num($recepcionPendiente?->cantidad_recibida) }} {{ $unidad }}. Compárala con lo solicitado ({{ $num($gestion->cantidad_reposicion) }} {{ $unidad }}).</p>
                        <form method="POST" action="{{ route('gestion-inventario.recepcion.verificar', $gestion) }}" class="grid gap-4 md:grid-cols-2">
                            @csrf
                            <fieldset class="md:col-span-2">
                                <legend class="{{ $etiquetaCampo }}">¿La recepción es conforme?</legend>
                                <label class="mr-5 inline-flex items-center gap-2 text-sm"><input type="radio" name="conforme" value="1" @checked(old('conforme', '1') === '1') required> Sí, conforme</label>
                                <label class="inline-flex items-center gap-2 text-sm"><input type="radio" name="conforme" value="0" @checked(old('conforme') === '0')> No, hay diferencia o incidencia</label>
                            </fieldset>
                            <div>
                                <label for="tipo_incidencia" class="{{ $etiquetaCampo }}">Tipo de diferencia (solo si no es conforme)</label>
                                <select id="tipo_incidencia" name="tipo_incidencia" class="{{ $campo }}">
                                    <option value="">Selecciona</option>
                                    @foreach ($tiposIncidencia as $tipo)
                                        <option value="{{ $tipo }}" @selected(old('tipo_incidencia') === $tipo)>{{ ['faltante' => 'Faltante', 'exceso' => 'Exceso', 'danado' => 'Dañado', 'incorrecto' => 'Producto incorrecto'][$tipo] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="cantidad_afectada" class="{{ $etiquetaCampo }}">Unidades afectadas (solo dañado o incorrecto)</label>
                                <input id="cantidad_afectada" name="cantidad_afectada" type="number" min="0.01" step="0.01" value="{{ old('cantidad_afectada') }}" class="{{ $campo }}">
                            </div>
                            <div class="md:col-span-2">
                                <label for="descripcion" class="{{ $etiquetaCampo }}">Descripción de la diferencia (solo si no es conforme)</label>
                                <textarea id="descripcion" name="descripcion" rows="2" maxlength="2000" class="{{ $campo }}">{{ old('descripcion') }}</textarea>
                            </div>
                            <div class="md:col-span-2"><button type="submit" class="{{ $boton }}">Verificar recepción</button></div>
                        </form>
                    @elseif ($estadoActual === 'en_incidencia')
                        @foreach ($gestion->incidencias->where('estado', 'abierta') as $incidencia)
                            <p class="mb-2 text-sm text-[#5f6d5f]">Incidencia abierta: {{ $etiqueta($incidencia->tipo) }} ({{ $num($incidencia->cantidad_afectada) }} {{ $unidad }}) — {{ $incidencia->descripcion }}</p>
                        @endforeach
                        <form method="POST" action="{{ route('gestion-inventario.incidencia', $gestion) }}" class="mt-3">
                            @csrf
                            <label for="resolucion" class="{{ $etiquetaCampo }}">Gestión realizada con el proveedor (reposición, devolución, nota de crédito…)</label>
                            <textarea id="resolucion" name="resolucion" rows="3" maxlength="2000" required class="{{ $campo }}">{{ old('resolucion') }}</textarea>
                            <button type="submit" class="mt-3 {{ $boton }}">Gestionar diferencia o incidencia</button>
                        </form>
                    @elseif ($estadoActual === 'recepcion_conforme')
                        <form method="POST" action="{{ route('gestion-inventario.ingreso', $gestion) }}" class="grid gap-4 md:grid-cols-2">
                            @csrf
                            <div>
                                <label for="documento_ingreso" class="{{ $etiquetaCampo }}">Documento de ingreso (guía o factura, opcional)</label>
                                <input id="documento_ingreso" name="documento_ingreso" type="text" maxlength="100" value="{{ old('documento_ingreso') }}" class="{{ $campo }}">
                            </div>
                            <div class="flex items-end"><button type="submit" class="{{ $boton }}">Registrar ingreso</button></div>
                        </form>
                    @elseif ($estadoActual === 'ingreso_registrado')
                        <form method="POST" action="{{ route('gestion-inventario.almacenar', $gestion) }}" class="grid gap-4 md:grid-cols-2">
                            @csrf
                            <div>
                                <label for="ubicacion" class="{{ $etiquetaCampo }}">Ubicación de almacenamiento (zona, estante o mesa)</label>
                                <input id="ubicacion" name="ubicacion" type="text" maxlength="120" value="{{ old('ubicacion') }}" required class="{{ $campo }}">
                            </div>
                            <div class="flex items-end"><button type="submit" class="{{ $boton }}">Almacenar y organizar</button></div>
                        </form>
                    @elseif ($estadoActual === 'almacenado')
                        <p class="mb-3 text-sm text-[#5f6d5f]">Se registrará la entrada de {{ $num($gestion->cantidad_ingresada) }} {{ $unidad }} y se actualizarán las existencias.</p>
                        <form method="POST" action="{{ route('gestion-inventario.movimiento', $gestion) }}">@csrf<button type="submit" class="{{ $boton }}">Registrar movimiento y actualizar existencias</button></form>
                    @endif
                </div>
            @endif
        </section>

        @if ($gestion->recepciones->isNotEmpty())
            <section class="rounded-[10px] border border-[#e4ebdf] bg-white p-6 shadow-sm">
                <h2 class="text-xl font-bold text-[#103928]">Recepciones</h2>
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full min-w-[700px] text-left text-sm text-[#304e42]">
                        <thead class="bg-[#edf8e7] text-[#3b5d2d]"><tr><th class="px-3 py-2">Fecha</th><th class="px-3 py-2">Recibido</th><th class="px-3 py-2">Aceptado</th><th class="px-3 py-2">Resultado</th><th class="px-3 py-2">Observaciones</th></tr></thead>
                        <tbody>
                            @foreach ($gestion->recepciones as $recepcion)
                                <tr class="border-t border-[#e4ebdf]">
                                    <td class="px-3 py-2">{{ $recepcion->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-3 py-2">{{ $num($recepcion->cantidad_recibida) }}</td>
                                    <td class="px-3 py-2">{{ $num($recepcion->cantidad_aceptada) }}</td>
                                    <td class="px-3 py-2">{{ $recepcion->conforme === null ? 'Por verificar' : ($recepcion->conforme ? 'Conforme' : 'No conforme') }}</td>
                                    <td class="px-3 py-2">{{ $recepcion->observaciones ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        @if ($gestion->incidencias->isNotEmpty())
            <section class="rounded-[10px] border border-[#e4ebdf] bg-white p-6 shadow-sm">
                <h2 class="text-xl font-bold text-[#103928]">Diferencias e incidencias</h2>
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full min-w-[700px] text-left text-sm text-[#304e42]">
                        <thead class="bg-[#edf8e7] text-[#3b5d2d]"><tr><th class="px-3 py-2">Tipo</th><th class="px-3 py-2">Unidades</th><th class="px-3 py-2">Descripción</th><th class="px-3 py-2">Estado</th><th class="px-3 py-2">Resolución</th></tr></thead>
                        <tbody>
                            @foreach ($gestion->incidencias as $incidencia)
                                <tr class="border-t border-[#e4ebdf]">
                                    <td class="px-3 py-2">{{ $etiqueta($incidencia->tipo) }}</td>
                                    <td class="px-3 py-2">{{ $num($incidencia->cantidad_afectada) }}</td>
                                    <td class="px-3 py-2">{{ $incidencia->descripcion }}</td>
                                    <td class="px-3 py-2">{{ $etiqueta($incidencia->estado) }}</td>
                                    <td class="px-3 py-2">{{ $incidencia->resolucion ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        @if ($gestion->ubicacion || $gestion->movimientos->isNotEmpty())
            <section class="rounded-[10px] border border-[#e4ebdf] bg-white p-6 shadow-sm">
                <h2 class="text-xl font-bold text-[#103928]">Ingreso, almacenamiento y movimientos</h2>
                @if ($gestion->cantidad_ingresada !== null)
                    <p class="mt-3 text-sm text-[#1d352d]">Ingreso registrado: {{ $num($gestion->cantidad_ingresada) }} {{ $unidad }}@if ($gestion->documento_ingreso) · Documento: {{ $gestion->documento_ingreso }}@endif</p>
                @endif
                @if ($gestion->ubicacion)
                    <p class="mt-1 text-sm text-[#1d352d]">Ubicación: {{ $gestion->ubicacion }}</p>
                @endif
                @if ($gestion->movimientos->isNotEmpty())
                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full min-w-[700px] text-left text-sm text-[#304e42]">
                            <thead class="bg-[#edf8e7] text-[#3b5d2d]"><tr><th class="px-3 py-2">Fecha</th><th class="px-3 py-2">Tipo</th><th class="px-3 py-2">Cantidad</th><th class="px-3 py-2">Stock antes</th><th class="px-3 py-2">Stock después</th><th class="px-3 py-2">Motivo</th></tr></thead>
                            <tbody>
                                @foreach ($gestion->movimientos as $movimiento)
                                    <tr class="border-t border-[#e4ebdf]">
                                        <td class="px-3 py-2">{{ $movimiento->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="px-3 py-2">{{ $etiqueta($movimiento->tipo) }}</td>
                                        <td class="px-3 py-2">{{ $num($movimiento->cantidad) }}</td>
                                        <td class="px-3 py-2">{{ $num($movimiento->stock_antes) }}</td>
                                        <td class="px-3 py-2">{{ $num($movimiento->stock_despues) }}</td>
                                        <td class="px-3 py-2">{{ $movimiento->motivo }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        @endif
    </main>
</body>
</html>
