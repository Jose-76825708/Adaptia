<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <title>Etapa del caso</title>
</head>
<body class="flex h-screen font-sans">
    @include('partials.sidebar')

    <main class="flex flex-5 flex-col gap-4 overflow-y-auto bg-[#fbfbfb] p-8">
        <section class="flex items-center justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#7a965f]">Caso {{ $caso->codigo }}</p>
                <h1 class="text-[2.5em] font-bold text-[#103928]">
                    @if ($etapa === 'diagnostico') Diagnóstico
                    @elseif ($etapa === 'plan') Plan de mantenimiento
                    @elseif ($etapa === 'recursos') Disponibilidad de recursos
                    @elseif ($etapa === 'abastecimiento') Abastecimiento
                    @elseif ($etapa === 'ejecucion') Ejecución
                    @else Cierre / Reapertura @endif
                </h1>
            </div>

            <a href="{{ route('mantenimiento.casos.show', $caso) }}" class="inline-flex items-center justify-center rounded-[10px] border border-[#dce6d7] bg-white px-5 py-2.5 text-sm font-medium text-[#3b5b42] transition hover:bg-[#f1f7ee]">Volver al detalle</a>
        </section>

        @if (session('success'))
            <div id="etapa-exito" class="rounded-[10px] border border-[#cfe0c2] bg-[#edf8e7] p-4 text-sm font-semibold text-[#3b5d2d]" role="status">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div id="etapa-errores" class="rounded-[10px] border border-red-200 bg-red-50 p-4 text-sm text-red-700" role="alert">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        @include('mantenimiento.casos.paneles.' . $etapa, ['caso' => $caso])

        <section class="flex flex-wrap justify-between gap-3 rounded-[10px] border border-[#e4ebdf] bg-white p-4">
            <a href="{{ route('mantenimiento.casos.show', $caso) }}" class="rounded-full border border-[#dce6d7] px-5 py-2.5 text-sm font-semibold text-[#3b5b42] transition hover:bg-[#f1f7ee]">Volver</a>

            @if ($etapa !== 'cierre')
                <div class="flex gap-3">
                    @php
                        $siguiente = match ($etapa) {
                            'diagnostico' => 'plan',
                            'plan' => 'recursos',
                            'recursos' => 'abastecimiento',
                            'abastecimiento' => 'ejecucion',
                            'ejecucion' => 'cierre',
                            default => 'diagnostico',
                        };
                    @endphp
                    <a href="{{ route('mantenimiento.casos.show.etapa', ['caso' => $caso, 'etapa' => $siguiente]) }}" class="rounded-full bg-[#7cb22b] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#6eab26]">Siguiente</a>
                </div>
            @else
                <form method="POST" action="{{ route('mantenimiento.casos.cierre.cerrar', $caso) }}">
                    @csrf
                    <button type="submit" class="rounded-full bg-[#7cb22b] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#6eab26]">Finalizar</button>
                </form>
            @endif
        </section>
    </main>
</body>
</html>
