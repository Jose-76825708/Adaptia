<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    @vite('resources/css/app.css')
    <title>Editar Sensor</title>
</head>

<body class="flex font-sans h-screen">

    @include('partials.sidebar')

    <main class="flex flex-5 flex-col px-25 py-10 bg-[#fbfbfb] h-full gap-4">

        <section>
            <a class="flex items-center text-[#0c251c] font-bold text-[0.8em]" href="{{ route('sensores.index') }}">
                <img class="w-[3%] h-auto" src="{{ asset('images/regreso-flecha.png') }}" alt="">Volver a Sensores
            </a>
        </section>

        <section class="flex items-center justify-between mb-8">
            <div class="flex-4">
                <h1 class="text-[#103928] font-bold text-[2.5em]">Editar Sensor</h1>
                <p class="text-[#8b8d8f] text-[1em]">Modifica la información del sensor seleccionado.</p>
            </div>
        </section>

        <section class="flex justify-center">
            <div class="flex flex-col p-10 bg-[#fefdfe] rounded-[20px] shadow-xl w-full max-w-2xl gap-8 border border-[#ecedea]">
                <form action="{{ route('sensores.update', $sensor->id) }}" method="POST" class="flex flex-col gap-6">
                    @csrf
                    @method('PUT')

                    <div class="flex flex-col gap-2">
                        <label for="identificador_fisico" class="text-[#304e42] font-semibold text-[1.1em]">Identificador Físico</label>
                        <input type="text" name="identificador_fisico" id="identificador_fisico"
                               class="p-4 bg-[#f3f5f3] border-2 @error('identificador_fisico') border-red-500 @else border-[#ecedea] @enderror rounded-[10px] outline-none focus:border-[#629f22] transition duration-300"
                               placeholder="Ej: SENSOR-001, TEMP-HUM-02..."
                               value="{{ old('identificador_fisico', $sensor->identificador_fisico) }}" required>
                        @error('identificador_fisico')
                            <span class="text-red-500 text-[0.9em] font-medium">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex flex-col gap-2">
                        <label for="estado" class="text-[#304e42] font-semibold text-[1.1em]">Estado</label>
                        <select name="estado" id="estado"
                                class="p-4 bg-[#f3f5f3] border-2 @error('estado') border-red-500 @else border-[#ecedea] @enderror rounded-[10px] outline-none focus:border-[#629f22] transition duration-300">
                            <option value="">Seleccione estado</option>
                            <option value="activo" {{ (old('estado') ?? $sensor->estado) == 'activo' ? 'selected' : '' }}>Activo</option>
                            <option value="inactivo" {{ (old('estado') ?? $sensor->estado) == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                        </select>
                        @error('estado')
                            <span class="text-red-500 text-[0.9em] font-medium">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex justify-end mt-4">
                        <button type="submit"
                                class="flex items-center justify-between py-3 px-8 bg-[#629f22] text-[#fbfbfb] rounded-[10px] gap-3 hover:scale-110 transition duration-300 font-bold">
                            <img class="w-[1.2em] h-auto" src="{{ asset('images/anadir.png') }}" alt="Actualizar">
                            Actualizar Sensor
                        </button>
                    </div>
                </form>
            </div>
        </section>

    </main>
</body>

</html>