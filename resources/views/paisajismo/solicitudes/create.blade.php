@extends('layouts.app')

@section('title','Solicitar proyecto')

@section('content')
<main class="mx-auto max-w-4xl px-4 py-10">
    <section class="rounded-[10px] border border-[#e4ebdf] bg-white p-6 shadow-sm">
        <h1 class="text-3xl font-bold text-[#0f3c2b]">Solicitar proyecto</h1>
        <p class="mt-2 text-sm text-[#5f6d5f]">Completa los datos del área que deseas transformar.</p>

        @if ($errors->any())
            <div id="pais-errores" class="mt-4 rounded-[10px] border border-red-200 bg-red-50 p-4 text-sm text-red-700" role="alert">
                @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('paisajismo.solicitudes.store') }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label for="pais-nombre" class="block text-sm font-semibold text-[#0f3c2b]">Nombre del proyecto</label>
                <input id="pais-nombre" name="nombre" type="text" value="{{ old('nombre') }}" class="mt-1 block w-full rounded-2xl border border-[#d8e2d7] bg-[#f8faf6] px-3 py-2.5 text-[#0f3c2b]">
            </div>
            <div>
                <label for="pais-ubicacion" class="block text-sm font-semibold text-[#0f3c2b]">Ubicación</label>
                <input id="pais-ubicacion" name="ubicacion" type="text" value="{{ old('ubicacion') }}" class="mt-1 block w-full rounded-2xl border border-[#d8e2d7] bg-[#f8faf6] px-3 py-2.5 text-[#0f3c2b]">
            </div>
            <div>
                <label for="pais-descripcion" class="block text-sm font-semibold text-[#0f3c2b]">Descripción</label>
                <textarea id="pais-descripcion" name="descripcion" rows="4" class="mt-1 block w-full rounded-2xl border border-[#d8e2d7] bg-[#f8faf6] px-3 py-2.5 text-[#0f3c2b]">{{ old('descripcion') }}</textarea>
            </div>
            <div class="grid gap-4 md:grid-cols-3">
                <div><label for="pais-area" class="block text-sm font-semibold text-[#0f3c2b]">Área m²</label><input id="pais-area" name="area_m2" type="text" value="{{ old('area_m2') }}" class="mt-1 block w-full rounded-2xl border border-[#d8e2d7] bg-[#f8faf6] px-3 py-2.5"></div>
                <div><label for="pais-presupuesto" class="block text-sm font-semibold text-[#0f3c2b]">Presupuesto</label><input id="pais-presupuesto" name="presupuesto_estimado" type="text" value="{{ old('presupuesto_estimado') }}" class="mt-1 block w-full rounded-2xl border border-[#d8e2d7] bg-[#f8faf6] px-3 py-2.5"></div>
                <div><label for="pais-fecha" class="block text-sm font-semibold text-[#0f3c2b]">Fecha deseada</label><input id="pais-fecha" name="fecha_deseada" type="date" value="{{ old('fecha_deseada') }}" class="mt-1 block w-full rounded-2xl border border-[#d8e2d7] bg-[#f8faf6] px-3 py-2.5"></div>
            </div>

            <div class="flex items-center justify-between gap-3 pt-2">
                <a href="{{ route('paisajismo.solicitudes.index') }}" class="rounded-full border border-[#dce6d7] px-5 py-2.5 text-sm font-semibold text-[#3b5b42]">Volver</a>
                <button type="submit" class="rounded-full bg-[#7cb22b] px-5 py-2.5 text-sm font-semibold text-white">Guardar solicitud</button>
            </div>
        </form>
    </section>
</main>
@endsection
