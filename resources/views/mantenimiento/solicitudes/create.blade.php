@extends('layouts.app')

@section('title', 'Solicitar mantenimiento')

@section('content')
    <main class="mx-auto max-w-3xl px-4 py-10">
        <div class="rounded-3xl border border-[#e4ebdf] bg-white p-6 shadow-sm sm:p-8">
            <div class="mb-6 flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#7a965f]">Mantenimiento</p>
                    <h1 class="mt-2 text-3xl font-bold text-[#0f3c2b]">Crear solicitud</h1>
                    <p class="mt-2 text-sm text-[#5d6d5e]">Describe el problema para abrir una solicitud de mantenimiento.</p>
                </div>
                <a href="{{ route('mantenimiento.solicitudes.index') }}" class="rounded-full border border-[#dce6d7] px-5 py-2.5 text-sm font-medium text-[#3b5b42] transition hover:bg-[#f1f7ee]">Volver</a>
            </div>

            @if ($errors->any())
                <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                    <ul class="list-disc space-y-1 pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('mantenimiento.solicitudes.store') }}" class="space-y-6">
                @csrf

                <div>
                    <label for="area_verde" class="mb-2 block text-sm font-semibold text-[#0f3c2b]">Área verde</label>
                    <input id="area_verde" name="area_verde" type="text" value="{{ old('area_verde') }}" class="w-full rounded-2xl border border-[#d8e2d7] bg-[#f8faf6] px-4 py-3 text-[#0f3c2b] focus:border-[#7cb22b] focus:outline-none" placeholder="Ej. Jardín principal">
                    @error('area_verde')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="descripcion" class="mb-2 block text-sm font-semibold text-[#0f3c2b]">Descripción</label>
                    <textarea id="descripcion" name="descripcion" rows="5" class="w-full rounded-2xl border border-[#d8e2d7] bg-[#f8faf6] px-4 py-3 text-[#0f3c2b] focus:border-[#7cb22b] focus:outline-none" placeholder="Describe el problema observando la planta o el área verde.">{{ old('descripcion') }}</textarea>
                    @error('descripcion')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="fecha_deseada" class="mb-2 block text-sm font-semibold text-[#0f3c2b]">Fecha deseada</label>
                    <input id="fecha_deseada" name="fecha_deseada" type="date" value="{{ old('fecha_deseada') }}" class="w-full rounded-2xl border border-[#d8e2d7] bg-[#f8faf6] px-4 py-3 text-[#0f3c2b] focus:border-[#7cb22b] focus:outline-none">
                    @error('fecha_deseada')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="observaciones" class="mb-2 block text-sm font-semibold text-[#0f3c2b]">Observaciones</label>
                    <textarea id="observaciones" name="observaciones" rows="4" class="w-full rounded-2xl border border-[#d8e2d7] bg-[#f8faf6] px-4 py-3 text-[#0f3c2b] focus:border-[#7cb22b] focus:outline-none" placeholder="Opcional. Añade detalles adicionales.">{{ old('observaciones') }}</textarea>
                    @error('observaciones')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('mantenimiento.solicitudes.index') }}" class="rounded-full border border-[#dce6d7] px-5 py-2.5 text-sm font-medium text-[#3b5b42] transition hover:bg-[#f1f7ee]">Cancelar</a>
                    <button type="submit" class="rounded-full bg-[#7cb22b] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#6ea225]">Guardar solicitud</button>
                </div>
            </form>
        </div>
    </main>
@endsection
