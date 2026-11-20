<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    @vite('resources/css/app.css')
    <title>Detalle del caso</title>
</head>

<body class="flex font-sans h-screen">

    @include('partials.sidebar')

    <main class="flex flex-5 flex-col p-8 bg-[#fbfbfb] h-full gap-4 overflow-y-auto">
        <section class="flex items-center justify-between gap-4">
            <div class="flex-4">
                <h1 class="text-[#103928] font-bold text-[2.5em]">Detalle del caso</h1>
                <p class="text-[#8b8d8f] text-[1em]">Consulta la ficha del caso y el estado del flujo.</p>
            </div>
            <div class="flex-1 flex justify-end">
                <a href="{{ route('mantenimiento.casos.index') }}"
                   class="inline-flex items-center justify-center rounded-[10px] border border-[#dce6d7] bg-white px-5 py-2.5 text-sm font-medium text-[#3b5b42] transition hover:bg-[#f1f7ee]">
                    Volver
                </a>
            </div>
        </section>

        @php
            $estados = App\Services\CasoMantenimientoService::ESTADOS;
        @endphp

        <section class="rounded-[10px] border border-[#e4ebdf] bg-white p-6 shadow-sm sm:p-8">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#7a965f]">Código</p>
                    <h2 class="mt-2 text-2xl font-bold text-[#0f3c2b]">{{ $caso->codigo }}</h2>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <span class="rounded-full bg-[#edf8e7] px-3 py-1 text-sm font-semibold text-[#3b5d2d]">
                        {{ ucfirst(str_replace('_', ' ', $caso->estado)) }}
                    </span>
                    <span class="rounded-full bg-[#f2f4ec] px-3 py-1 text-sm font-medium text-[#49614d]">
                        {{ ucfirst($caso->urgencia) }}
                    </span>
                </div>
            </div>

            <div class="mb-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($estados as $estado)
                    <div class="rounded-[10px] border {{ $caso->estado === $estado ? 'border-[#7cb22b] bg-[#f4f9ea]' : 'border-[#e4ebdf] bg-[#f8faf6]' }} px-4 py-3 text-sm text-[#0f3c2b]">
                        {{ ucfirst(str_replace('_', ' ', $estado)) }}
                    </div>
                @endforeach
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="space-y-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#7a965f]">Solicitante</p>
                        <p class="mt-2 text-lg font-semibold text-[#0f3c2b]">{{ $caso->solicitante?->name ?? 'Sin solicitante' }}</p>
                        <p class="text-sm text-[#5f6d5f]">{{ $caso->solicitante?->email ?? '' }}</p>
                    </div>

                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#7a965f]">Área verde</p>
                        <p class="mt-2 text-lg font-medium text-[#0f3c2b]">{{ $caso->area_verde }}</p>
                    </div>

                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#7a965f]">Tipo</p>
                        <p class="mt-2 text-lg font-medium text-[#0f3c2b]">{{ ucfirst(str_replace('_', ' ', $caso->tipo_mantenimiento)) }}</p>
                    </div>

                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#7a965f]">Fecha deseada</p>
                        <p class="mt-2 text-lg font-medium text-[#0f3c2b]">
                            {{ $caso->fecha_deseada ? \Carbon\Carbon::parse($caso->fecha_deseada)->format('d/m/Y') : 'No especificado' }}
                        </p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#7a965f]">Descripción</p>
                        <p class="mt-2 whitespace-pre-line text-base text-[#1d352d]">{{ $caso->descripcion }}</p>
                    </div>

                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#7a965f]">Observaciones</p>
                        <p class="mt-2 whitespace-pre-line text-base text-[#1d352d]">{{ $caso->observaciones ?? 'No especificado' }}</p>
                    </div>

                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#7a965f]">Creado el</p>
                        <p class="mt-2 text-base text-[#1d352d]">{{ $caso->created_at?->format('d/m/Y H:i') ?? 'Sin fecha' }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="mt-8 rounded-[10px] border border-[#e4ebdf] bg-white p-6 shadow-sm" aria-labelledby="secuencia-titulo">
            <h2 id="secuencia-titulo" class="text-xl font-bold text-[#103928]">Flujo de mantenimiento</h2>
            <p class="mt-2 text-sm text-[#5f6d5f]">Abre una etapa para trabajarla. Cada etapa conserva Volver, Guardar y Siguiente.</p>

            <div class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                <a href="{{ route('mantenimiento.casos.show.etapa', ['caso' => $caso, 'etapa' => 'diagnostico']) }}" class="rounded-[10px] border border-[#dce6d7] bg-[#f8faf6] px-4 py-4 font-semibold text-[#0f3c2b] transition hover:border-[#7cb22b] hover:bg-[#f1f7ee]">Diagnosticar</a>
                <a href="{{ route('mantenimiento.casos.show.etapa', ['caso' => $caso, 'etapa' => 'plan']) }}" class="rounded-[10px] border border-[#dce6d7] bg-[#f8faf6] px-4 py-4 font-semibold text-[#0f3c2b] transition hover:border-[#7cb22b] hover:bg-[#f1f7ee]">Plan de mantenimiento</a>
                <a href="{{ route('mantenimiento.casos.show.etapa', ['caso' => $caso, 'etapa' => 'recursos']) }}" class="rounded-[10px] border border-[#dce6d7] bg-[#f8faf6] px-4 py-4 font-semibold text-[#0f3c2b] transition hover:border-[#7cb22b] hover:bg-[#f1f7ee]">Disponibilidad de recursos</a>
                <a href="{{ route('mantenimiento.casos.show.etapa', ['caso' => $caso, 'etapa' => 'abastecimiento']) }}" class="rounded-[10px] border border-[#dce6d7] bg-[#f8faf6] px-4 py-4 font-semibold text-[#0f3c2b] transition hover:border-[#7cb22b] hover:bg-[#f1f7ee]">Abastecimiento</a>
                <a href="{{ route('mantenimiento.casos.show.etapa', ['caso' => $caso, 'etapa' => 'ejecucion']) }}" class="rounded-[10px] border border-[#dce6d7] bg-[#f8faf6] px-4 py-4 font-semibold text-[#0f3c2b] transition hover:border-[#7cb22b] hover:bg-[#f1f7ee]">Ejecución</a>
                <a href="{{ route('mantenimiento.casos.show.etapa', ['caso' => $caso, 'etapa' => 'cierre']) }}" class="rounded-[10px] border border-[#dce6d7] bg-[#f8faf6] px-4 py-4 font-semibold text-[#0f3c2b] transition hover:border-[#7cb22b] hover:bg-[#f1f7ee]">Cierre / Reapertura</a>
            </div>
        </section>
    </main>
</body>

</html>
