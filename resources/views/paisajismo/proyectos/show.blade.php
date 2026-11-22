<!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><link rel="icon" href="{{ asset('images/logo.png') }}">@vite(['resources/css/app.css','resources/js/app.js'])<title>Detalle paisajismo</title></head>
<body class="flex h-screen font-sans">@include('partials.sidebar')
<main class="flex flex-5 flex-col gap-4 overflow-y-auto bg-[#fbfbfb] p-8">
<section class="flex items-center justify-between"><div><p class="text-xs font-bold uppercase tracking-[0.18em] text-[#7a965f]">Código</p><h1 class="mt-2 text-2xl font-bold text-[#0f3c2b]">{{ $proyecto->codigo }}</h1></div><a href="{{ route('paisajismo.proyectos.index') }}" class="rounded-[10px] border border-[#dce6d7] bg-white px-5 py-2.5 text-sm text-[#3b5b42]">Volver</a></section>
<section class="rounded-[10px] border border-[#e4ebdf] bg-white p-6">
<div class="grid gap-4 md:grid-cols-2"><div><p class="text-xs font-bold text-[#7a965f]">Nombre</p><p class="mt-1 text-lg font-semibold text-[#0f3c2b]">{{ $proyecto->nombre }}</p></div><div><p class="text-xs font-bold text-[#7a965f]">Estado</p><p class="mt-1 text-lg font-semibold text-[#0f3c2b]">{{ ucfirst(str_replace('_',' ', $proyecto->estado)) }}</p></div><div><p class="text-xs font-bold text-[#7a965f]">Ubicación</p><p class="mt-1 text-[#1d352d]">{{ $proyecto->ubicacion }}</p></div><div><p class="text-xs font-bold text-[#7a965f]">Área</p><p class="mt-1 text-[#1d352d]">{{ $proyecto->area_m2 }} m²</p></div></div>
</section>
<section class="mt-6 space-y-4">
@includeIf('paisajismo.proyectos.paneles.diseno',['proyecto'=>$proyecto])
@includeIf('paisajismo.proyectos.paneles.instalacion',['proyecto'=>$proyecto])
@includeIf('paisajismo.proyectos.paneles.entrega',['proyecto'=>$proyecto])
</section>
</main></body></html>
