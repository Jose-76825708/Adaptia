<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    @vite('resources/css/app.css')
    <title>Mi Perfil - Adaptia</title>
</head>

<body class="flex font-sans h-screen">

    @include('partials.sidebar')

    <main class="flex flex-5 flex-col px-25 py-10 bg-[#fbfbfb] h-full gap-4 overflow-y-auto">

        <section>
            <a class="flex items-center text-[#0c251c] font-bold text-[0.8em]" href="{{ route('home') }}">
                <img class="w-[3%] h-auto" src="{{ asset('images/regreso-flecha.png') }}" alt="">Volver al Inicio
            </a>
        </section>

        <section class="flex items-center justify-between mb-8">
            <div class="flex-4">
                <h1 class="text-[#103928] font-bold text-[2.5em]">Mi Perfil de Usuario</h1>
                <p class="text-[#8b8d8f] text-[1em]">Configura tu entorno para recibir las mejores recomendaciones de plantas.</p>
            </div>
        </section>

        <section class="flex justify-center pb-10">
            <div class="flex flex-col p-10 bg-[#fefdfe] rounded-[20px] shadow-xl w-full max-w-2xl gap-8 border border-[#ecedea]">

                @if(session('success'))
                    <div class="p-4 bg-green-100 text-green-700 rounded-[10px] border border-green-200 text-center font-medium">
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('perfil.update') }}" method="POST" class="flex flex-col gap-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- ESPACIO -->
                        <div class="flex flex-col gap-2">
                            <label for="espacio" class="text-[#304e42] font-semibold text-[1.1em]">Espacio Disponible</label>
                            <select name="espacio" id="espacio"
                                    class="p-4 bg-[#f3f5f3] border-2 @error('espacio') border-red-500 @else border-[#ecedea] @enderror rounded-[10px] outline-none focus:border-[#629f22] transition duration-300" required>
                                <option value="" disabled {{ is_null($perfil) || old('espacio', $perfil->espacio) == '' ? 'selected' : '' }}>Seleccione espacio...</option>
                                <option value="pequena" {{ old('espacio', $perfil->espacio ?? '') == 'pequena' ? 'selected' : '' }}>Pequeña (Estante/Mesa)</option>
                                <option value="mediana" {{ old('espacio', $perfil->espacio ?? '') == 'mediana' ? 'selected' : '' }}>Mediana (Habitación)</option>
                                <option value="grande" {{ old('espacio', $perfil->espacio ?? '') == 'grande' ? 'selected' : '' }}>Grande (Jardín/Patio)</option>
                            </select>
                            @error('espacio') <span class="text-red-500 text-[0.9em] font-medium">{{ $message }}</span> @enderror
                        </div>

                        <!-- LUZ -->
                        <div class="flex flex-col gap-2">
                            <label for="luz" class="text-[#304e42] font-semibold text-[1.1em]">Luz Natural</label>
                            <select name="luz" id="luz"
                                    class="p-4 bg-[#f3f5f3] border-2 @error('luz') border-red-500 @else border-[#ecedea] @enderror rounded-[10px] outline-none focus:border-[#629f22] transition duration-300" required>
                                <option value="" disabled {{ is_null($perfil) || old('luz', $perfil->luz) == '' ? 'selected' : '' }}>Seleccione luz...</option>
                                <option value="baja" {{ old('luz', $perfil->luz ?? '') == 'baja' ? 'selected' : '' }}>Baja (Sombra)</option>
                                <option value="media" {{ old('luz', $perfil->luz ?? '') == 'media' ? 'selected' : '' }}>Media (Luz indirecta)</option>
                                <option value="alta" {{ old('luz', $perfil->luz ?? '') == 'alta' ? 'selected' : '' }}>Alta (Mucha luz)</option>
                                <option value="siempre_en_el_sol" {{ old('luz', $perfil->luz ?? '') == 'siempre_en_el_sol' ? 'selected' : '' }}>Siempre al sol</option>
                            </select>
                            @error('luz') <span class="text-red-500 text-[0.9em] font-medium">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- NIVEL DE CUIDADO -->
                        <div class="flex flex-col gap-2">
                            <label for="nivel_cuidado" class="text-[#304e42] font-semibold text-[1.1em]">Nivel de Cuidado</label>
                            <select name="nivel_cuidado" id="nivel_cuidado"
                                    class="p-4 bg-[#f3f5f3] border-2 @error('nivel_cuidado') border-red-500 @else border-[#ecedea] @enderror rounded-[10px] outline-none focus:border-[#629f22] transition duration-300" required>
                                <option value="" disabled {{ is_null($perfil) || old('nivel_cuidado', $perfil->nivel_cuidado) == '' ? 'selected' : '' }}>Seleccione nivel...</option>
                                <option value="principiante" {{ old('nivel_cuidado', $perfil->nivel_cuidado ?? '') == 'principiante' ? 'selected' : '' }}>Principiante</option>
                                <option value="intermedio" {{ old('nivel_cuidado', $perfil->nivel_cuidado ?? '') == 'intermedio' ? 'selected' : '' }}>Intermedio</option>
                                <option value="experto" {{ old('nivel_cuidado', $perfil->nivel_cuidado ?? '') == 'experto' ? 'selected' : '' }}>Experto</option>
                            </select>
                            @error('nivel_cuidado') <span class="text-red-500 text-[0.9em] font-medium">{{ $message }}</span> @enderror
                        </div>

                        <!-- MASCOTAS Y NIÑOS -->
                        <div class="flex items-center gap-3 py-6">
                            <input type="checkbox" name="mascotas_ninos" id="mascotas_ninos" value="1"
                                   class="w-6 h-6 accent-[#629f22] cursor-pointer" {{ old('mascotas_ninos', $perfil->mascotas_ninos ?? false) ? 'checked' : '' }}>
                            <label for="mascotas_ninos" class="text-[#304e42] font-semibold text-[1.1em] cursor-pointer">
                                Tengo mascotas o niños pequeños
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end mt-4">
                        <button type="submit"
                                class="flex items-center justify-between py-3 px-8 bg-[#629f22] text-[#fbfbfb] rounded-[10px] gap-3 hover:scale-110 transition duration-300 font-bold text-[1.1em]">
                            Guardar mi Perfil
                        </button>
                    </div>
                </form>
            </div>
        </section>

    </main>

</body>

</html>
