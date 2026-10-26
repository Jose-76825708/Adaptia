@extends('layouts.app')

@section('content')
    <main class="bg-[#f5f7f5] min-h-screen px-8 py-10">
        <div class="max-w-5xl mx-auto space-y-6">
            <div class="rounded-[20px] bg-white shadow-md border border-[#e7efe2] p-8">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-[#6a8f4e]">Almacén</p>
                <h1 class="mt-3 text-3xl font-bold text-[#103928]">Recepción de materiales</h1>
                <p class="mt-4 text-[#5d6760]">
                    Registro base para la recepción de plantas, materiales o insumos. Esta pantalla complementa el inventario existente sin cambiar su flujo original.
                </p>
            </div>

            <div class="rounded-[20px] bg-white shadow-md border border-[#e7efe2] p-8">
                <form class="space-y-6">
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-[#103928] mb-2">Tipo de recepción</label>
                            <select class="w-full rounded-[12px] border border-[#dfe7dc] bg-[#f9faf8] p-3 text-[#103928]">
                                <option>Compra</option>
                                <option>Entrega interna</option>
                                <option>Devolución</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-[#103928] mb-2">Fecha</label>
                            <input type="date" class="w-full rounded-[12px] border border-[#dfe7dc] bg-[#f9faf8] p-3 text-[#103928]" value="{{ date('Y-m-d') }}">
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-[#103928] mb-2">Proveedor / origen</label>
                            <input type="text" placeholder="Nombre del proveedor u origen" class="w-full rounded-[12px] border border-[#dfe7dc] bg-[#f9faf8] p-3 text-[#103928]">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-[#103928] mb-2">Responsable</label>
                            <input type="text" value="{{ auth()->user()->name ?? 'Almacén' }}" class="w-full rounded-[12px] border border-[#dfe7dc] bg-[#f9faf8] p-3 text-[#103928]" readonly>
                        </div>
                    </div>

                    <div class="rounded-[14px] border border-dashed border-[#bfd3ae] bg-[#f4f9f0] p-5">
                        <p class="text-sm font-semibold text-[#103928]">Detalle de recepción</p>
                        <div class="mt-4 grid md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-[#103928] mb-2">Planta / material</label>
                                <input type="text" placeholder="Nombre" class="w-full rounded-[10px] border border-[#dfe7dc] bg-white p-3 text-[#103928]">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-[#103928] mb-2">Cantidad esperada</label>
                                <input type="number" min="1" value="1" class="w-full rounded-[10px] border border-[#dfe7dc] bg-white p-3 text-[#103928]">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-[#103928] mb-2">Cantidad recibida</label>
                                <input type="number" min="0" value="0" class="w-full rounded-[10px] border border-[#dfe7dc] bg-white p-3 text-[#103928]">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-[#103928] mb-2">Estado</label>
                                <select class="w-full rounded-[10px] border border-[#dfe7dc] bg-white p-3 text-[#103928]">
                                    <option>Conforme</option>
                                    <option>No conforme</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-[#103928] mb-2">Observaciones</label>
                        <textarea rows="4" class="w-full rounded-[12px] border border-[#dfe7dc] bg-[#f9faf8] p-3 text-[#103928]" placeholder="Registre observaciones, diferencias o incidencias..."></textarea>
                    </div>

                    <div class="flex justify-end gap-4">
                        <a href="{{ route('almacen.index') }}" class="rounded-full bg-[#e9ece9] px-5 py-3 text-sm font-bold text-[#103928]">
                            Volver
                        </a>
                        <button type="button" class="rounded-full bg-[#7cb22b] px-5 py-3 text-sm font-bold text-white">
                            Guardar recepción
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
@endsection
