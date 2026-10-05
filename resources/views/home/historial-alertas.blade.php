@extends('layouts.app')

@section('title', 'Historial y Alertas - Adaptia')

@section('content')
<main class="w-full space-y-10 bg-[#f8faf6] px-4 py-8 sm:px-8 sm:py-10 lg:px-12">
    <section class="overflow-hidden rounded-[2rem] bg-gradient-to-br from-[#0f3c2b] via-[#18543a] to-[#2d7141] shadow-xl shadow-[#0f3c2b]/10">
        <div class="relative flex flex-col items-center px-6 py-10 text-center sm:px-10 sm:py-14">
            <div class="absolute -right-12 -top-16 h-56 w-56 rounded-full border-[32px] border-white/5"></div>
            <div class="absolute -bottom-24 -left-12 h-64 w-64 rounded-full border-[36px] border-white/5"></div>
            <div class="relative mb-5 flex h-20 w-20 items-center justify-center rounded-3xl bg-white/15 shadow-inner ring-1 ring-white/20 backdrop-blur">
                <img class="h-11 w-11" src="{{ asset('images/logo.png') }}" alt="">
            </div>
            <p class="relative mb-2 text-sm font-semibold uppercase tracking-[0.2em] text-[#d4e8ba]">Cuidado conectado</p>
            <h1 class="relative text-3xl font-bold tracking-tight text-white sm:text-4xl lg:text-5xl">Historial y alertas</h1>
            <p class="relative mt-3 max-w-2xl text-base leading-7 text-white/80 sm:text-lg">
                Revisa las mediciones históricas y el estado de las alertas de tus plantas monitoreadas.
            </p>
            <p class="relative mt-2 max-w-2xl text-sm leading-6 text-white/65">
                Las tendencias muestran hasta las últimas 30 lecturas y el historial lista hasta 50 alertas por planta.
            </p>
        </div>
    </section>

    @include('home.partials.historial-monitoreo', ['sensoresAsignados' => $sensoresAsignados])

    <div class="flex justify-center">
        <a href="{{ route('home') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-[#7cb22b] px-5 py-2.5 font-bold text-white transition hover:-translate-y-0.5 hover:bg-[#6eab26]">
            Volver al inicio
        </a>
    </div>
</main>
@endsection
