@extends('layouts.app')

@section('content')
    <main class="bg-[#f5f7f5] min-h-screen px-8 py-10">
        <div class="max-w-6xl mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-[#6a8f4e]">Almacén</p>
                    <h1 class="mt-2 text-3xl font-bold text-[#103928]">Recepciones</h1>
                </div>
                <a href="{{ route('recepciones.create') }}" class="rounded-full bg-[#7cb22b] px-5 py-3 text-sm font-bold text-white">
                    Nueva recepción
                </a>
            </div>

            @if (session('success'))
                <div class="rounded-[12px] border border-[#c7e2b9] bg-[#edf7e8] p-4 text-[#285d29]">
                    {{ session('success') }}
                </div>
            @endif

            <div class="rounded-[20px] bg-white shadow-md border border-[#e7efe2] p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm text-[#103928]">
                        <thead class="border-b border-[#e8ece7]">
                        <tr>
                            <th class="py-3 pr-4">Fecha</th>
                            <th class="py-3 pr-4">Tipo</th>
                            <th class="py-3 pr-4">Responsable</th>
                            <th class="py-3 pr-4">Estado</th>
                            <th class="py-3 pr-4">Observación</th>
                            <th class="py-3 pr-4 text-right">Acción</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($recepciones as $recepcion)
                            <tr class="border-b border-[#f0f2ee]">
                                <td class="py-3 pr-4">{{ $recepcion->fecha_recepcion->format('d/m/Y') }}</td>
                                <td class="py-3 pr-4">{{ ucfirst($recepcion->tipo) }}</td>
                                <td class="py-3 pr-4">{{ $recepcion->almacenista->name ?? 'Sin responsable' }}</td>
                                <td class="py-3 pr-4">
                                    <span class="rounded-full bg-[#edf5e4] px-2 py-1 text-xs font-bold text-[#3d5b24]">
                                        {{ $recepcion->estado }}
                                    </span>
                                </td>
                                <td class="py-3 pr-4">{{ $recepcion->observaciones ?: 'Sin observaciones' }}</td>
                                <td class="py-3 pr-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('recepciones.show', $recepcion) }}" class="rounded-full bg-[#e9ece9] px-3 py-2 text-xs font-bold text-[#103928]">
                                            Ver
                                        </a>
                                        @if ($recepcion->estado === 'pendiente')
                                            <form action="{{ route('recepciones.aprobar', $recepcion) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="rounded-full bg-[#7cb22b] px-3 py-2 text-xs font-bold text-white">
                                                    Aprobar
                                                </button>
                                            </form>
                                            <form action="{{ route('recepciones.rechazar', $recepcion) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="rounded-full bg-[#d85f4a] px-3 py-2 text-xs font-bold text-white">
                                                    Rechazar
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-xs text-[#5d6760]">Cerrada</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-6 text-center text-[#5d6760]">No hay recepciones registradas.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
@endsection
