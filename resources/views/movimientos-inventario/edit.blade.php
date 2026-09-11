<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    @vite('resources/css/app.css')
    <title>Editar Movimiento de Inventario</title>
</head>

<body class="flex font-sans h-screen">

    @include('partials.sidebar')

    <main class="flex flex-5 flex-col px-25 py-10 bg-[#fbfbfb] h-full gap-4 overflow-y-auto">

        <section>
            <a class="flex items-center text-[#0c251c] font-bold text-[0.8em]" href="{{ route('movimientos-inventario.index') }}">
                <img class="w-[3%] h-auto" src="{{ asset('images/regreso-flecha.png') }}" alt="">Volver a Movimientos de Inventario
            </a>
        </section>

        <section class="flex items-center justify-between mb-8">
            <div class="flex-4">
                <h1 class="text-[#103928] font-bold text-[2.5em]">Editar Movimiento de Inventario</h1>
                <p class="text-[#8b8d8f] text-[1em]">Actualiza los datos de un movimiento de inventario existente.</p>
            </div>
        </section>

        <section class="flex justify-center pb-10">
            <div class="flex flex-col p-10 bg-[#fefdfe] rounded-[20px] shadow-xl w-full gap-8 border border-[#ecedea]">
                <form action="{{ route('movimientos-inventario.update', $movimiento->id) }}" method="POST" class="flex flex-col gap-10">
                    @csrf
                    @method('PUT')

                    <!-- SECCIÓN: DATOS DEL MOVIMIENTO -->
                    <div class="flex flex-col gap-6">
                        <div class="flex items-center gap-3 border-b border-[#ecedea] pb-2">
                            <div class="w-2 h-6 bg-[#629f22] rounded-full"></div>
                            <h2 class="text-[#103928] font-bold text-[1.3em]">Datos del Movimiento</h2>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="flex flex-col gap-2">
                                <label for="planta_id" class="text-[#304e42] font-semibold text-[1.1em]">Planta</label>
                                <select name="planta_id" id="planta_id"
                                        class="p-4 bg-[#f3f5f3] border-2 @error('planta_id') border-red-500 @else border-[#ecedea] @enderror rounded-[10px] outline-none focus:border-[#629f22] transition duration-300" required>
                                    <option value="" disabled selected>Seleccione una planta...</option>
                                    @foreach($plantas as $planta)
                                        <option value="{{ $planta->id }}" {{ old('planta_id', $movimiento->planta_id) == $planta->id ? 'selected' : '' }}>
                                            {{ $planta->nombre }} (Stock: {{ $planta->stock_actual ?? 0 }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('planta_id') <span class="text-red-500 text-[0.9em] font-medium">{{ $message }}</span> @enderror
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="tipo" class="text-[#304e42] font-semibold text-[1.1em]">Tipo de Movimiento</label>
                                <div class="flex space-x-4">
                                    <div class="flex items-center gap-2">
                                        <input type="radio" name="tipo" id="tipo_entrada" value="entrada"
                                               class="w-4 h-4 text-[#629f22]" {{ old('tipo', $movimiento->tipo) == 'entrada' ? 'checked' : '' }}>
                                        <label for="tipo_entrada" class="text-[#304e42] font-semibold text-[1.1em] cursor-pointer">Entrada</label>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <input type="radio" name="tipo" id="tipo_salida" value="salida"
                                               class="w-4 h-4 text-[#629f22]" {{ old('tipo', $movimiento->tipo) == 'salida' ? 'checked' : '' }}>
                                        <label for="tipo_salida" class="text-[#304e42] font-semibold text-[1.1em] cursor-pointer">Salida</label>
                                    </div>
                                </div>
                                @error('tipo') <span class="text-red-500 text-[0.9em] font-medium">{{ $message }}</span> @enderror
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="cantidad" class="text-[#304e42] font-semibold text-[1.1em]">Cantidad</label>
                                <input type="number" name="cantidad" id="cantidad"
                                       min="1"
                                       class="p-4 bg-[#f3f5f3] border-2 @error('cantidad') border-red-500 @else border-[#ecedea] @enderror rounded-[10px] outline-none focus:border-[#629f22] transition duration-300"
                                       placeholder="Ej: 5" value="{{ old('cantidad', $movimiento->cantidad) }}" required>
                                @error('cantidad') <span class="text-red-500 text-[0.9em] font-medium">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end mt-6">
                        <button type="submit"
                                class="flex items-center justify-between py-4 px-10 bg-[#629f22] text-[#fbfbfb] rounded-[10px] gap-3 hover:scale-110 transition duration-300 font-bold text-[1.1em]">
                            <img class="w-[1.2em] h-auto" src="{{ asset('images/anadir.png') }}" alt="Actualizar">
                            Actualizar Movimiento
                        </button>
                    </div>
                </form>
            </div>
        </section>

    </main>

</body>

</html>