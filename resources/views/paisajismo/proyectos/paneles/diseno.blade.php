<section id="dis-panel" class="rounded-[10px] border border-[#e4ebdf] bg-[#f8faf6] p-5 shadow-sm" aria-labelledby="dis-titulo">
<h2 id="dis-titulo" class="text-xl font-bold text-[#103928]">Diseño y cotización</h2>
@if ($errors->diseno->any())<div id="dis-errores" class="mt-3 rounded-[10px] border border-red-200 bg-red-50 p-3 text-sm text-red-700" role="alert">@foreach ($errors->diseno->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
@if ($historial_propuestas->isNotEmpty())
<table id="dis-historial" class="mt-4 w-full border-collapse text-sm text-[#1d352d]"><thead><tr class="bg-[#edf8e7] text-[#3b5d2d]"><th scope="col" class="border px-3 py-2 text-center">Versión</th><th scope="col" class="border px-3 py-2 text-center">Fecha</th><th scope="col" class="border px-3 py-2 text-center">Total</th><th scope="col" class="border px-3 py-2 text-center">Decisión</th><th scope="col" class="border px-3 py-2 text-center">Motivo</th></tr></thead><tbody>@foreach ($historial_propuestas as $version)<tr class="even:bg-[#f8faf6]"><td class="border px-3 py-2 text-center">Versión {{ $version->version }}</td><td class="border px-3 py-2 text-center">{{ $version->created_at?->format('d/m/Y') ?? 'Sin fecha' }}</td><td class="border px-3 py-2 text-center">S/ {{ number_format((float)$version->total,2) }}</td><td class="border px-3 py-2 text-center">{{ $version->decision === 'aprobada' ? 'Aprobada' : ($version->decision === 'rechazada' ? 'Rechazada' : 'Pendiente') }}</td><td class="border px-3 py-2 text-center whitespace-pre-line">{{ $version->decision === 'rechazada' ? ($version->motivo_decision ?? 'Sin motivo') : '—' }}</td></tr>@endforeach</tbody></table>
@if ($proyecto->estado==='solicitado' && $propuesta_vigente && $propuesta_vigente->decision==='rechazada')
<div id="dis-aviso-rechazo" class="mt-3 rounded-[10px] border border-red-200 bg-red-50 p-3 text-sm text-red-700">La versión {{ $propuesta_vigente->version }} fue rechazada: {{ $propuesta_vigente->motivo_decision ?? 'Sin motivo' }}. Crea una nueva versión.</div>
@endif
@endif
@if ($propuesta_vigente)
<table class="mt-4 w-full border-collapse text-sm text-[#1d352d]"><thead><tr class="bg-[#edf8e7] text-[#3b5d2d]"><th scope="col" class="border px-3 py-2 text-center">Planta</th><th scope="col" class="border px-3 py-2 text-center">Cantidad</th><th scope="col" class="border px-3 py-2 text-center">Precio</th><th scope="col" class="border px-3 py-2 text-center">Subtotal</th></tr></thead><tbody>@foreach ($propuesta_vigente->plantas as $linea)<tr><td class="border px-3 py-2 text-center">{{ $linea->planta->nombre }}</td><td class="border px-3 py-2 text-center">{{ $linea->cantidad }}</td><td class="border px-3 py-2 text-center">S/ {{ number_format((float)$linea->precio_unitario,2) }}</td><td class="border px-3 py-2 text-center">S/ {{ number_format($linea->cantidad*(float)$linea->precio_unitario,2) }}</td></tr>@endforeach</tbody></table>
<p class="mt-3 text-[#0f3c2b]"><strong>Total:</strong> S/ {{ number_format((float)$propuesta_vigente->total,2) }}</p>
@endif
@if ($proyecto->estado==='solicitado')
<form method="POST" action="{{ route('paisajismo.proyectos.diseno.propuesta',$proyecto) }}" class="mt-4 rounded-[10px] border border-[#e4ebdf] bg-white p-5">
@csrf<label for="dis-concepto" class="block text-sm font-semibold text-[#0f3c2b]">Concepto</label><textarea id="dis-concepto" name="dis_concepto" rows="4" class="mt-1 w-full rounded-2xl border border-[#d8e2d7] bg-[#f8faf6] px-3 py-2">{{ old('dis_concepto') }}</textarea>
@for ($i=0;$i<10;$i++)
<div class="mt-3 grid gap-3 md:grid-cols-2"><div><label for="dis-planta-{{ $i }}" class="block text-sm text-[#0f3c2b]">Planta {{ $i+1 }}</label><select id="dis-planta-{{ $i }}" name="dis_lineas[{{ $i }}][planta_id]" class="mt-1 w-full rounded-2xl border border-[#d8e2d7] px-3 py-2"><option value="">Sin seleccionar</option>@foreach ($plantas_disponibles as $planta)<option value="{{ $planta->id }}" @selected((string)old('dis_lineas.'. $i .'.planta_id') === (string)$planta->id)>{{ $planta->nombre }}</option>@endforeach</select></div><div><label for="dis-cantidad-{{ $i }}" class="block text-sm text-[#0f3c2b]">Cantidad</label><input id="dis-cantidad-{{ $i }}" name="dis_lineas[{{ $i }}][cantidad]" type="number" min="1" max="1000" value="{{ old('dis_lineas.'. $i .'.cantidad') }}" class="mt-1 w-full rounded-2xl border border-[#d8e2d7] px-3 py-2"></div></div>
@endfor
<button type="submit" class="mt-4 rounded-full bg-[#7cb22b] px-5 py-2.5 text-sm font-semibold text-white">Guardar propuesta</button>
</form>
@endif
@if ($proyecto->estado==='propuesto' && $propuesta_vigente)
<form method="POST" action="{{ route('paisajismo.proyectos.diseno.decision',$proyecto) }}" class="mt-4 rounded-[10px] border border-[#e4ebdf] bg-white p-5">@csrf
<label for="dis-decision" class="block text-sm font-semibold text-[#0f3c2b]">Decisión</label><select id="dis-decision" name="dis_decision" class="mt-1 w-full rounded-2xl border border-[#d8e2d7] px-3 py-2"><option value="aprobada" @selected(old('dis_decision')==='aprobada')>Aprobada</option><option value="rechazada" @selected(old('dis_decision')==='rechazada')>Rechazada</option></select>
<label for="dis-motivo" class="mt-3 block text-sm font-semibold text-[#0f3c2b]">Motivo de rechazo</label><textarea id="dis-motivo" name="dis_motivo" rows="3" class="mt-1 w-full rounded-2xl border border-[#d8e2d7] px-3 py-2">{{ old('dis_motivo') }}</textarea>
<button type="submit" class="mt-3 rounded-full bg-[#7cb22b] px-5 py-2.5 text-sm font-semibold text-white">Guardar decisión</button>
</form>
@endif
</section>
