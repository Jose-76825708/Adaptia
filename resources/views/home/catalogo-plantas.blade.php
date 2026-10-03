@extends('layouts.app')

@section('title', 'Catálogo de Plantas - Adaptia')

@section('content')
<main class="w-full space-y-16 bg-[#f8faf6] px-4 py-8 sm:space-y-20 sm:px-8 sm:py-10 lg:px-12">
    <section class="reveal overflow-hidden rounded-[2rem] bg-gradient-to-br from-[#0f3c2b] via-[#18543a] to-[#2d7141] shadow-xl shadow-[#0f3c2b]/10">
        <div class="relative flex flex-col items-center px-6 py-10 text-center sm:px-10 sm:py-14">
            <div class="absolute -right-12 -top-16 h-56 w-56 rounded-full border-[32px] border-white/5"></div>
            <div class="absolute -bottom-24 -left-12 h-64 w-64 rounded-full border-[36px] border-white/5"></div>
            <div class="relative mb-5 flex h-20 w-20 items-center justify-center rounded-3xl bg-white/15 shadow-inner ring-1 ring-white/20 backdrop-blur sm:h-24 sm:w-24">
                <img class="h-12 w-12" src="{{ asset('images/plantas.png') }}" alt="">
            </div>
            <p class="relative mb-2 text-sm font-semibold uppercase tracking-[0.2em] text-[#d4e8ba]">Descubre tu próximo rincón verde</p>
            <h1 class="relative text-3xl font-bold tracking-tight text-white sm:text-4xl lg:text-5xl">
                Catálogo de plantas
            </h1>
            <p class="relative mt-3 max-w-2xl text-base leading-7 text-white/80 sm:text-lg">
                Explora las plantas disponibles y conoce sus características para encontrar las que mejor encajen contigo.
            </p>
        </div>
    </section>

    <section class="reveal">
        <div class="mb-7 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="mb-2 text-xs font-bold uppercase tracking-[0.18em] text-[#7a965f]">Conoce nuestras especies</p>
                <h2 class="text-2xl font-bold tracking-tight text-[#0f3c2b] sm:text-3xl">Plantas disponibles</h2>
            </div>
            <p class="rounded-full border border-[#e5ebdf] bg-white px-4 py-2 text-sm font-semibold text-[#52752d] shadow-sm">
                {{ $plantas->count() }} {{ $plantas->count() === 1 ? 'planta' : 'plantas' }}
            </p>
        </div>

        @if ($plantas->isNotEmpty())
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($plantas as $planta)
                    <article class="group flex h-full flex-col overflow-hidden rounded-3xl border border-[#e5ebdf] bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl">
                        <div class="relative flex h-56 items-center justify-center overflow-hidden bg-gradient-to-br from-[#f3f8ee] to-[#eaf2e3] p-6">
                            @if ($planta->tipoPlanta)
                                <span class="absolute left-4 top-4 rounded-full border border-white/80 bg-white/85 px-3 py-1 text-xs font-bold text-[#52752d] shadow-sm">
                                    {{ $planta->tipoPlanta->nombre }}
                                </span>
                            @endif
                            @if ($planta->imagen)
                                <img src="{{ asset('storage/' . $planta->imagen) }}"
                                     alt="{{ $planta->nombre }}"
                                     class="h-full max-h-44 w-3/4 object-contain transition duration-500 group-hover:scale-105">
                            @else
                                <img src="{{ asset('images/hoja_verde.png') }}"
                                     alt=""
                                     class="h-28 w-28 object-contain opacity-70">
                            @endif
                        </div>

                        <div class="flex flex-1 flex-col p-5 sm:p-6">
                            <div class="flex items-start justify-between gap-3">
                                <h3 class="text-xl font-bold text-[#0f3c2b]">{{ $planta->nombre }}</h3>
                                <span class="shrink-0 rounded-full bg-[#eff6e8] px-3 py-1.5 text-sm font-bold text-[#52752d]">
                                    S/ {{ number_format($planta->precio, 2) }}
                                </span>
                            </div>
                            <p class="mt-2 min-h-10 text-sm leading-5 text-[#718071]">
                                {{ $planta->descripcion ?: 'Conoce sus características y cuidados recomendados.' }}
                            </p>

                            <dl class="mt-5 grid grid-cols-2 gap-3 border-t border-[#edf1e9] pt-5">
                                <div class="rounded-xl bg-[#f7f9f5] p-3">
                                    <dt class="text-xs font-medium text-[#82907e]">Luz</dt>
                                    <dd class="mt-1 font-semibold text-[#34533b]">{{ ucfirst(str_replace('_', ' ', $planta->luz_requerida)) }}</dd>
                                </div>
                                <div class="rounded-xl bg-[#f7f9f5] p-3">
                                    <dt class="text-xs font-medium text-[#82907e]">Riego</dt>
                                    <dd class="mt-1 font-semibold text-[#34533b]">{{ ucfirst(str_replace('_', ' ', $planta->frecuencia_riego)) }}</dd>
                                </div>
                                <div class="rounded-xl bg-[#f7f9f5] p-3">
                                    <dt class="text-xs font-medium text-[#82907e]">Tamaño</dt>
                                    <dd class="mt-1 font-semibold text-[#34533b]">{{ ucfirst($planta->tamaño_adulto) }}</dd>
                                </div>
                                <div class="rounded-xl bg-[#f7f9f5] p-3">
                                    <dt class="text-xs font-medium text-[#82907e]">Cuidado</dt>
                                    <dd class="mt-1 font-semibold text-[#34533b]">{{ ucfirst($planta->nivel_cuidado) }}</dd>
                                </div>
                            </dl>

                            <div class="mt-4 flex flex-wrap items-center gap-2">
                                <span class="rounded-full border border-[#e5ebdf] px-3 py-1 text-xs font-medium text-[#63745f]">
                                    {{ ucfirst($planta->tipo_ambiente) }}
                                </span>
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $planta->toxicidad ? 'bg-[#fff2e8] text-[#a44a17]' : 'bg-[#eff6e8] text-[#52752d]' }}">
                                    {{ $planta->toxicidad ? 'Tóxica' : 'No tóxica' }}
                                </span>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="rounded-3xl border border-dashed border-[#ccd9c1] bg-white px-6 py-12 text-center">
                <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-2xl bg-[#eff6e8]">
                    <img class="h-9 w-9" src="{{ asset('images/plantas.png') }}" alt="">
                </div>
                <h3 class="text-xl font-bold text-[#0f3c2b]">Todavía no hay plantas en el catálogo</h3>
                <p class="mt-2 text-sm text-[#718071]">Vuelve pronto para conocer las plantas disponibles.</p>
            </div>
        @endif
    </section>
</main>
@endsection
