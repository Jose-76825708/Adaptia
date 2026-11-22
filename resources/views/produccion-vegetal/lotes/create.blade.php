<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    @vite('resources/css/app.css')
    <title>Nuevo lote de producción</title>
</head>
<body class="flex h-screen font-sans">
    @include('partials.sidebar')

    <main class="flex flex-5 flex-col gap-5 overflow-y-auto bg-[#fbfbfb] p-8">
        <section class="flex items-center justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#7a965f]">Producción vegetal</p>
                <h1 class="text-[2.5em] font-bold text-[#103928]">Identificar necesidad</h1>
            </div>
            <a href="{{ route('produccion-vegetal.lotes.index') }}" class="rounded-full border border-[#dce6d7] bg-white px-5 py-2.5 text-sm font-semibold text-[#3b5b42]">Volver</a>
        </section>

        @if ($errors->any())
            <div class="rounded-[10px] border border-red-200 bg-red-50 p-4 text-sm text-red-700" role="alert">
                @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
            </div>
        @endif

        @if ($bajoMinimo->isNotEmpty())
            <section class="rounded-[10px] border border-[#e4ebdf] bg-[#f4f9ea] p-5">
                <h2 class="text-sm font-bold text-[#3b5d2d]">Plantas bajo stock mínimo (referencia)</h2>
                <ul class="mt-2 text-sm text-[#304e42]">
                    @foreach ($bajoMinimo as $planta)
                        <li>{{ $planta->nombre }}: {{ $planta->stock_actual }} de {{ $planta->stock_minimo }} mínimo</li>
                    @endforeach
                </ul>
            </section>
        @endif

        <form method="POST" action="{{ route('produccion-vegetal.lotes.store') }}" class="space-y-5">
            @csrf
            <section class="rounded-[10px] border border-[#e4ebdf] bg-white p-6 shadow-sm">
                <h2 class="text-xl font-bold text-[#103928]">Necesidad de producto</h2>
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="motivo" class="mb-2 block text-sm font-semibold text-[#0f3c2b]">Motivo</label>
                        <select id="motivo" name="motivo" required class="w-full rounded-2xl border border-[#d8e2d7] bg-[#f8faf6] px-3 py-2.5">
                            @foreach ($motivos as $motivo)
                                <option value="{{ $motivo }}" @selected(old('motivo') === $motivo)>{{ ucfirst(str_replace('_', ' ', $motivo)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="cantidad_meta" class="mb-2 block text-sm font-semibold text-[#0f3c2b]">Cantidad meta de plantas</label>
                        <input id="cantidad_meta" name="cantidad_meta" type="number" min="1" value="{{ old('cantidad_meta') }}" required class="w-full rounded-2xl border border-[#d8e2d7] bg-[#f8faf6] px-3 py-2.5">
                    </div>
                    <div class="md:col-span-2">
                        <label for="observaciones" class="mb-2 block text-sm font-semibold text-[#0f3c2b]">Observaciones</label>
                        <textarea id="observaciones" name="observaciones" rows="3" maxlength="2000" class="w-full rounded-2xl border border-[#d8e2d7] bg-[#f8faf6] px-3 py-2.5">{{ old('observaciones') }}</textarea>
                    </div>
                </div>
            </section>
            <div class="flex justify-end">
                <button type="submit" class="rounded-full bg-[#7cb22b] px-6 py-3 text-sm font-semibold text-white">Crear lote</button>
            </div>
        </form>
    </main>
</body>
</html>
