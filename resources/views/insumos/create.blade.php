<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    @vite('resources/css/app.css')
    <title>Crear Insumo</title>
</head>

<body class="flex font-sans h-screen">

    @include('partials.sidebar')

    <main class="flex flex-5 flex-col px-25 py-10 bg-[#fbfbfb] h-full gap-4 overflow-y-auto">

        <section>
            <a class="flex items-center text-[#0c251c] font-bold text-[0.8em]" href="{{ route('insumos.index') }}">
                <img class="w-[3%] h-auto" src="{{ asset('images/regreso-flecha.png') }}" alt="">Volver a Insumos
            </a>
        </section>

        <section class="flex items-center justify-between mb-8">
            <div class="flex-4">
                <h1 class="text-[#103928] font-bold text-[2.5em]">Crear Insumo</h1>
                <p class="text-[#8b8d8f] text-[1em]">Registra un nuevo insumo. El código se genera automáticamente.</p>
            </div>
        </section>

        <section class="flex justify-center pb-10">
            <div class="flex flex-col p-10 bg-[#fefdfe] rounded-[20px] shadow-xl w-full gap-8 border border-[#ecedea]">
                <form action="{{ route('insumos.store') }}" method="POST" class="flex flex-col gap-10">
                    @csrf

                    <div class="flex flex-col gap-6">
                        <div class="flex items-center gap-3 border-b border-[#ecedea] pb-2">
                            <div class="w-2 h-6 bg-[#629f22] rounded-full"></div>
                            <h2 class="text-[#103928] font-bold text-[1.3em]">Datos del Insumo</h2>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="flex flex-col gap-2">
                                <label for="nombre" class="text-[#304e42] font-semibold text-[1.1em]">Nombre del Insumo</label>
                                <input type="text" name="nombre" id="nombre" maxlength="150"
                                       class="p-4 bg-[#f3f5f3] border-2 @error('nombre') border-red-500 @else border-[#ecedea] @enderror rounded-[10px] outline-none focus:border-[#629f22] transition duration-300"
                                       placeholder="Ej: Abono orgánico" value="{{ old('nombre') }}" required>
                                @error('nombre') <span class="text-red-500 text-[0.9em] font-medium">{{ $message }}</span> @enderror
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="unidad" class="text-[#304e42] font-semibold text-[1.1em]">Unidad de medida</label>
                                <input type="text" name="unidad" id="unidad" maxlength="30" list="unidades"
                                       class="p-4 bg-[#f3f5f3] border-2 @error('unidad') border-red-500 @else border-[#ecedea] @enderror rounded-[10px] outline-none focus:border-[#629f22] transition duration-300"
                                       placeholder="Ej: kg, litro, saco, unidad" value="{{ old('unidad') }}" required>
                                <datalist id="unidades">
                                    <option value="kg"><option value="litro"><option value="saco"><option value="unidad"><option value="pieza"><option value="kit">
                                </datalist>
                                @error('unidad') <span class="text-red-500 text-[0.9em] font-medium">{{ $message }}</span> @enderror
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="stock_actual" class="text-[#304e42] font-semibold text-[1.1em]">Stock actual</label>
                                <input type="number" step="0.01" min="0" name="stock_actual" id="stock_actual"
                                       class="p-4 bg-[#f3f5f3] border-2 @error('stock_actual') border-red-500 @else border-[#ecedea] @enderror rounded-[10px] outline-none focus:border-[#629f22] transition duration-300"
                                       placeholder="0" value="{{ old('stock_actual') }}" required>
                                @error('stock_actual') <span class="text-red-500 text-[0.9em] font-medium">{{ $message }}</span> @enderror
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="stock_minimo" class="text-[#304e42] font-semibold text-[1.1em]">Stock mínimo</label>
                                <input type="number" step="0.01" min="0" name="stock_minimo" id="stock_minimo"
                                       class="p-4 bg-[#f3f5f3] border-2 @error('stock_minimo') border-red-500 @else border-[#ecedea] @enderror rounded-[10px] outline-none focus:border-[#629f22] transition duration-300"
                                       placeholder="0" value="{{ old('stock_minimo') }}" required>
                                @error('stock_minimo') <span class="text-red-500 text-[0.9em] font-medium">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                            class="py-3 px-8 bg-[#629f22] text-[#fbfbfb] rounded-[10px] font-bold hover:scale-105 transition duration-300">
                            Guardar insumo
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </main>
</body>

</html>
