@extends('layouts.app')

@section('title','Mis proyectos de paisajismo')

@section('content')
<main class="mx-auto max-w-6xl px-4 py-10">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div><p class="text-xs font-bold uppercase tracking-[0.18em] text-[#7a965f]">Paisajismo</p><h1 class="mt-2 text-3xl font-bold text-[#0f3c2b]">Mis proyectos</h1></div>
        <div class="flex gap-3"><a href="{{ route('home') }}" class="rounded-full border border-[#dce6d7] px-5 py-2.5 text-sm font-medium text-[#3b5b42]">Volver</a><a href="{{ route('paisajismo.solicitudes.create') }}" class="rounded-full bg-[#7cb22b] px-5 py-2.5 text-sm font-semibold text-white">Solicitar proyecto</a></div>
    </div>

    @if (session('success'))
        <div id="pais-exito" class="mb-4 rounded-[10px] border border-[#cfe0c2] bg-[#edf8e7] p-4 text-sm font-semibold text-[#3b5d2d]" role="status">{{ session('success') }}</div>
    @endif

    <div class="overflow-x-auto rounded-[10px] border border-[#e4ebdf] bg-white p-5">
        <table class="w-full border-collapse text-sm text-[#1d352d]">
            <thead><tr class="bg-[#edf8e7] text-[#3b5d2d]"><th scope="col" class="border border-[#dce6d7] px-3 py-2 text-center">Código</th><th scope="col" class="border border-[#dce6d7] px-3 py-2 text-center">Nombre</th><th scope="col" class="border border-[#dce6d7] px-3 py-2 text-center">Ubicación</th><th scope="col" class="border border-[#dce6d7] px-3 py-2 text-center">Área (m²)</th><th scope="col" class="border border-[#dce6d7] px-3 py-2 text-center">Fecha de solicitud</th><th scope="col" class="border border-[#dce6d7] px-3 py-2 text-center">Estado</th><th scope="col" class="border border-[#dce6d7] px-3 py-2 text-center">Acciones</th></tr></thead>
            <tbody>
            @forelse ($proyectos as $proyecto)
                <tr class="even:bg-[#f8faf6]">
                    <td class="border border-[#e4ebdf] px-3 py-2 text-center">{{ $proyecto->codigo }}</td>
                    <td class="border border-[#e4ebdf] px-3 py-2 text-center">{{ $proyecto->nombre }}</td>
                    <td class="border border-[#e4ebdf] px-3 py-2 text-center">{{ $proyecto->ubicacion }}</td>
                    <td class="border border-[#e4ebdf] px-3 py-2 text-center">{{ number_format((float)$proyecto->area_m2,2) }}</td>
                    <td class="border border-[#e4ebdf] px-3 py-2 text-center">{{ $proyecto->created_at?->format('d/m/Y') ?? 'Sin fecha' }}</td>
                    <td class="border border-[#e4ebdf] px-3 py-2 text-center">{{ ucfirst(str_replace('_',' ', $proyecto->estado)) }}</td>
                    <td class="border border-[#e4ebdf] px-3 py-2 text-center">
                        <div class="flex flex-wrap justify-center gap-2">
                            <a href="{{ route('paisajismo.solicitudes.show',$proyecto) }}" class="rounded-full border border-[#dce6d7] px-3 py-1 text-xs font-semibold text-[#3b5b42]">Ver detalle</a>
                            @if ($proyecto->propuestas_count>0)
                                <a href="{{ route('paisajismo.solicitudes.propuesta',$proyecto) }}" class="rounded-full border border-[#dce6d7] px-3 py-1 text-xs font-semibold text-[#3b5b42]">Ver propuesta</a>
                            @endif
                            @if ($proyecto->estado==='entregado')
                                <a href="{{ route('paisajismo.solicitudes.resumen',$proyecto) }}" class="rounded-full border border-[#dce6d7] px-3 py-1 text-xs font-semibold text-[#3b5b42]">Ver resumen</a>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-3 py-8 text-center text-[#5f6d5f]">No tienes proyectos registrados.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div class="mt-4">{{ $proyectos->links() }}</div>
    </div>
</main>
@endsection
