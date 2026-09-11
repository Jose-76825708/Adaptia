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
                        <!-- TAMAÑO ADULTO -->
                        <div class="flex flex-col gap-2">
                            <label for="tamaño_adulto" class="text-[#304e42] font-semibold text-[1.1em]">Tamaño Adulto de la Planta</label>
                            <select name="tamaño_adulto" id="tamaño_adulto"
                                    class="p-4 bg-[#f3f5f3] border-2 @error('tamaño_adulto') border-red-500 @else border-[#ecedea] @enderror rounded-[10px] outline-none focus:border-[#629f22] transition duration-300" required>
                                <option value="" disabled {{ is_null($perfil) || old('tamaño_adulto', $perfil->tamaño_adulto) == '' ? 'selected' : '' }}>Seleccione tamaño...</option>
                                <option value="pequena" {{ old('tamaño_adulto', $perfil->tamaño_adulto ?? '') == 'pequena' ? 'selected' : '' }}>Pequeña (Estante/Mesa)</option>
                                <option value="mediana" {{ old('tamaño_adulto', $perfil->tamaño_adulto ?? '') == 'mediana' ? 'selected' : '' }}>Mediana (Habitación)</option>
                                <option value="grande" {{ old('tamaño_adulto', $perfil->tamaño_adulto ?? '') == 'grande' ? 'selected' : '' }}>Grande (Jardín/Patio)</option>
                            </select>
                            @error('tamaño_adulto') <span class="text-red-500 text-[0.9em] font-medium">{{ $message }}</span> @enderror
                        </div>

                        <!-- LUZ_REQUERIDA -->
                        <div class="flex flex-col gap-2">
                            <label for="luz_requerida" class="text-[#304e42] font-semibold text-[1.1em]">Luz Requerida</label>
                            <select name="luz_requerida" id="luz_requerida"
                                    class="p-4 bg-[#f3f5f3] border-2 @error('luz_requerida') border-red-500 @else border-[#ecedea] @enderror rounded-[10px] outline-none focus:border-[#629f22] transition duration-300" required>
                                <option value="" disabled {{ is_null($perfil) || old('luz_requerida', $perfil->luz_requerida) == '' ? 'selected' : '' }}>Seleccione luz requerida...</option>
                                <option value="baja" {{ old('luz_requerida', $perfil->luz_requerida ?? '') == 'baja' ? 'selected' : '' }}>Baja (Sombra)</option>
                                <option value="media" {{ old('luz_requerida', $perfil->luz_requerida ?? '') == 'media' ? 'selected' : '' }}>Media (Luz indirecta)</option>
                                <option value="alta" {{ old('luz_requerida', $perfil->luz_requerida ?? '') == 'alta' ? 'selected' : '' }}>Alta (Mucha luz)</option>
                                <option value="siempre_en_el_sol" {{ old('luz_requerida', $perfil->luz_requerida ?? '') == 'siempre_en_el_sol' ? 'selected' : '' }}>Siempre al sol</option>
                            </select>
                            @error('luz_requerida') <span class="text-red-500 text-[0.9em] font-medium">{{ $message }}</span> @enderror
                        </div>

                        <!-- FRECUENCIA DE RIEGO -->
                        <div class="flex flex-col gap-2">
                            <label for="frecuencia_riego" class="text-[#304e42] font-semibold text-[1.1em]">Frecuencia de Riego</label>
                            <select name="frecuencia_riego" id="frecuencia_riego"
                                    class="p-4 bg-[#f3f5f3] border-2 @error('frecuencia_riego') border-red-500 @else border-[#ecedea] @enderror rounded-[10px] outline-none focus:border-[#629f22] transition duration-300" required>
                                <option value="" disabled {{ is_null($perfil) || old('frecuencia_riego', $perfil->frecuencia_riego) == '' ? 'selected' : '' }}>Seleccione frecuencia...</option>
                                <option value="diario" {{ old('frecuencia_riego', $perfil->frecuencia_riego ?? '') == 'diario' ? 'selected' : '' }}>Diario</option>
                                <option value="cada_3_dias" {{ old('frecuencia_riego', $perfil->frecuencia_riego ?? '') == 'cada_3_dias' ? 'selected' : '' }}>Cada 3 días</option>
                                <option value="semanal" {{ old('frecuencia_riego', $perfil->frecuencia_riego ?? '') == 'semanal' ? 'selected' : '' }}>Semanal</option>
                                <option value="quincenal" {{ old('frecuencia_riego', $perfil->frecuencia_riego ?? '') == 'quincenal' ? 'selected' : '' }}>Quincenal</option>
                                <option value="mensualmente" {{ old('frecuencia_riego', $perfil->frecuencia_riego ?? '') == 'mensualmente' ? 'selected' : '' }}>Mensualmente</option>
                            </select>
                            @error('frecuencia_riego') <span class="text-red-500 text-[0.9em] font-medium">{{ $message }}</span> @enderror
                        </div>

                        <!-- TIPO DE AMBIENTE -->
                        <div class="flex flex-col gap-2">
                            <label for="tipo_ambiente" class="text-[#304e42] font-semibold text-[1.1em]">Tipo de Ambiente</label>
                            <select name="tipo_ambiente" id="tipo_ambiente"
                                    class="p-4 bg-[#f3f5f3] border-2 @error('tipo_ambiente') border-red-500 @else border-[#ecedea] @enderror rounded-[10px] outline-none focus:border-[#629f22] transition duration-300" required>
                                <option value="" disabled {{ is_null($perfil) || old('tipo_ambiente', $perfil->tipo_ambiente) == '' ? 'selected' : '' }}>Seleccione ambiente...</option>
                                <option value="interiores" {{ old('tipo_ambiente', $perfil->tipo_ambiente ?? '') == 'interiores' ? 'selected' : '' }}>Interiores</option>
                                <option value="exteriores" {{ old('tipo_ambiente', $perfil->tipo_ambiente ?? '') == 'exteriores' ? 'selected' : '' }}>Exteriores</option>
                                <option value="ambos" {{ old('tipo_ambiente', $perfil->tipo_ambiente ?? '') == 'ambos' ? 'selected' : '' }}>Ambos</option>
                            </select>
                            @error('tipo_ambiente') <span class="text-red-500 text-[0.9em] font-medium">{{ $message }}</span> @enderror
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

                        <!-- TOXICIDAD -->
                        <div class="flex items-center gap-3 py-6">
                            <input type="checkbox" name="toxicidad" id="toxicidad" value="1"
                                   class="w-6 h-6 accent-[#629f22] cursor-pointer" {{ old('toxicidad', $perfil->toxicidad ?? false) ? 'checked' : '' }}>
                            <label for="toxicidad" class="text-[#304e42] font-semibold text-[1.1em] cursor-pointer">
                                Prefiere evitar plantas tóxicas
                            </label>
                        </div>

                        <!-- ESTETICA -->
                        <div class="flex flex-col gap-2">
                            <label for="estetica" class="text-[#304e42] font-semibold text-[1.1em]">Estética Preferida</label>
                            <select name="estetica" id="estetica"
                                    class="p-4 bg-[#f3f5f3] border-2 @error('estetica') border-red-500 @else border-[#ecedea] @enderror rounded-[10px] outline-none focus:border-[#629f22] transition duration-300" required>
                                <option value="" disabled {{ is_null($perfil) || old('estetica', $perfil->estetica) == '' ? 'selected' : '' }}>Seleccione estética...</option>
                                <option value="follaje" {{ old('estetica', $perfil->estetica ?? '') == 'follaje' ? 'selected' : '' }}>Follaje</option>
                                <option value="flor" {{ old('estetica', $perfil->estetica ?? '') == 'flor' ? 'selected' : '' }}>Flor</option>
                                <option value="colgantes" {{ old('estetica', $perfil->estetica ?? '') == 'colgantes' ? 'selected' : '' }}>Colgantes</option>
                                <option value="suculenta" {{ old('estetica', $perfil->estetica ?? '') == 'suculenta' ? 'selected' : '' }}>Suculenta</option>
                            </select>
                            @error('estetica') <span class="text-red-500 text-[0.9em] font-medium">{{ $message }}</span> @enderror
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
