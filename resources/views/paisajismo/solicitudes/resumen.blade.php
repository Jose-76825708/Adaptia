@extends('layouts.app')
@section('title','Resumen del proyecto')
@section('content')
<main class="mx-auto max-w-5xl px-4 py-10">
<section class="rounded-[10px] border border-[#e4ebdf] bg-white p-6 shadow-sm">
<div class="flex items-center justify-between gap-4"><div><p class="text-xs font-bold uppercase tracking-[0.18em] text-[#7a965f]">{{ $proyecto->codigo }}</p><h1 class="mt-2 text-3xl font-bold text-[#0f3c2b]">Resumen del proyecto</h1></div><a href="{{ route('paisajismo.solicitudes.index') }}" class="rounded-full border border-[#dce6d7] px-5 py-2.5 text-sm font-semibold text-[#3b5b42]">Volver</a></div>
<table class="mt-5 w-full border-collapse text-sm text-[#1d352d]"><thead><tr class="bg-[#edf8e7] text-[#3b5d2d]"><th scope="col" class="border px-3 py-2 text-center">Nombre</th><th scope="col" class="border px-3 py-2 text-center">Ubicación</th><th scope="col" class="border px-3 py-2 text-center">Estado</th></tr></thead><tbody><tr><td class="border px-3 py-2 text-center">{{ $resumen['proyecto']['nombre'] }}</td><td class="border px-3 py-2 text-center">{{ $resumen['proyecto']['ubicacion'] }}</td><td class="border px-3 py-2 text-center">{{ ucfirst(str_replace('_',' ', $resumen['proyecto']['estado'])) }}</td></tr></tbody></table>
@if ($resumen['propuesta'])<div class="mt-5 rounded-[10px] border border-[#e4ebdf] p-4"><h2 class="font-bold text-[#103928]">Propuesta {{ $resumen['propuesta']['version'] }}</h2><p class="mt-2 whitespace-pre-line text-[#1d352d]">{{ $resumen['propuesta']['concepto'] }}</p><p class="mt-3 font-semibold text-[#0f3c2b]">Total: S/ {{ number_format($resumen['propuesta']['total'],2) }}</p></div>@endif
@if ($resumen['entrega'])<div class="mt-5 rounded-[10px] border border-[#e4ebdf] p-4"><h2 class="font-bold text-[#103928]">Entrega</h2><p class="mt-2 whitespace-pre-line text-[#1d352d]">{{ $resumen['entrega']['resumen'] }}</p></div>@endif
@if (!$resumen['satisfaccion'])
<form method="POST" action="{{ route('paisajismo.solicitudes.satisfaccion',$proyecto) }}" class="mt-5 rounded-[10px] border border-[#e4ebdf] p-5">@csrf<label for="pais-calificacion" class="block text-sm font-semibold text-[#0f3c2b]">Calificación</label><select id="pais-calificacion" name="calificacion" class="mt-1 w-full rounded-2xl border border-[#d8e2d7] px-3 py-2"><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option></select><label for="pais-comentario" class="mt-3 block text-sm font-semibold text-[#0f3c2b]">Comentario</label><textarea id="pais-comentario" name="comentario" rows="3" class="mt-1 w-full rounded-2xl border border-[#d8e2d7] px-3 py-2"></textarea><button type="submit" class="mt-3 rounded-full bg-[#7cb22b] px-5 py-2.5 text-sm font-semibold text-white">Guardar calificación</button></form>
@endif
<form method="POST" action="{{ route('paisajismo.solicitudes.mantenimiento',$proyecto) }}" class="mt-5">@csrf<button type="submit" class="rounded-full border border-[#dce6d7] px-5 py-2.5 text-sm font-semibold text-[#3b5b42]">Solicitar mantenimiento posterior</button></form>
</section>
</main>
@endsection
