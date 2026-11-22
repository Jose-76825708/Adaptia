<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    @vite('resources/css/app.css')
    <title>Nueva campaña comercial</title>
</head>
<body class="flex h-screen font-sans">
    @include('partials.sidebar')

    <main class="flex flex-5 flex-col gap-5 overflow-y-auto bg-[#fbfbfb] p-8">
        <section class="flex items-center justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#7a965f]">Planeación comercial</p>
                <h1 class="text-[2.5em] font-bold text-[#103928]">Definir campaña</h1>
            </div>
            <a href="{{ route('planeacion-comercial.campanas.index') }}" class="rounded-full border border-[#dce6d7] bg-white px-5 py-2.5 text-sm font-semibold text-[#3b5b42]">Volver</a>
        </section>

        @if ($errors->any())
            <div class="rounded-[10px] border border-red-200 bg-red-50 p-4 text-sm text-red-700" role="alert">
                @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('planeacion-comercial.campanas.store') }}" class="space-y-5">
            @csrf
            <section class="rounded-[10px] border border-[#e4ebdf] bg-white p-6 shadow-sm">
                <h2 class="text-xl font-bold text-[#103928]">Objetivos, presupuesto y fechas</h2>
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label for="objetivos" class="mb-2 block text-sm font-semibold text-[#0f3c2b]">Objetivos comerciales</label>
                        <textarea id="objetivos" name="objetivos" rows="3" maxlength="5000" required class="w-full rounded-2xl border border-[#d8e2d7] bg-[#f8faf6] px-3 py-2.5">{{ old('objetivos') }}</textarea>
                    </div>
                    <div>
                        <label for="presupuesto" class="mb-2 block text-sm font-semibold text-[#0f3c2b]">Presupuesto</label>
                        <input id="presupuesto" name="presupuesto" type="number" min="0" step="0.01" value="{{ old('presupuesto', 0) }}" required class="w-full rounded-2xl border border-[#d8e2d7] bg-[#f8faf6] px-3 py-2.5">
                    </div>
                    <div></div>
                    <div>
                        <label for="fecha_inicio" class="mb-2 block text-sm font-semibold text-[#0f3c2b]">Fecha de inicio</label>
                        <input id="fecha_inicio" name="fecha_inicio" type="date" value="{{ old('fecha_inicio') }}" required class="w-full rounded-2xl border border-[#d8e2d7] bg-[#f8faf6] px-3 py-2.5">
                    </div>
                    <div>
                        <label for="fecha_fin" class="mb-2 block text-sm font-semibold text-[#0f3c2b]">Fecha de fin</label>
                        <input id="fecha_fin" name="fecha_fin" type="date" value="{{ old('fecha_fin') }}" required class="w-full rounded-2xl border border-[#d8e2d7] bg-[#f8faf6] px-3 py-2.5">
                    </div>
                </div>
            </section>

            <section class="rounded-[10px] border border-[#e4ebdf] bg-white p-6 shadow-sm">
                <h2 class="text-xl font-bold text-[#103928]">Plantas y metas de venta</h2>
                <p class="mt-1 text-sm text-[#5f6d5f]">Añade una fila por planta. La cantidad meta también se usa para comprobar disponibilidad.</p>
                @include('planeacion-comercial.partials.productos-form', ['productosIniciales' => []])
            </section>

            <div class="flex justify-end">
                <button type="submit" class="rounded-full bg-[#7cb22b] px-6 py-3 text-sm font-semibold text-white">Crear campaña</button>
            </div>
        </form>
    </main>
</body>
</html>
