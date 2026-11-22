@php
    $campo = 'w-full rounded-2xl border border-[#d8e2d7] bg-[#f8faf6] px-3 py-2.5';
    $etiqueta = 'mb-2 block text-sm font-semibold text-[#0f3c2b]';
    $boton = 'rounded-full bg-[#7cb22b] px-6 py-3 text-sm font-semibold text-white';
    $nombreEstado = fn (string $valor) => ucfirst(str_replace('_', ' ', $valor));

    $estados = App\Services\ProduccionVegetalService::ESTADOS;
    $paso = array_search($lote->estado, $estados, true) + 1;
    $total = count($estados);
    $porcentaje = (int) round($paso / $total * 100);

    $fases = [
        'Planificar' => ['necesidad_identificada', 'especie_seleccionada', 'material_seleccionado'],
        'Producir' => ['sustrato_preparado', 'propagacion_realizada', 'plantas_colocadas'],
        'Cuidar y revisar' => ['en_cuidados', 'en_revision'],
        'Cerrar' => ['clasificada', 'cerrado'],
    ];
    $faseActual = collect($fases)->search(fn ($lista) => in_array($lote->estado, $lista, true));
    $indiceFaseActual = array_search($faseActual, array_keys($fases), true);

    $guia = [
        'necesidad_identificada' => ['Elige qué planta vas a producir', 'Selecciona la especie para este lote.'],
        'especie_seleccionada' => ['Elige cómo la vas a reproducir', 'Indica el método y cuánto material vas a usar.'],
        'material_seleccionado' => ['Describe el sustrato', 'Anota la mezcla de tierra que preparaste.'],
        'sustrato_preparado' => ['Registra la propagación', 'Indica en qué fecha la hiciste.'],
        'propagacion_realizada' => ['Registra las plantas colocadas', 'Indica cuántas pusiste en el sustrato.'],
        'plantas_colocadas' => ['Empieza los cuidados', 'Desde aquí vas a anotar riego, luz y otros cuidados.'],
        'en_cuidados' => ['Anota los cuidados y pide revisión', 'Registra al menos un cuidado y luego envía el lote a revisión.'],
        'en_revision' => ['Revisa si las plantas están aptas', 'Si no están aptas, el lote vuelve a cuidados.'],
        'clasificada' => ['Envía las plantas al inventario', 'Solo las categorías A y B pasan a inventario.'],
        'cerrado' => ['Lote terminado', 'Este lote ya completó todo el proceso.'],
    ];
    [$tituloPaso, $ayudaPaso] = $guia[$lote->estado];
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    @vite('resources/css/app.css')
    <title>{{ $lote->codigo }} · Producción vegetal</title>
</head>
<body class="flex h-screen font-sans">
    @include('partials.sidebar')

    <main class="flex flex-5 flex-col gap-5 overflow-y-auto bg-[#fbfbfb] p-8">
        <section class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#7a965f]">Producción vegetal</p>
                <h1 class="text-[2.2em] font-bold text-[#103928]">{{ $lote->codigo }}</h1>
                <p class="text-[#68766b]">{{ $lote->planta?->nombre ?? 'Especie por elegir' }} · Meta: {{ $lote->cantidad_meta }} plantas</p>
            </div>
            <a href="{{ route('produccion-vegetal.lotes.index') }}" class="rounded-full border border-[#dce6d7] bg-white px-5 py-2.5 text-sm font-semibold text-[#3b5b42]">Volver a lotes</a>
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
            <div class="flex flex-wrap items-center justify-between gap-2">
                <p class="text-sm font-semibold text-[#0f3c2b]">Paso {{ $paso }} de {{ $total }}</p>
                <span class="rounded-full bg-[#edf8e7] px-3 py-1 text-sm font-semibold text-[#3b5d2d]">{{ $nombreEstado($lote->estado) }}</span>
            </div>
            <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-[#e4ebdf]">
                <div class="h-full rounded-full bg-[#7cb22b]" style="width: {{ $porcentaje }}%"></div>
            </div>
            <div class="mt-4 grid gap-2 sm:grid-cols-4">
                @foreach (array_keys($fases) as $i => $nombreFase)
                    <div class="rounded-[10px] px-3 py-2 text-center text-sm font-medium
                        {{ $i < $indiceFaseActual ? 'bg-[#edf8e7] text-[#3b5d2d]' : ($i === $indiceFaseActual ? 'border border-[#7cb22b] bg-[#f4f9ea] text-[#0f3c2b]' : 'bg-[#f8faf6] text-[#8a968a]') }}">
                        {{ $i < $indiceFaseActual ? '✓ ' : '' }}{{ $nombreFase }}
                    </div>
                @endforeach
            </div>
        </section>

        <section class="rounded-[10px] border border-[#7cb22b] bg-white p-6 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-[#55801f]">Qué toca ahora</p>
            <h2 class="mt-1 text-2xl font-bold text-[#103928]">{{ $tituloPaso }}</h2>
            <p class="mt-1 text-sm text-[#5f6d5f]">{{ $ayudaPaso }}</p>

            @switch($lote->estado)
                @case('necesidad_identificada')
                    <form method="POST" action="{{ route('produccion-vegetal.lotes.avanzar', $lote) }}" class="mt-5 space-y-4">
                        @csrf
                        <input type="hidden" name="destino" value="especie_seleccionada">
                        <div>
                            <label for="planta_id" class="{{ $etiqueta }}">Especie de planta</label>
                            <select id="planta_id" name="planta_id" required class="{{ $campo }}">
                                <option value="">Selecciona una especie</option>
                                @foreach ($plantas as $planta)
                                    <option value="{{ $planta->id }}">{{ $planta->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="{{ $boton }}">Seleccionar especie</button>
                    </form>
                    @break

                @case('especie_seleccionada')
                    <form method="POST" action="{{ route('produccion-vegetal.lotes.avanzar', $lote) }}" class="mt-5 grid gap-4 md:grid-cols-2">
                        @csrf
                        <input type="hidden" name="destino" value="material_seleccionado">
                        <div>
                            <label for="metodo_propagacion" class="{{ $etiqueta }}">Método de propagación</label>
                            <select id="metodo_propagacion" name="metodo_propagacion" required class="{{ $campo }}">
                                @foreach ($metodos as $metodo)
                                    <option value="{{ $metodo }}">{{ $nombreEstado($metodo) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="cantidad_material" class="{{ $etiqueta }}">Cantidad de material</label>
                            <input id="cantidad_material" name="cantidad_material" type="number" min="1" required class="{{ $campo }}">
                        </div>
                        <div class="md:col-span-2"><button type="submit" class="{{ $boton }}">Seleccionar material</button></div>
                    </form>
                    @break

                @case('material_seleccionado')
                    <form method="POST" action="{{ route('produccion-vegetal.lotes.avanzar', $lote) }}" class="mt-5 space-y-4">
                        @csrf
                        <input type="hidden" name="destino" value="sustrato_preparado">
                        <div>
                            <label for="sustrato_mezcla" class="{{ $etiqueta }}">Mezcla de sustrato</label>
                            <textarea id="sustrato_mezcla" name="sustrato_mezcla" rows="3" maxlength="1000" required class="{{ $campo }}" placeholder="Ej.: tierra negra, arena y humus"></textarea>
                        </div>
                        <button type="submit" class="{{ $boton }}">Registrar sustrato</button>
                    </form>
                    @break

                @case('sustrato_preparado')
                    <form method="POST" action="{{ route('produccion-vegetal.lotes.avanzar', $lote) }}" class="mt-5 space-y-4">
                        @csrf
                        <input type="hidden" name="destino" value="propagacion_realizada">
                        <div>
                            <label for="fecha_propagacion" class="{{ $etiqueta }}">Fecha de propagación</label>
                            <input id="fecha_propagacion" name="fecha_propagacion" type="date" max="{{ today()->toDateString() }}" required class="{{ $campo }}">
                        </div>
                        <button type="submit" class="{{ $boton }}">Registrar propagación</button>
                    </form>
                    @break

                @case('propagacion_realizada')
                    <form method="POST" action="{{ route('produccion-vegetal.lotes.avanzar', $lote) }}" class="mt-5 space-y-4">
                        @csrf
                        <input type="hidden" name="destino" value="plantas_colocadas">
                        <div>
                            <label for="cantidad_sembrada" class="{{ $etiqueta }}">Plantas colocadas</label>
                            <input id="cantidad_sembrada" name="cantidad_sembrada" type="number" min="1" required class="{{ $campo }}">
                        </div>
                        <button type="submit" class="{{ $boton }}">Registrar plantas colocadas</button>
                    </form>
                    @break

                @case('plantas_colocadas')
                    <form method="POST" action="{{ route('produccion-vegetal.lotes.avanzar', $lote) }}" class="mt-5">
                        @csrf
                        <input type="hidden" name="destino" value="en_cuidados">
                        <button type="submit" class="{{ $boton }}">Iniciar cuidados</button>
                    </form>
                    @break

                @case('en_cuidados')
                    <form method="POST" action="{{ route('produccion-vegetal.lotes.cuidados', $lote) }}" class="mt-5 grid gap-4 md:grid-cols-3">
                        @csrf
                        <div>
                            <label for="tipo" class="{{ $etiqueta }}">Cuidado</label>
                            <select id="tipo" name="tipo" required class="{{ $campo }}">
                                @foreach ($tiposCuidado as $tipo)
                                    <option value="{{ $tipo }}">{{ $nombreEstado($tipo) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="fecha" class="{{ $etiqueta }}">Fecha</label>
                            <input id="fecha" name="fecha" type="date" max="{{ today()->toDateString() }}" value="{{ today()->toDateString() }}" required class="{{ $campo }}">
                        </div>
                        <div>
                            <label for="observacion" class="{{ $etiqueta }}">Nota (opcional)</label>
                            <input id="observacion" name="observacion" type="text" maxlength="1000" class="{{ $campo }}">
                        </div>
                        <div class="md:col-span-3"><button type="submit" class="{{ $boton }}">Registrar cuidado</button></div>
                    </form>
                    <form method="POST" action="{{ route('produccion-vegetal.lotes.avanzar', $lote) }}" class="mt-4 border-t border-[#e4ebdf] pt-4">
                        @csrf
                        <input type="hidden" name="destino" value="en_revision">
                        <p class="mb-3 text-sm text-[#5f6d5f]">Cuidados registrados: {{ $lote->cuidados->count() }}</p>
                        <button type="submit" class="rounded-full border border-[#7cb22b] px-6 py-3 text-sm font-semibold text-[#55801f]">Enviar a revisión</button>
                    </form>
                    @break

                @case('en_revision')
                    @if ($ultimaRevision?->apta)
                        <form method="POST" action="{{ route('produccion-vegetal.lotes.clasificar', $lote) }}" class="mt-5 grid gap-4 md:grid-cols-3">
                            @csrf
                            <p class="text-sm font-semibold text-[#3b5d2d] md:col-span-3">La última revisión fue apta. Reparte las {{ $vivas }} plantas vivas (A + B + descarte = {{ $vivas }}).</p>
                            <div>
                                <label for="cantidad_categoria_a" class="{{ $etiqueta }}">Categoría A</label>
                                <input id="cantidad_categoria_a" name="cantidad_categoria_a" type="number" min="0" required class="{{ $campo }}">
                            </div>
                            <div>
                                <label for="cantidad_categoria_b" class="{{ $etiqueta }}">Categoría B</label>
                                <input id="cantidad_categoria_b" name="cantidad_categoria_b" type="number" min="0" required class="{{ $campo }}">
                            </div>
                            <div>
                                <label for="cantidad_descarte" class="{{ $etiqueta }}">Descarte</label>
                                <input id="cantidad_descarte" name="cantidad_descarte" type="number" min="0" required class="{{ $campo }}">
                            </div>
                            <div class="md:col-span-3"><button type="submit" class="{{ $boton }}">Clasificar plantas</button></div>
                        </form>
                    @else
                        <form method="POST" action="{{ route('produccion-vegetal.lotes.revisiones', $lote) }}" class="mt-5 grid gap-4 md:grid-cols-2">
                            @csrf
                            <fieldset>
                                <legend class="{{ $etiqueta }}">¿Las plantas están aptas?</legend>
                                <label class="mr-4 text-sm"><input type="radio" name="apta" value="1" required> Sí</label>
                                <label class="text-sm"><input type="radio" name="apta" value="0"> No, necesitan más cuidados</label>
                            </fieldset>
                            <div>
                                <label for="plantas_perdidas" class="{{ $etiqueta }}">Plantas perdidas (vivas ahora: {{ $vivas }})</label>
                                <input id="plantas_perdidas" name="plantas_perdidas" type="number" min="0" max="{{ $vivas }}" value="0" class="{{ $campo }}">
                            </div>
                            <div class="md:col-span-2">
                                <label for="observaciones" class="{{ $etiqueta }}">Nota (opcional)</label>
                                <input id="observaciones" name="observaciones" type="text" maxlength="1000" class="{{ $campo }}">
                            </div>
                            <div class="md:col-span-2"><button type="submit" class="{{ $boton }}">Registrar revisión</button></div>
                        </form>
                    @endif
                    @break

                @case('clasificada')
                    <p class="mt-4 text-sm text-[#304e42]">
                        Categoría A: {{ $lote->cantidad_categoria_a }} · Categoría B: {{ $lote->cantidad_categoria_b }} · Descarte: {{ $lote->cantidad_descarte }}.
                        Pasarán a inventario <strong>{{ (int) $lote->cantidad_categoria_a + (int) $lote->cantidad_categoria_b }}</strong> plantas de {{ $lote->planta?->nombre }}.
                    </p>
                    <form method="POST" action="{{ route('produccion-vegetal.lotes.cerrar', $lote) }}" class="mt-4">
                        @csrf
                        <button type="submit" class="{{ $boton }}">Enviar a inventario y cerrar</button>
                    </form>
                    @break

                @default
                    <p class="mt-4 text-sm text-[#304e42]">
                        Cerrado el {{ $lote->cerrado_en?->format('d/m/Y H:i') }}.
                        @if ($lote->movimiento)
                            Se registró la entrada de inventario #{{ $lote->movimiento->id }} por {{ $lote->movimiento->cantidad }} plantas.
                        @else
                            No hubo plantas vendibles, así que no se creó entrada de inventario.
                        @endif
                    </p>
            @endswitch
        </section>

        <section class="rounded-[10px] border border-[#e4ebdf] bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold text-[#103928]">Resumen del lote</h2>
            <div class="mt-3 grid gap-3 sm:grid-cols-2 xl:grid-cols-4 text-sm">
                <p><span class="text-[#7a965f]">Motivo:</span> <strong>{{ $nombreEstado($lote->motivo) }}</strong></p>
                @if ($lote->metodo_propagacion)<p><span class="text-[#7a965f]">Método:</span> <strong>{{ $nombreEstado($lote->metodo_propagacion) }}</strong></p>@endif
                @if ($lote->sustrato_mezcla)<p><span class="text-[#7a965f]">Sustrato:</span> <strong>{{ $lote->sustrato_mezcla }}</strong></p>@endif
                @if ($lote->cantidad_sembrada)<p><span class="text-[#7a965f]">Colocadas:</span> <strong>{{ $lote->cantidad_sembrada }}</strong></p>@endif
                @if ($lote->cantidad_perdida)<p><span class="text-[#7a965f]">Perdidas:</span> <strong>{{ $lote->cantidad_perdida }}</strong></p>@endif
                @if ($lote->cantidad_producida !== null)<p><span class="text-[#7a965f]">Producidas:</span> <strong>{{ $lote->cantidad_producida }}</strong></p>@endif
            </div>
        </section>

        @if ($lote->cuidados->isNotEmpty() || $lote->revisiones->isNotEmpty())
            <details class="rounded-[10px] border border-[#e4ebdf] bg-white p-6 shadow-sm">
                <summary class="cursor-pointer text-lg font-bold text-[#103928]">Historial de cuidados y revisiones</summary>
                @if ($lote->cuidados->isNotEmpty())
                    <h3 class="mt-4 text-sm font-bold text-[#3b5d2d]">Cuidados</h3>
                    <ul class="mt-2 space-y-1 text-sm text-[#304e42]">
                        @foreach ($lote->cuidados->sortByDesc('id') as $cuidado)
                            <li>{{ $cuidado->fecha->format('d/m/Y') }} · {{ $nombreEstado($cuidado->tipo) }}@if ($cuidado->observacion) — {{ $cuidado->observacion }}@endif</li>
                        @endforeach
                    </ul>
                @endif
                @if ($lote->revisiones->isNotEmpty())
                    <h3 class="mt-4 text-sm font-bold text-[#3b5d2d]">Revisiones</h3>
                    <ul class="mt-2 space-y-1 text-sm text-[#304e42]">
                        @foreach ($lote->revisiones->sortByDesc('id') as $revision)
                            <li>{{ $revision->created_at->format('d/m/Y H:i') }} · {{ $revision->apta ? 'Apta' : 'No apta' }} · Perdidas: {{ $revision->plantas_perdidas }}@if ($revision->observaciones) — {{ $revision->observaciones }}@endif</li>
                        @endforeach
                    </ul>
                @endif
            </details>
        @endif
    </main>
</body>
</html>
