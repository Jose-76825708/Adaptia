@extends('layouts.app')

@section('content')
    <main class="bg-[#f5f7f5] min-h-screen px-8 py-10">
        <div class="max-w-6xl mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-[#6a8f4e]">Almacén</p>
                    <h1 class="mt-2 text-3xl font-bold text-[#103928]">Detalle de recepción</h1>
                </div>
                <a href="{{ route('recepciones.index') }}" class="rounded-full bg-[#e9ece9] px-5 py-3 text-sm font-bold text-[#103928]">
                    Volver
                </a>
            </div>

            <div class="rounded-[20px] bg-white shadow-md border border-[#e7efe2] p-6">
                <div class="grid md:grid-cols-3 gap-4 text-sm text-[#103928]">
                    <div>
                        <p class="text-[#5d6760]">Fecha</p>
                        <p class="mt-1 font-semibold">{{ $recepcion->fecha_recepcion->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-[#5d6760]">Tipo</p>
                        <p class="mt-1 font-semibold">{{ ucfirst($recepcion->tipo) }}</p>
                    </div>
                    <div>
                        <p class="text-[#5d6760]">Estado</p>
                        <p class="mt-1 font-semibold">{{ ucfirst(str_replace('_', ' ', $recepcion->estado)) }}</p>
                    </div>
                </div>

                <div class="mt-6">
                    <p class="text-sm font-semibold text-[#103928]">Observaciones</p>
                    <p class="mt-2 text-[#5d6760]">{{ $recepcion->observaciones ?: 'Sin observaciones' }}</p>
                </div>
            </div>

            <div class="rounded-[20px] bg-white shadow-md border border-[#e7efe2] p-6">
                <h2 class="text-xl font-bold text-[#103928]">Items</h2>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full text-left text-sm text-[#103928]">
                        <thead class="border-b border-[#e8ece7]">
                        <tr>
                            <th class="py-3 pr-4">Planta</th>
                            <th class="py-3 pr-4">Esperada</th>
                            <th class="py-3 pr-4">Recibida</th>
                            <th class="py-3 pr-4">Unidad</th>
                            <th class="py-3 pr-4">Estado</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($recepcion->detalles as $detalle)
                            <tr class="border-b border-[#f0f2ee]">
                                <td class="py-3 pr-4">{{ $detalle->planta->nombre ?? 'Sin nombre' }}</td>
                                <td class="py-3 pr-4">{{ $detalle->cantidad_esperada }}</td>
                                <td class="py-3 pr-4">{{ $detalle->cantidad_recibida }}</td>
                                <td class="py-3 pr-4">{{ $detalle->unidad }}</td>
                                <td class="py-3 pr-4">{{ ucfirst(str_replace('_', ' ', $detalle->estado_item)) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($recepcion->incidencias->isNotEmpty())
                <div class="rounded-[20px] bg-white shadow-md border border-[#e7efe2] p-6">
                    <h2 class="text-xl font-bold text-[#103928]">Incidencias</h2>
                    <ul class="mt-4 space-y-3">
                        @foreach ($recepcion->incidencias as $incidencia)
                            <li class="rounded-[12px] border border-[#f0d7d0] bg-[#fff4f1] p-4 text-sm text-[#6b352c]">
                                <p class="font-semibold">{{ $incidencia->motivo }} · {{ $incidencia->estado }}</p>
                                <p class="mt-1">Esperada: {{ $incidencia->cantidad_esperada }} · Recibida: {{ $incidencia->cantidad_recibida }} · Diferencia: {{ $incidencia->diferencia }}</p>
                                <p class="mt-1">{{ $incidencia->observaciones ?: 'Sin observaciones.' }}</p>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($recepcion->auditorias->isNotEmpty())
                <div class="rounded-[20px] bg-white shadow-md border border-[#e7efe2] p-6">
                    <h2 class="text-xl font-bold text-[#103928]">Historial</h2>
                    <ul class="mt-4 space-y-3">
                        @foreach ($recepcion->auditorias as $audit)
                            <li class="rounded-[12px] border border-[#e8ece7] bg-[#f9faf8] p-4 text-sm text-[#103928]">
                                <p class="font-semibold">{{ ucfirst($audit->accion) }} por {{ $audit->usuario->name ?? 'Sistema' }}</p>
                                <p class="mt-1">{{ $audit->detalle }}</p>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </main>
@endsection
