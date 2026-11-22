<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    @vite('resources/css/app.css')
    <title>Nueva revisión de existencias</title>
</head>
<body class="flex h-screen font-sans">
    @include('partials.sidebar')

    <main class="flex flex-5 flex-col gap-5 overflow-y-auto bg-[#fbfbfb] p-8">
        <section class="flex items-center justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#7a965f]">Gestión de inventario</p>
                <h1 class="text-[2.5em] font-bold text-[#103928]">Revisar existencias</h1>
            </div>
            <a href="{{ route('gestion-inventario.index') }}" class="rounded-full border border-[#dce6d7] bg-white px-5 py-2.5 text-sm font-semibold text-[#3b5b42]">Volver</a>
        </section>

        @if ($errors->any())
            <div class="rounded-[10px] border border-red-200 bg-red-50 p-4 text-sm text-red-700" role="alert">
                @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('gestion-inventario.store') }}" class="space-y-5">
            @csrf
            <section class="rounded-[10px] border border-[#e4ebdf] bg-white p-6 shadow-sm">
                <h2 class="text-xl font-bold text-[#103928]">Producto y cantidad requerida</h2>
                <p class="mt-1 text-sm text-[#5f6d5f]">Indica qué producto necesitas y cuánto. Se comparará con el stock del sistema y con el conteo físico.</p>
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="tipo_item" class="mb-2 block text-sm font-semibold text-[#0f3c2b]">Tipo de producto</label>
                        <select id="tipo_item" name="tipo_item" required class="w-full rounded-2xl border border-[#d8e2d7] bg-[#f8faf6] px-3 py-2.5">
                            <option value="planta" @selected(old('tipo_item', $tipoInicial) === 'planta')>Planta</option>
                            <option value="insumo" @selected(old('tipo_item', $tipoInicial) === 'insumo')>Insumo</option>
                        </select>
                    </div>
                    <div>
                        <label for="cantidad_requerida" class="mb-2 block text-sm font-semibold text-[#0f3c2b]">Cantidad requerida</label>
                        <input id="cantidad_requerida" name="cantidad_requerida" type="number" min="0.01" step="0.01" value="{{ old('cantidad_requerida') }}" required class="w-full rounded-2xl border border-[#d8e2d7] bg-[#f8faf6] px-3 py-2.5">
                    </div>
                    <div id="campo-planta">
                        <label for="planta_id" class="mb-2 block text-sm font-semibold text-[#0f3c2b]">Planta</label>
                        <select id="planta_id" name="planta_id" class="w-full rounded-2xl border border-[#d8e2d7] bg-[#f8faf6] px-3 py-2.5">
                            <option value="">Selecciona una planta</option>
                            @foreach ($plantas as $planta)
                                <option value="{{ $planta->id }}" @selected((string) old('planta_id', $plantaInicial) === (string) $planta->id)>{{ $planta->nombre }} (stock {{ $planta->stock_actual }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="campo-insumo">
                        <label for="recurso_id" class="mb-2 block text-sm font-semibold text-[#0f3c2b]">Insumo</label>
                        <select id="recurso_id" name="recurso_id" class="w-full rounded-2xl border border-[#d8e2d7] bg-[#f8faf6] px-3 py-2.5">
                            <option value="">Selecciona un insumo</option>
                            @foreach ($insumos as $insumo)
                                <option value="{{ $insumo->id }}" @selected((string) old('recurso_id', $insumoInicial) === (string) $insumo->id)>{{ $insumo->nombre }} (stock {{ rtrim(rtrim(number_format((float) $insumo->stock_actual, 2, '.', ''), '0'), '.') }} {{ $insumo->unidad }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label for="motivo" class="mb-2 block text-sm font-semibold text-[#0f3c2b]">Motivo (opcional)</label>
                        <textarea id="motivo" name="motivo" rows="3" maxlength="2000" class="w-full rounded-2xl border border-[#d8e2d7] bg-[#f8faf6] px-3 py-2.5">{{ old('motivo') }}</textarea>
                    </div>
                </div>
            </section>

            <div class="flex justify-end">
                <button type="submit" class="rounded-full bg-[#7cb22b] px-6 py-3 text-sm font-semibold text-white">Registrar revisión</button>
            </div>
        </form>
    </main>

    <script>
        (function () {
            const tipo = document.getElementById('tipo_item');
            const planta = document.getElementById('campo-planta');
            const insumo = document.getElementById('campo-insumo');
            function actualizar() {
                const esPlanta = tipo.value === 'planta';
                planta.hidden = !esPlanta;
                insumo.hidden = esPlanta;
            }
            tipo.addEventListener('change', actualizar);
            actualizar();
        })();
    </script>
</body>
</html>
