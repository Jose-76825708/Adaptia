<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    @vite('resources/css/app.css')
    <title>Editar Planta</title>
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
                <h1 class="text-[#103928] font-bold text-[2.5em]">Editar Planta</h1>
                <p class="text-[#8b8d8f] text-[1em]">Modifica los datos y requerimientos de la planta seleccionada.</p>
            </div>
        </section>

        <section class="flex justify-center pb-10">
            <div class="flex flex-col p-10 bg-[#fefdfe] rounded-[20px] shadow-xl w-full gap-8 border border-[#ecedea]">
                <form action="{{ route('plantas.update', $find->id) }}" method="POST" class="flex flex-col gap-10">
                    @csrf
                    @method('PUT')

                    <!-- SECCIÓN 1: INFORMACIÓN GENERAL -->
                    <div class="flex flex-col gap-6">
                        <div class="flex items-center gap-3 border-b border-[#ecedea] pb-2">
                            <div class="w-2 h-6 bg-[#629f22] rounded-full"></div>
                            <h2 class="text-[#103928] font-bold text-[1.3em]">Información General</h2>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="flex flex-col gap-2">
                                <label for="nombre" class="text-[#304e42] font-semibold text-[1.1em]">Nombre de la Planta</label>
                                <input type="text" name="nombre" id="nombre"
                                       class="p-4 bg-[#f3f5f3] border-2 @error('nombre') border-red-500 @else border-[#ecedea] @enderror rounded-[10px] outline-none focus:border-[#629f22] transition duration-300"
                                       placeholder="Ej: Monstera Deliciosa" value="{{ old('nombre', $find->nombre) }}" required>
                                @error('nombre') <span class="text-red-500 text-[0.9em] font-medium">{{ $message }}</span> @enderror
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="tipo_planta_id" class="text-[#304e42] font-semibold text-[1.1em]">Tipo de Planta</label>
                                <select name="tipo_planta_id" id="tipo_planta_id"
                                        class="p-4 bg-[#f3f5f3] border-2 @error('tipo_planta_id') border-red-500 @else border-[#ecedea] @enderror rounded-[10px] outline-none focus:border-[#629f22] transition duration-300" required>
                                    <option value="" disabled selected>Seleccione un tipo...</option>
                                    @foreach($tipos as $tipo)
                                        <option value="{{ $tipo->id }}" {{ old('tipo_planta_id', $find->tipo_planta_id) == $tipo->id ? 'selected' : '' }}>{{ $tipo->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('tipo_planta_id') <span class="text-red-500 text-[0.9em] font-medium">{{ $message }}</span> @enderror
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="precio" class="text-[#304e42] font-semibold text-[1.1em]">Precio (S/.)</label>
                                <input type="number" step="0.01" name="precio" id="precio"
                                       class="p-4 bg-[#f3f5f3] border-2 @error('precio') border-red-500 @else border-[#ecedea] @enderror rounded-[10px] outline-none focus:border-[#629f22] transition duration-300"
                                       placeholder="0.00" value="{{ old('precio', $find->precio) }}" required>
                                @error('precio') <span class="text-red-500 text-[0.9em] font-medium">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- SECCIÓN 2: FICHA TÉCNICA -->
                    <div class="flex flex-col gap-6">
                        <div class="flex items-center gap-3 border-b border-[#ecedea] pb-2">
                            <div class="w-2 h-6 bg-[#629f22] rounded-full"></div>
                            <h2 class="text-[#103928] font-bold text-[1.3em]">Ficha Técnica</h2>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                            <div class="flex flex-col gap-2">
                                <label for="luz_requerida" class="text-[#304e42] font-semibold text-[1.1em]">Luz Requerida</label>
                                <select name="luz_requerida" id="luz_requerida"
                                        class="p-4 bg-[#f3f5f3] border-2 @error('luz_requerida') border-red-500 @else border-[#ecedea] @enderror rounded-[10px] outline-none focus:border-[#629f22] transition duration-300" required>
                                    <option value="" disabled selected>Seleccione luz...</option>
                                    <option value="baja" {{ old('luz_requerida', $find->luz_requerida) == 'baja' ? 'selected' : '' }}>Baja</option>
                                    <option value="media" {{ old('luz_requerida', $find->luz_requerida) == 'media' ? 'selected' : '' }}>Media</option>
                                    <option value="alta" {{ old('luz_requerida', $find->luz_requerida) == 'alta' ? 'selected' : '' }}>Alta</option>
                                    <option value="siempre_en_el_sol" {{ old('luz_requerida', $find->luz_requerida) == 'siempre_en_el_sol' ? 'selected' : '' }}>Siempre al sol</option>
                                </select>
                                @error('luz_requerida') <span class="text-red-500 text-[0.9em] font-medium">{{ $message }}</span> @enderror
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="tamaño_adulto" class="text-[#304e42] font-semibold text-[1.1em]">Espacio Requerido</label>
                                <select name="tamaño_adulto" id="tamaño_adulto"
                                        class="p-4 bg-[#f3f5f3] border-2 @error('tamaño_adulto') border-red-500 @else border-[#ecedea] @enderror rounded-[10px] outline-none focus:border-[#629f22] transition duration-300" required>
                                    <option value="" disabled selected>Seleccione espacio...</option>
                                    <option value="pequena" {{ old('tamaño_adulto', $find->tamaño_adulto) == 'pequena' ? 'selected' : '' }}>Pequeña</option>
                                    <option value="mediana" {{ old('tamaño_adulto', $find->tamaño_adulto) == 'mediana' ? 'selected' : '' }}>Mediana</option>
                                    <option value="grande" {{ old('tamaño_adulto', $find->tamaño_adulto) == 'grande' ? 'selected' : '' }}>Grande</option>
                                </select>
                                @error('tamaño_adulto') <span class="text-red-500 text-[0.9em] font-medium">{{ $message }}</span> @enderror
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="tipo_ambiente" class="text-[#304e42] font-semibold text-[1.1em]">Tipo de Ambiente</label>
                                <select name="tipo_ambiente" id="tipo_ambiente"
                                        class="p-4 bg-[#f3f5f3] border-2 @error('tipo_ambiente') border-red-500 @else border-[#ecedea] @enderror rounded-[10px] outline-none focus:border-[#629f22] transition duration-300" required>
                                    <option value="" disabled selected>Seleccione ambiente...</option>
                                    <option value="interiores" {{ old('tipo_ambiente', $find->tipo_ambiente) == 'interiores' ? 'selected' : '' }}>Interiores</option>
                                    <option value="exteriores" {{ old('tipo_ambiente', $find->tipo_ambiente) == 'exteriores' ? 'selected' : '' }}>Exteriores</option>
                                    <option value="ambos" {{ old('tipo_ambiente', $find->tipo_ambiente) == 'ambos' ? 'selected' : '' }}>Ambos</option>
                                </select>
                                @error('tipo_ambiente') <span class="text-red-500 text-[0.9em] font-medium">{{ $message }}</span> @enderror
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="frecuencia_riego" class="text-[#304e42] font-semibold text-[1.1em]">Frecuencia de Riego</label>
                                <select name="frecuencia_riego" id="frecuencia_riego"
                                        class="p-4 bg-[#f3f5f3] border-2 @error('frecuencia_riego') border-red-500 @else border-[#ecedea] @enderror rounded-[10px] outline-none focus:border-[#629f22] transition duration-300" required>
                                    <option value="" disabled selected>Seleccione riego...</option>
                                    <option value="diario" {{ old('frecuencia_riego', $find->frecuencia_riego) == 'diario' ? 'selected' : '' }}>Diario</option>
                                    <option value="cada_3_dias" {{ old('frecuencia_riego', $find->frecuencia_riego) == 'cada_3_dias' ? 'selected' : '' }}>Cada 3 días</option>
                                    <option value="semanal" {{ old('frecuencia_riego', $find->frecuencia_riego) == 'semanal' ? 'selected' : '' }}>Semanal</option>
                                    <option value="quincenal" {{ old('frecuencia_riego', $find->frecuencia_riego) == 'quincenal' ? 'selected' : '' }}>Quincenal</option>
                                    <option value="mensualmente" {{ old('frecuencia_riego', $find->frecuencia_riego) == 'mensualmente' ? 'selected' : '' }}>Mensualmente</option>
                                </select>
                                @error('frecuencia_riego') <span class="text-red-500 text-[0.9em] font-medium">{{ $message }}</span> @enderror
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="estetica" class="text-[#304e42] font-semibold text-[1.1em]">Estética</label>
                                <select name="estetica" id="estetica"
                                        class="p-4 bg-[#f3f5f3] border-2 @error('estetica') border-red-500 @else border-[#ecedea] @enderror rounded-[10px] outline-none focus:border-[#629f22] transition duration-300" required>
                                    <option value="" disabled selected>Seleccione estética...</option>
                                    <option value="follaje" {{ old('estetica', $find->estetica) == 'follaje' ? 'selected' : '' }}>Follaje</option>
                                    <option value="flor" {{ old('estetica', $find->estetica) == 'flor' ? 'selected' : '' }}>Flor</option>
                                    <option value="colgantes" {{ old('estetica', $find->estetica) == 'colgantes' ? 'selected' : '' }}>Colgantes</option>
                                    <option value="suculentas" {{ old('estetica', $find->estetica) == 'suculentas' ? 'selected' : '' }}>Suculenta</option>
                                </select>
                                @error('estetica') <span class="text-red-500 text-[0.9em] font-medium">{{ $message }}</span> @enderror
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="nivel_cuidado" class="text-[#304e42] font-semibold text-[1.1em]">Nivel de Cuidado</label>
                                <select name="nivel_cuidado" id="nivel_cuidado"
                                        class="p-4 bg-[#f3f5f3] border-2 @error('nivel_cuidado') border-red-500 @else border-[#ecedea] @enderror rounded-[10px] outline-none focus:border-[#629f22] transition duration-300" required>
                                    <option value="" disabled selected>Seleccione nivel...</option>
                                    <option value="principiante" {{ old('nivel_cuidado', $find->nivel_cuidado) == 'principiante' ? 'selected' : '' }}>Principiante</option>
                                    <option value="intermedio" {{ old('nivel_cuidado', $find->nivel_cuidado) == 'intermedio' ? 'selected' : '' }}>Intermedio</option>
                                    <option value="experto" {{ old('nivel_cuidado', $find->nivel_cuidado) == 'experto' ? 'selected' : '' }}>Experto</option>
                                </select>
                                @error('nivel_cuidado') <span class="text-red-500 text-[0.9em] font-medium">{{ $message }}</span> @enderror
                            </div>

                            <div class="flex items-center gap-3 py-4">
                                <input type="checkbox" name="es_toxica" id="es_toxica" value="1"
                                       class="w-6 h-6 accent-[#629f22] cursor-pointer" {{ old('es_toxica', $find->es_toxica) ? 'checked' : '' }}>
                                <label for="es_toxica" class="text-[#304e42] font-semibold text-[1.1em] cursor-pointer">¿Es tóxica para mascotas o niños?</label>
                            </div>
                        </div>
                    </div>

                    <!-- SECCIÓN 3: GESTIÓN DE INVENTARIO -->
                    <div class="flex flex-col gap-6">
                        <div class="flex items-center gap-3 border-b border-[#ecedea] pb-2">
                            <div class="w-2 h-6 bg-[#629f22] rounded-full"></div>
                            <h2 class="text-[#103928] font-bold text-[1.3em]">Gestión de Inventario</h2>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="flex flex-col gap-2">
                                <label for="stock_actual" class="text-[#304e42] font-semibold text-[1.1em]">Stock Actual</label>
                                <input type="number" name="stock_actual" id="stock_actual"
                                       class="p-4 bg-[#f3f5f3] border-2 @error('stock_actual') border-red-500 @else border-[#ecedea] @enderror rounded-[10px] outline-none focus:border-[#629f22] transition duration-300"
                                       placeholder="0" value="{{ old('stock_actual', $find->stock_actual) }}" required>
                                @error('stock_actual') <span class="text-red-500 text-[0.9em] font-medium">{{ $message }}</span> @enderror
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="stock_minimo" class="text-[#304e42] font-semibold text-[1.1em]">Stock Mínimo (Alerta)</label>
                                <input type="number" name="stock_minimo" id="stock_minimo"
                                       class="p-4 bg-[#f3f5f3] border-2 @error('stock_minimo') border-red-500 @else border-[#ecedea] @enderror rounded-[10px] outline-none focus:border-[#629f22] transition duration-300"
                                       placeholder="0" value="{{ old('stock_minimo', $find->stock_minimo) }}" required>
                                @error('stock_minimo') <span class="text-red-500 text-[0.9em] font-medium">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end mt-6">
                        <button type="submit"
                                class="flex items-center justify-between py-4 px-10 bg-[#629f22] text-[#fbfbfb] rounded-[10px] gap-3 hover:scale-110 transition duration-300 font-bold text-[1.1em]">
                            <img class="w-[1.2em] h-auto" src="{{ asset('images/anadir.png') }}" alt="Actualizar">
                            Actualizar Planta en Catálogo
                        </button>
                    </div>
                </form>
            </div>
        </section>

    </main>

</body>

</html>
