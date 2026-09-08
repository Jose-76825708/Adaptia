<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    @vite('resources/css/app.css')
    <title>Crear Tipo de Planta</title>
</head>

<body class="flex font-sans h-screen">

    @include('partials.sidebar')

    <main class="flex flex-5 flex-col px-25 py-10 bg-[#fbfbfb] h-full gap-4">

        <section>
            <a class="flex items-center text-[#0c251c] font-bold text-[0.8em]" href="{{ route('tipoPlantas.index') }}">
                <img class="w-[3%] h-auto" src="{{ asset('images/regreso-flecha.png') }}" alt="">Volver a Tipos de Plantas
            </a>
        </section>

        <section class="flex items-center justify-between mb-8">
            <div class="flex-4">
                <h1 class="text-[#103928] font-bold text-[2.5em]">Crear Tipo de Planta</h1>
                <p class="text-[#8b8d8f] text-[1em]">Registra una nueva categoría de plantas para organizar mejor tu catálogo.</p>
            </div>
        </section>

        <section class="flex justify-center">
            <div class="flex flex-col p-10 bg-[#fefdfe] rounded-[20px] shadow-xl w-full max-w-2xl gap-8 border border-[#ecedea]">
                <form action="{{ route('tipoPlantas.store') }}" method="POST" class="flex flex-col gap-6">
                    @csrf

                    <div class="flex flex-col gap-2">
                        <label for="nombre" class="text-[#304e42] font-semibold text-[1.1em]">Nombre del Tipo de Planta</label>
                        <input type="text" name="nombre" id="nombre"
                               class="p-4 bg-[#f3f5f3] border-2 @error('nombre') border-red-500 @else border-[#ecedea] @enderror rounded-[10px] outline-none focus:border-[#629f22] transition duration-300"
                               placeholder="Ej: Suculentas, Helechos..."
                               value="{{ old('nombre') }}" required>
                        @error('nombre')
                            <span class="text-red-500 text-[0.9em] font-medium">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex justify-end mt-4">
                        <button type="submit"
                                class="flex items-center justify-between py-3 px-8 bg-[#629f22] text-[#fbfbfb] rounded-[10px] gap-3 hover:scale-110 transition duration-300 font-bold">
                            <img class="w-[1.2em] h-auto" src="{{ asset('images/anadir.png') }}" alt="Añadir">
                            Guardar Tipo de Planta
                        </button>
                    </div>
                </form>
            </div>
        </section>

    </main>

</body>

</html>
