<section id="ent-panel" class="rounded-[10px] border border-[#e4ebdf] bg-[#f8faf6] p-5 shadow-sm" aria-labelledby="ent-titulo">
<h2 id="ent-titulo" class="text-xl font-bold text-[#103928]">Entrega</h2>
@if ($errors->entrega->any())<div id="ent-errores" class="mt-3 rounded-[10px] border border-red-200 bg-red-50 p-3 text-sm text-red-700" role="alert">@foreach ($errors->entrega->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
@if ($proyecto->estado==='en_instalacion')
@if ((int)($ultimo_avance_entrega?->porcentaje ?? 0)!==100)
<div id="ent-avance-100" class="mt-3 rounded-[10px] border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">Para registrar la entrega, el avance debe llegar al 100%. Avance actual: {{ (int)($ultimo_avance_entrega?->porcentaje ?? 0) }}%</div>
@else
<form method="POST" action="{{ route('paisajismo.proyectos.entrega',$proyecto) }}" class="mt-4 rounded-[10px] border border-[#e4ebdf] bg-white p-5">@csrf
<label for="ent-resumen" class="block text-sm font-semibold text-[#0f3c2b]">Resumen</label><textarea id="ent-resumen" name="ent_resumen" rows="3" class="mt-1 w-full rounded-2xl border border-[#d8e2d7] px-3 py-2">{{ old('ent_resumen') }}</textarea>
<label for="ent-recomendaciones" class="mt-3 block text-sm font-semibold text-[#0f3c2b]">Recomendaciones</label><textarea id="ent-recomendaciones" name="ent_recomendaciones" rows="3" class="mt-1 w-full rounded-2xl border border-[#d8e2d7] px-3 py-2">{{ old('ent_recomendaciones') }}</textarea>
<p class="mt-2 text-sm text-[#5f6d5f]">Último avance: {{ $ultimo_avance_entrega?->porcentaje ?? 0 }}%</p>
<button type="submit" class="mt-3 rounded-full bg-[#7cb22b] px-5 py-2.5 text-sm font-semibold text-white">Registrar entrega</button>
</form>
@endif
@endif
@if ($entrega_vigente)
<table class="mt-4 w-full border-collapse text-sm text-[#1d352d]"><thead><tr class="bg-[#edf8e7] text-[#3b5d2d]"><th scope="col" class="border px-3 py-2 text-center">Resumen</th><th scope="col" class="border px-3 py-2 text-center">Calificación</th></tr></thead><tbody><tr><td class="border px-3 py-2 text-center whitespace-pre-line">{{ $entrega_vigente->resumen }}</td><td class="border px-3 py-2 text-center">{{ $entrega_vigente->calificacion ?? 'Sin calificar' }}</td></tr></tbody></table>
@endif
</section>
