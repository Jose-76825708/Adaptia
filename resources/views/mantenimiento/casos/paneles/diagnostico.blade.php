<section id="diag-panel" class="rounded-[10px] border border-[#e4ebdf] bg-[#f8faf6] p-5 shadow-sm" aria-labelledby="diag-titulo">
    <h2 id="diag-titulo" class="text-xl font-bold text-[#103928]">Diagnóstico</h2>

    @if ($errors->diagnostico->any())
        <div id="diag-errores" class="mt-3 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700" role="alert">
            @foreach ($errors->diagnostico->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('mantenimiento.casos.diagnostico.guardar', $caso) }}" enctype="multipart/form-data" class="mt-4 rounded-[10px] border border-[#e4ebdf] bg-white p-5">
        @csrf

        <label for="diag-nota-contenido" class="block text-sm font-semibold text-[#0f3c2b]">Nota de diagnóstico</label>
        <textarea id="diag-nota-contenido" name="contenido" rows="3" class="mt-1 block w-full rounded-2xl border border-[#d8e2d7] bg-[#f8faf6] px-3 py-2.5 text-[#0f3c2b] focus:border-[#7cb22b] focus:outline-none focus:ring-2 focus:ring-[#e4ebdf]"></textarea>

        <label for="diag-foto-archivo" class="mt-4 block text-sm font-semibold text-[#0f3c2b]">Foto de evidencia</label>
        <input id="diag-foto-archivo" name="archivo" type="file" accept="image/jpeg,image/png,image/webp" class="mt-1 block w-full rounded-2xl border border-[#d8e2d7] bg-[#f8faf6] px-3 py-2.5 text-sm text-[#0f3c2b] file:mr-3 file:rounded-full file:border-0 file:bg-[#edf8e7] file:px-3 file:py-2 file:text-[#3b5d2d]">

        <label for="diag-foto-descripcion" class="mt-4 block text-sm font-semibold text-[#0f3c2b]">Descripción de la foto</label>
        <input id="diag-foto-descripcion" name="descripcion" type="text" class="mt-1 block w-full rounded-2xl border border-[#d8e2d7] bg-[#f8faf6] px-3 py-2.5 text-[#0f3c2b] focus:border-[#7cb22b] focus:outline-none focus:ring-2 focus:ring-[#e4ebdf]">

        <button type="submit" class="mt-4 rounded-full bg-[#7cb22b] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#6eab26] focus:outline-none focus:ring-2 focus:ring-[#cfe0c2]">Guardar diagnóstico</button>
    </form>

    @if (session('success'))
        <div id="diag-guardado" class="mt-4 rounded-[10px] border border-[#cfe0c2] bg-[#edf8e7] p-4 text-sm font-semibold text-[#3b5d2d]" role="status">
            Diagnóstico guardado correctamente.
        </div>
    @endif

    @php($evidencias = $caso->evidencias()->latest('created_at')->latest('id')->get())
    @if ($evidencias->isNotEmpty())
        <div id="diag-evidencia" class="mt-4 rounded-[10px] border border-[#e4ebdf] bg-white p-5">
            <h3 class="text-lg font-bold text-[#103928]">Evidencia registrada</h3>
            @foreach ($evidencias as $evidencia)
                @if ($evidencia->tipo === 'foto')
                    <div id="diag-foto-contenedor-{{ $evidencia->id }}" class="mt-3 rounded-[10px] border border-[#e4ebdf] bg-[#f8faf6] p-3">
                        <a href="{{ asset('storage/' . $evidencia->ruta) }}" target="_blank" rel="noopener" class="block" title="Ampliar foto">
                            <img id="diag-foto-{{ $evidencia->id }}" src="{{ asset('storage/' . $evidencia->ruta) }}" alt="{{ $evidencia->descripcion ?: 'Evidencia fotográfica del caso' }}" class="h-48 w-full max-w-[20rem] object-contain rounded-[10px] bg-[#f8faf6] border border-[#e4ebdf]">
                        </a>
                        <form method="POST" action="{{ route('mantenimiento.casos.diagnostico.fotos.eliminar', ['caso' => $caso, 'evidencia' => $evidencia]) }}" class="mt-3">
                            @csrf
                            <button type="submit" class="rounded-lg border border-red-300 bg-red-50 px-3 py-2 text-sm font-semibold text-red-700 shadow-sm transition hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-300">Eliminar foto</button>
                        </form>
                    </div>
                @else
                    <p class="mt-2 whitespace-pre-line">{{ $evidencia->contenido }}</p>
                @endif
            @endforeach
        </div>
    @endif
</section>
