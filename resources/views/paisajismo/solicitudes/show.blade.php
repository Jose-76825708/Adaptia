@extends('layouts.app')
@section('title','Detalle del proyecto')
@section('content')
<main class="mx-auto max-w-5xl px-4 py-10">
<section class="rounded-[10px] border border-[#e4ebdf] bg-white p-6 shadow-sm">
<div class="mb-6 flex items-center justify-between gap-4"><div><p class="text-xs font-bold uppercase tracking-[0.18em] text-[#7a965f]">{{ $proyecto->codigo }}</p><h1 class="mt-2 text-3xl font-bold text-[#0f3c2b]">Detalle del proyecto</h1></div><a href="{{ route('paisajismo.solicitudes.index') }}" class="rounded-full border border-[#dce6d7] px-5 py-2.5 text-sm font-semibold text-[#3b5b42]">Volver a la lista</a></div>
<div class="grid gap-4 md:grid-cols-2"><div class="rounded-[10px] border border-[#e4ebdf] p-4"><p class="text-xs font-bold text-[#7a965f]">Nombre</p><p class="mt-1 font-semibold text-[#0f3c2b]">{{ $proyecto->nombre }}</p></div><div class="rounded-[10px] border border-[#e4ebdf] p-4"><p class="text-xs font-bold text-[#7a965f]">Estado</p><p class="mt-1 font-semibold text-[#0f3c2b]">{{ ucfirst(str_replace('_',' ', $proyecto->estado)) }}</p></div><div class="rounded-[10px] border border-[#e4ebdf] p-4"><p class="text-xs font-bold text-[#7a965f]">Ubicación</p><p class="mt-1 text-[#1d352d]">{{ $proyecto->ubicacion }}</p></div><div class="rounded-[10px] border border-[#e4ebdf] p-4"><p class="text-xs font-bold text-[#7a965f]">Área</p><p class="mt-1 text-[#1d352d]">{{ number_format((float)$proyecto->area_m2,2) }} m²</p></div><div class="rounded-[10px] border border-[#e4ebdf] p-4"><p class="text-xs font-bold text-[#7a965f]">Presupuesto</p><p class="mt-1 text-[#1d352d]">{{ $proyecto->presupuesto_estimado !== null ? 'S/ '.number_format((float)$proyecto->presupuesto_estimado,2) : 'No indicado' }}</p></div><div class="rounded-[10px] border border-[#e4ebdf] p-4"><p class="text-xs font-bold text-[#7a965f]">Fecha deseada</p><p class="mt-1 text-[#1d352d]">{{ $proyecto->fecha_deseada ? $proyecto->fecha_deseada->format('d/m/Y') : 'No indicada' }}</p></div></div>
<div class="mt-4 rounded-[10px] border border-[#e4ebdf] p-4"><p class="text-xs font-bold text-[#7a965f]">Descripción</p><p class="mt-2 whitespace-pre-line text-[#1d352d]">{{ $proyecto->descripcion }}</p></div>
<p class="mt-4 text-sm text-[#5f6d5f]">Fecha de solicitud: {{ $proyecto->created_at?->format('d/m/Y') ?? 'Sin fecha' }}</p>
<div class="mt-5 flex flex-wrap gap-3">
@if ($proyecto->propuestas_count>0)
<a href="{{ route('paisajismo.solicitudes.propuesta',$proyecto) }}" class="rounded-full bg-[#7cb22b] px-5 py-2.5 text-sm font-semibold text-white">Ver propuesta</a>
@endif
@if ($proyecto->estado==='entregado')
<a href="{{ route('paisajismo.solicitudes.resumen',$proyecto) }}" class="rounded-full border border-[#dce6d7] px-5 py-2.5 text-sm font-semibold text-[#3b5b42]">Ver resumen</a>
@endif
</div>
</section>
</main>
@endsection
