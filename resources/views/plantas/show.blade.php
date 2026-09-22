<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    @vite('resources/css/app.css')
    <title>Ficha Técnica - {{ $find->nombre }}</title>
</head>

<body class="flex font-sans h-screen">

    @include('partials.sidebar')

    <main class="flex flex-5 flex-col px-25 py-10 bg-[#fbfbfb] h-full gap-4 overflow-y-auto">

        <section>
            <a class="flex items-center text-[#0c251c] font-bold text-[0.8em]" href="{{ route('plantas.index') }}">
                <img class="w-[3%] h-auto" src="{{ asset('images/regreso-flecha.png') }}" alt="">Volver a Plantas
            </a>
        </section>

        <section class="flex items-center justify-between mb-8">
            <div class="flex-4">
                <h1 class="text-[#103928] font-bold text-[2.5em]">Ficha Técnica: {{ $find->nombre }}</h1>
                <p class="text-[#8b8d8f] text-[1em]">Detalles técnicos y requerimientos de cuidado de la planta.</p>
            </div>
            <div class="flex-1 flex gap-4">
                <a href="{{ route('plantas.edit', $find->id) }}"
                    class="flex items-center justify-center py-3 px-6 bg-[#629f22] text-[#fbfbfb] rounded-[10px] gap-3 hover:scale-110 transition duration-300 font-bold">
                    <img class="w-[1.2em] h-auto" src="{{ asset('images/editar.png') }}" alt="Editar">
                    Editar Planta
                </a>
            </div>
        </section>

        <section class="flex justify-center pb-10">
            <div class="flex flex-col p-10 bg-[#fefdfe] rounded-[20px] shadow-xl w-full max-w-5xl gap-8 border border-[#ecedea]">

                <!-- CABECERA DE LA FICHA -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 border-b border-[#ecedea] pb-6">
                    <div>
                        <h2 class="text-[#103928] text-[2em] font-bold">{{ $find->nombre }}</h2>
                        <p class="text-[#629f22] text-[1.2em] font-semibold">{{ $find->tipoPlanta->nombre }}</p>
                    </div>
                    <div class="flex flex-col items-end gap-2">
                        <span class="text-[#8b8d8f] text-[1em]">Precio de Venta</span>
                        <span class="text-[#103928] text-[2em] font-bold">S/. {{ number_format($find->precio, 2) }}</span>
                    </div>
                </div>

                <!-- CUADRÍCULA DE INFORMACIÓN -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

                    <!-- COLUMNA 1: CUIDADO -->
                    <div class="flex flex-col gap-6 p-6 bg-[#f3f5f3] rounded-[15px] border border-[#ecedea]">
                        <h3 class="text-[#103928] font-bold text-[1.3em] flex items-center gap-2">
                            <img class="w-6 h-auto" src="{{ asset('images/cuidados.png') }}" alt="Cuidado">
                            Cuidado y Luz
                        </h3>
                        <div class="flex flex-col gap-4">
                            <div class="flex justify-between border-b border-[#ecedea] pb-2">
                                <span class="text-[#8b8d8f]">Luz:</span>
                                <span class="text-[#304e42] font-bold">{{ ucfirst($find->luz_requerida) }}</span>
                            </div>
                            <div class="flex justify-between border-b border-[#ecedea] pb-2">
                                <span class="text-[#8b8d8f]">Riego:</span>
                                <span class="text-[#304e42] font-bold">{{ ucfirst($find->frecuencia_riego) }}</span>
                            </div>
                            <div class="flex justify-between border-b border-[#ecedea] pb-2">
                                <span class="text-[#8b8d8f]">Nivel:</span>
                                <span class="text-[#304e42] font-bold">{{ ucfirst($find->nivel_cuidado) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- COLUMNA 2: AMBIENTE -->
                    <div class="flex flex-col gap-6 p-6 bg-[#f3f5f3] rounded-[15px] border border-[#ecedea]">
                        <h3 class="text-[#103928] font-bold text-[1.3em] flex items-center gap-2">
                            <img class="w-6 h-auto" src="{{ asset('images/ambiente.png') }}" alt="Ambiente">
                            Ubicación
                        </h3>
                        <div class="flex flex-col gap-4">
                            <div class="flex justify-between border-b border-[#ecedea] pb-2">
                                <span class="text-[#8b8d8f]">Ambiente:</span>
                                <span class="text-[#304e42] font-bold">{{ ucfirst($find->tipo_ambiente) }}</span>
                            </div>
                            <div class="flex justify-between border-b border-[#ecedea] pb-2">
                                <span class="text-[#8b8d8f]">Espacio:</span>
                                <span class="text-[#304e42] font-bold">{{ ucfirst($find->espacio_requerido) }}</span>
                            </div>
                            <div class="flex justify-between border-b border-[#ecedea] pb-2">
                                <span class="text-[#8b8d8f]">Estética:</span>
                                <span class="text-[#304e42] font-bold">{{ ucfirst($find->estetica) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- COLUMNA 3: ESTADO Y ALERTAS -->
                    <div class="flex flex-col gap-6 p-6 bg-[#f3f5f3] rounded-[15px] border border-[#ecedea]">
                        <h3 class="text-[#103928] font-bold text-[1.3em] flex items-center gap-2">
                            <img class="w-6 h-auto" src="{{ asset('images/stock.png') }}" alt="Stock">
                            Inventario
                        </h3>
                        <div class="flex flex-col gap-4">
                            <div class="flex justify-between border-b border-[#ecedea] pb-2">
                                <span class="text-[#8b8d8f]">Stock Actual:</span>
                                <span class="text-[#304e42] font-bold">{{ $find->stock_actual }} und.</span>
                            </div>
                            <div class="flex justify-between border-b border-[#ecedea] pb-2">
                                <span class="text-[#8b8d8f]">Stock Mínimo:</span>
                                <span class="text-[#304e42] font-bold">{{ $find->stock_minimo }} und.</span>
                            </div>
                            <div class="flex justify-between border-b border-[#ecedea] pb-2">
                                <span class="text-[#8b8d8f]">Tóxica:</span>
                                <span class="text-[#304e42] font-bold {{ $find->es_toxica ? 'text-red-500' : 'text-green-600' }}">
                                    {{ $find->es_toxica ? 'Sí' : 'No' }}
                                </span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

    </main>

</body>

</html>
