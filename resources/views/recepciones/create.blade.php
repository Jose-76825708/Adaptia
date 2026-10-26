@extends('layouts.app')

@section('content')
    <main class="bg-[#f5f7f5] min-h-screen px-8 py-10">
        <div class="max-w-5xl mx-auto space-y-6">
            <div class="rounded-[20px] bg-white shadow-md border border-[#e7efe2] p-8">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-[#6a8f4e]">Almacén</p>
                <h1 class="mt-3 text-3xl font-bold text-[#103928]">Registrar recepción</h1>
            </div>

            <div class="rounded-[20px] bg-white shadow-md border border-[#e7efe2] p-8">
                <form action="{{ route('recepciones.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <input type="hidden" name="usuario_almacen_id" value="{{ auth()->id() }}">

                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-[#103928] mb-2">Tipo</label>
                            <select name="tipo" class="w-full rounded-[12px] border border-[#dfe7dc] bg-[#f9faf8] p-3 text-[#103928]">
                                <option value="compra">Compra</option>
                                <option value="entrega">Entrega</option>
                                <option value="devolucion">Devolución</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-[#103928] mb-2">Estado</label>
                            <select name="estado" class="w-full rounded-[12px] border border-[#dfe7dc] bg-[#f9faf8] p-3 text-[#103928]">
                                <option value="pendiente">Pendiente</option>
                                <option value="conforme">Conforme</option>
                                <option value="no_conforme">No conforme</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-[#103928] mb-2">Fecha de recepción</label>
                        <input type="datetime-local" name="fecha_recepcion" value="{{ now()->format('Y-m-d\TH:i') }}" class="w-full rounded-[12px] border border-[#dfe7dc] bg-[#f9faf8] p-3 text-[#103928]">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-[#103928] mb-2">Observaciones</label>
                        <textarea name="observaciones" rows="4" class="w-full rounded-[12px] border border-[#dfe7dc] bg-[#f9faf8] p-3 text-[#103928]" placeholder="Ejemplo: entrega con desgaste menor, ausencia de un material..."></textarea>
                    </div>

                    <div class="rounded-[14px] border border-dashed border-[#bfd3ae] bg-[#f4f9f0] p-5 space-y-4">
                        <p class="text-sm font-semibold text-[#103928]">Detalle de recepción</p>

                        <div class="grid md:grid-cols-5 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-[#103928] mb-2">Planta</label>
                                <select name="detalles[0][planta_id]" class="w-full rounded-[10px] border border-[#dfe7dc] bg-white p-3 text-[#103928]">
                                    @foreach (\App\Models\Planta::orderBy('nombre')->get() as $planta)
                                        <option value="{{ $planta->id }}">{{ $planta->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-[#103928] mb-2">Esperada</label>
                                <input type="number" name="detalles[0][cantidad_esperada]" min="1" value="1" class="w-full rounded-[10px] border border-[#dfe7dc] bg-white p-3 text-[#103928]">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-[#103928] mb-2">Recibida</label>
                                <input type="number" name="detalles[0][cantidad_recibida]" min="0" value="1" class="w-full rounded-[10px] border border-[#dfe7dc] bg-white p-3 text-[#103928]">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-[#103928] mb-2">Unidad</label>
                                <input type="text" name="detalles[0][unidad]" value="unidad" class="w-full rounded-[10px] border border-[#dfe7dc] bg-white p-3 text-[#103928]">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-[#103928] mb-2">Estado</label>
                                <select name="detalles[0][estado_item]" class="w-full rounded-[10px] border border-[#dfe7dc] bg-white p-3 text-[#103928]">
                                    <option value="conforme">Conforme</option>
                                    <option value="no_conforme">No conforme</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-4">
                        <a href="{{ route('almacen.index') }}" class="rounded-full bg-[#e9ece9] px-5 py-3 text-sm font-bold text-[#103928]">
                            Volver
                        </a>
                        <button type="submit" class="rounded-full bg-[#7cb22b] px-5 py-3 text-sm font-bold text-white">
                            Guardar recepción
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
@endsection
