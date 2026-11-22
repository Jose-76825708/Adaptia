@if (auth()->user()->rol === 'administrador')
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    @vite('resources/css/app.css')
    <title>Informe final</title>
</head>
<body class="flex h-screen font-sans">
    @include('partials.sidebar')

    <main id="informe-caso" class="flex flex-5 flex-col gap-4 overflow-y-auto bg-[#fbfbfb] p-8">
        <section class="flex items-center justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#7a965f]">Caso {{ $caso->codigo }}</p>
                <h1 id="informe-titulo" class="text-[2.5em] font-bold text-[#103928]">Informe final</h1>
            </div>

            @if (auth()->user()->rol === 'administrador')
                <a id="informe-volver" href="{{ route('mantenimiento.casos.show', $caso) }}" class="inline-flex items-center justify-center rounded-[10px] border border-[#dce6d7] bg-white px-5 py-2.5 text-sm font-medium text-[#3b5b42] transition hover:bg-[#f1f7ee]">Volver al detalle del caso</a>
            @else
                <a id="informe-volver" href="{{ route('mantenimiento.solicitudes.index') }}" class="inline-flex items-center justify-center rounded-[10px] border border-[#dce6d7] bg-white px-5 py-2.5 text-sm font-medium text-[#3b5b42] transition hover:bg-[#f1f7ee]">Volver a las solicitudes</a>
            @endif
        </section>

        <section id="informe-contenido" class="rounded-[10px] border border-[#e4ebdf] bg-white p-6 shadow-sm">
            <p id="informe-resumen" class="text-lg font-bold text-[#0f3c2b]">{{ $informe->resumen }}</p>

            <table class="mt-5 w-full border-collapse text-sm text-[#1d352d]">
                <thead>
                    <tr class="bg-[#edf8e7] text-[#3b5d2d]">
                        <th scope="col" class="border border-[#dce6d7] px-3 py-2 text-center">Versión</th>
                        <th scope="col" class="border border-[#dce6d7] px-3 py-2 text-center">Estado del caso</th>
                        <th scope="col" class="border border-[#dce6d7] px-3 py-2 text-center">Fecha de cierre</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="even:bg-[#f8faf6]">
                        <td class="border border-[#e4ebdf] px-3 py-2 text-center">{{ $informe->version }}</td>
                        <td class="border border-[#e4ebdf] px-3 py-2 text-center">{{ ucfirst(str_replace('_', ' ', $caso->estado)) }}</td>
                        <td class="border border-[#e4ebdf] px-3 py-2 text-center">{{ $informe->cerrado_en?->format('d/m/Y H:i') ?? 'Sin fecha' }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="mt-5 rounded-[10px] border border-[#e4ebdf] bg-[#f8faf6] p-5">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#7a965f]">Contenido del informe</p>
                <p id="informe-texto" class="mt-3 whitespace-pre-line text-base text-[#1d352d]">{{ $informe->contenido }}</p>
            </div>
        </section>
    </main>
</body>
</html>
@else
    @extends('layouts.app')

    @section('content')
        <main id="informe-caso-cliente" class="mx-auto max-w-4xl px-4 py-10">
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#7a965f]">Caso {{ $caso->codigo }}</p>
                    <h1 id="informe-titulo-cliente" class="mt-2 text-3xl font-bold text-[#0f3c2b]">Informe final</h1>
                </div>
                <a id="informe-volver-cliente" href="{{ route('mantenimiento.solicitudes.index') }}" class="inline-flex items-center justify-center rounded-full border border-[#dce6d7] px-5 py-2.5 text-sm font-medium text-[#3b5b42] transition hover:bg-[#f1f7ee]">Volver a mis solicitudes</a>
            </div>

            <article id="informe-contenido-cliente" class="rounded-[10px] border border-[#e4ebdf] bg-white p-6 shadow-sm">
                <p id="informe-resumen-cliente" class="text-lg font-bold text-[#0f3c2b]">{{ $informe->resumen }}</p>
                <p id="informe-texto-cliente" class="mt-4 whitespace-pre-line text-base text-[#1d352d]">{{ $informe->contenido }}</p>
                <p class="mt-5 text-sm text-[#5f6d5f]">Cierre registrado el {{ $informe->cerrado_en?->format('d/m/Y H:i') ?? 'sin fecha disponible' }}.</p>
            </article>
        </main>
    @endsection
@endif
