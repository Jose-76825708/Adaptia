@extends('layouts.app')

@section('title', 'Mis solicitudes')

@section('content')
    <main class="mx-auto max-w-6xl px-4 py-10">
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#7a965f]">Mantenimiento</p>
                <h1 class="mt-2 text-3xl font-bold text-[#0f3c2b]">Mis solicitudes</h1>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('home') }}" class="rounded-full border border-[#dce6d7] px-5 py-2.5 text-sm font-medium text-[#3b5b42] transition hover:bg-[#f1f7ee]">Volver</a>
                <a href="{{ route('mantenimiento.solicitudes.create') }}" class="rounded-full bg-[#7cb22b] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#6ea225]">Solicitar mantenimiento</a>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-6 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if ($casos->isEmpty())
            <div class="rounded-3xl border border-dashed border-[#cfe0c2] bg-[#f8faf6] px-6 py-12 text-center">
                <h2 class="text-xl font-bold text-[#0f3c2b]">No tienes solicitudes de mantenimiento</h2>
                <p class="mt-2 text-sm text-[#5f6d5f]">Aún no has abierto ninguna solicitud para tu área verde.</p>
                <a href="{{ route('mantenimiento.solicitudes.create') }}" class="mt-5 inline-flex rounded-full bg-[#7cb22b] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#6ea225]">Solicitar mantenimiento</a>
            </div>
        @else
            <div class="overflow-hidden rounded-3xl border border-[#e4ebdf] bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm text-[#1d352d]">
                        <thead class="bg-[#f3f8ef] text-[#4c5d4f]">
                            <tr>
                                <th class="px-4 py-3 font-semibold">Código</th>
                                <th class="px-4 py-3 font-semibold">Área verde</th>
                                <th class="px-4 py-3 font-semibold">Tipo</th>
                                <th class="px-4 py-3 font-semibold">Estado</th>
                                <th class="px-4 py-3 font-semibold">Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($casos as $caso)
                                <tr class="border-t border-[#edf3eb]">
                                    <td class="px-4 py-3 font-semibold text-[#0f3c2b]">{{ $caso->codigo }}</td>
                                    <td class="px-4 py-3">{{ $caso->area_verde }}</td>
                                    <td class="px-4 py-3">{{ str_replace('_', ' ', ucfirst($caso->tipo_mantenimiento)) }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex rounded-full bg-[#edf8e7] px-2.5 py-1 text-xs font-semibold text-[#3b5d2d]">
                                            {{ str_replace('_', ' ', ucfirst($caso->estado)) }}
                                        </span>
                                        @if ($caso->estado === 'cerrado')
                                            <a href="{{ route('mantenimiento.casos.informe', $caso) }}" class="ml-2 text-sm font-semibold text-[#2d5f3a] underline">Ver informe</a>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">{{ $caso->created_at->format('d/m/Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($casos->hasPages())
                <div class="mt-6">
                    {{ $casos->links() }}
                </div>
            @endif
        @endif
    </main>
@endsection
