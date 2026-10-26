@extends('layouts.app')

@section('content')
    <main class="bg-[#f5f7f5] min-h-screen px-8 py-10">
        <div class="max-w-6xl mx-auto space-y-6">
            <div class="rounded-[20px] bg-white shadow-md border border-[#e7efe2] p-8">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-[#6a8f4e]">Almacén</p>
                <h1 class="mt-3 text-3xl font-bold text-[#103928]">Panel de recepción y control</h1>
                <p class="mt-4 text-[#5d6760]">
                    Desde aquí se puede revisar la recepción de materiales, validar conformidad y dejar seguimiento del inventario.
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-6">
                <div class="rounded-[20px] bg-white shadow-md border border-[#e7efe2] p-6">
                    <h2 class="text-xl font-bold text-[#103928]">Recepción</h2>
                    <p class="mt-3 text-[#5d6760]">Registrar la llegada de plantas, materiales o insumos.</p>
                    <a href="{{ route('almacen.recepcion') }}" class="mt-5 inline-block rounded-full bg-[#7cb22b] px-4 py-2 text-sm font-bold text-white">
                        Abrir recepción
                    </a>
                </div>

                <div class="rounded-[20px] bg-white shadow-md border border-[#e7efe2] p-6">
                    <h2 class="text-xl font-bold text-[#103928]">Conformidad</h2>
                    <p class="mt-3 text-[#5d6760]">Validar si la entrega coincide con lo solicitado y registrar diferencias.</p>
                    <span class="mt-5 inline-block rounded-full bg-[#edf5e4] px-4 py-2 text-sm font-bold text-[#3d5b24]">
                        Pendiente de mejora
                    </span>
                </div>

                <div class="rounded-[20px] bg-white shadow-md border border-[#e7efe2] p-6">
                    <h2 class="text-xl font-bold text-[#103928]">Seguimiento</h2>
                    <p class="mt-3 text-[#5d6760]">Mantener trazabilidad de la recepción y las incidencias del almacén.</p>
                    <span class="mt-5 inline-block rounded-full bg-[#edf5e4] px-4 py-2 text-sm font-bold text-[#3d5b24]">
                        Módulo en evolución
                    </span>
                </div>
            </div>
        </div>
    </main>
@endsection
