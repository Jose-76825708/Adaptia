@extends('layouts.app')

@section('title', 'Actualizar mi perfil - Adaptia')

@section('content')
<style>
  .profile-select {
    min-height: 3.25rem;
    border: 1px solid #dce5dc;
    border-radius: 0.875rem;
    background-color: #fff;
    padding: 0.75rem 1rem;
    color: #263a2e;
    transition: border-color 180ms ease, box-shadow 180ms ease;
  }

  .profile-select:focus {
    border-color: #7cb22b;
    outline: none;
    box-shadow: 0 0 0 4px rgb(124 178 43 / 16%);
  }

  #wizard-steps {
    display: grid;
    grid-template-areas: "wizard-step";
    align-content: stretch;
  }

  .wizard-step {
    grid-area: wizard-step;
    display: flex;
    min-height: 28rem;
    flex-direction: column;
    justify-content: center;
    opacity: 0;
    transform: translateX(1.5rem);
    pointer-events: none;
    visibility: hidden;
    transition:
      opacity 360ms ease,
      transform 360ms cubic-bezier(0.22, 1, 0.36, 1),
      visibility 0s linear 360ms;
  }

  .wizard-step.active {
    opacity: 1;
    pointer-events: auto;
    transform: translateX(0);
    visibility: visible;
    transition-delay: 0s;
  }

  .wizard-step.enter-from-left {
    transform: translateX(-1.5rem);
  }

  .wizard-step.is-exiting-left,
  .wizard-step.is-exiting-right {
    opacity: 0;
    pointer-events: none;
    visibility: visible;
  }

  .wizard-step.is-exiting-left {
    transform: translateX(-1.5rem);
  }

  .wizard-step.is-exiting-right {
    transform: translateX(1.5rem);
  }

  @media (prefers-reduced-motion: reduce) {
    .wizard-step {
      transition-duration: 1ms;
      transition-delay: 0s;
      transform: none;
    }
  }
</style>
<main>
    <!-- Encabezado -->
    <section class="reveal w-full bg-linear-to-br from-[#f3f8ee] via-white to-[#f1f6eb] px-4 py-10 sm:px-8 sm:py-14 lg:px-12">
        <div class="mx-auto w-full text-center">
            <span class="mb-3 inline-flex items-center gap-2 rounded-full border border-[#dce9d0] bg-white/80 px-4 py-2 text-sm font-semibold text-[#52752d] shadow-sm">
                <span class="h-2 w-2 rounded-full bg-[#7cb22b]"></span>
                Tu espacio, tus preferencias
            </span>
            <h1 class="text-3xl font-bold tracking-tight text-[#0f3c2b] sm:text-4xl lg:text-5xl">Personaliza tu perfil</h1>
            <p class="mx-auto mt-3 max-w-2xl text-base leading-7 text-[#5f6b54] sm:text-lg">
                Cuéntanos sobre tu espacio y tus hábitos para encontrar plantas que encajen contigo.
            </p>
        </div>
    </section>

    <!-- Wizard container -->
    <div class="reveal flex min-h-[70vh] w-full flex-col overflow-hidden border-y border-[#e5ebdf] bg-white shadow-[0_18px_60px_-30px_rgba(15,60,43,0.24)] sm:rounded-3xl sm:border sm:border-[#e5ebdf]">
        <!-- Barra de progreso -->
        <div class="border-b border-[#edf1e9] bg-[#fbfcfa] px-5 py-5 sm:px-8 lg:px-12">
            <div class="mb-4 flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#78905e]">Configuración del perfil</p>
                    <p class="mt-1 text-sm font-semibold text-[#34533b]">
                        Paso <span id="wizard-current-step" class="text-[#0f3c2b]">1</span>
                        <span class="font-normal text-[#879184]">de</span>
                        <span id="wizard-total-steps">4</span>
                    </p>
                </div>
                <span id="wizard-completion" class="rounded-full bg-[#edf5e5] px-3 py-1.5 text-xs font-bold text-[#52752d] sm:text-sm">25% completado</span>
            </div>
            <div id="wizard-progress-bar"
                 class="h-2 overflow-hidden rounded-full bg-[#e7ece2]"
                 role="progressbar"
                 aria-label="Progreso del perfil"
                 aria-valuemin="0"
                 aria-valuemax="100"
                 aria-valuenow="25">
                <div class="h-full rounded-full bg-linear-to-r from-[#7cb22b] to-[#a4cc5e] transition-[width] duration-500 ease-out" style="width: 25%"></div>
            </div>
        </div>

        <!-- Pasos del wizard -->
        <div id="wizard-steps" class="w-full flex-1 overflow-hidden">
            <!-- Paso 1: Espacio y luz -->
            <div class="wizard-step active p-5 sm:p-8 lg:p-12" id="step-1">
                <div class="mb-7 border-l-4 border-[#7cb22b] pl-4 sm:mb-9">
                    <p class="text-sm font-bold uppercase tracking-[0.14em] text-[#78905e]">Paso 1</p>
                    <h3 class="mt-1 text-2xl font-bold text-[#0f3c2b] sm:text-3xl">Tu espacio y la luz</h3>
                    <p class="mt-2 text-sm text-[#718071] sm:text-base">Empecemos por conocer el lugar donde vivirá tu planta.</p>
                </div>
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:gap-8">
                    <div class="rounded-2xl border border-[#edf1e9] bg-[#fbfcfa] px-4 py-6 sm:px-5 sm:py-7">
                        <label for="wizard-tamanio" class="mb-2 block text-sm font-semibold text-[#344638]">Tamaño adulto de planta preferido</label>
                        <select name="tamaño_adulto" id="wizard-tamanio"
                                class="profile-select w-full"
                                required>
                            <option value="">Selecciona una opción</option>
                            <option value="pequena" {{ old('tamaño_adulto', optional($perfil)->tamaño_adulto) == 'pequena' ? 'selected' : '' }}>
                                Pequeña (Estante/Mesa)
                            </option>
                            <option value="mediana" {{ old('tamaño_adulto', optional($perfil)->tamaño_adulto) == 'mediana' ? 'selected' : '' }}>
                                Mediana (Habitación)
                            </option>
                            <option value="grande" {{ old('tamaño_adulto', optional($perfil)->tamaño_adulto) == 'grande' ? 'selected' : '' }}>
                                Grande (Jardín/Patio)
                            </option>
                        </select>
                    </div>
                    <div class="rounded-2xl border border-[#edf1e9] bg-[#fbfcfa] px-4 py-6 sm:px-5 sm:py-7">
                        <label for="wizard-luz" class="mb-2 block text-sm font-semibold text-[#344638]">Luz disponible en tu espacio</label>
                        <select name="luz_requerida" id="wizard-luz"
                                class="profile-select w-full"
                                required>
                            <option value="">Selecciona una opción</option>
                            <option value="baja" {{ old('luz_requerida', optional($perfil)->luz_requerida) == 'baja' ? 'selected' : '' }}>
                                Baja (Sombra)
                            </option>
                            <option value="media" {{ old('luz_requerida', optional($perfil)->luz_requerida) == 'media' ? 'selected' : '' }}>
                                Media (Luz indirecta)
                            </option>
                            <option value="alta" {{ old('luz_requerida', optional($perfil)->luz_requerida) == 'alta' ? 'selected' : '' }}>
                                Alta (Mucha luz)
                            </option>
                            <option value="siempre_en_el_sol" {{ old('luz_requerida', optional($perfil)->luz_requerida) == 'siempre_en_el_sol' ? 'selected' : '' }}>
                                Siempre al sol
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Paso 2: Hábitos y ambiente -->
            <div class="wizard-step p-5 sm:p-8 lg:p-12" id="step-2">
                <div class="mb-7 border-l-4 border-[#7cb22b] pl-4 sm:mb-9">
                    <p class="text-sm font-bold uppercase tracking-[0.14em] text-[#78905e]">Paso 2</p>
                    <h3 class="mt-1 text-2xl font-bold text-[#0f3c2b] sm:text-3xl">Tus hábitos y ambiente</h3>
                    <p class="mt-2 text-sm text-[#718071] sm:text-base">Así sabremos qué cuidados y entorno te resultan más cómodos.</p>
                </div>
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:gap-8">
                    <div class="rounded-2xl border border-[#edf1e9] bg-[#fbfcfa] px-4 py-6 sm:px-5 sm:py-7">
                        <label for="wizard-riego" class="mb-2 block text-sm font-semibold text-[#344638]">Frecuencia de riego que prefieres</label>
                        <select name="frecuencia_riego" id="wizard-riego"
                                class="profile-select w-full"
                                required>
                            <option value="">Selecciona una opción</option>
                            <option value="diario" {{ old('frecuencia_riego', optional($perfil)->frecuencia_riego) == 'diario' ? 'selected' : '' }}>
                                Diario
                            </option>
                            <option value="cada_3_dias" {{ old('frecuencia_riego', optional($perfil)->frecuencia_riego) == 'cada_3_dias' ? 'selected' : '' }}>
                                Cada 3 días
                            </option>
                            <option value="semanal" {{ old('frecuencia_riego', optional($perfil)->frecuencia_riego) == 'semanal' ? 'selected' : '' }}>
                                Semanal
                            </option>
                            <option value="quincenal" {{ old('frecuencia_riego', optional($perfil)->frecuencia_riego) == 'quincenal' ? 'selected' : '' }}>
                                Quincenal
                            </option>
                            <option value="mensualmente" {{ old('frecuencia_riego', optional($perfil)->frecuencia_riego) == 'mensualmente' ? 'selected' : '' }}>
                                Mensualmente
                            </option>
                        </select>
                    </div>
                    <div class="rounded-2xl border border-[#edf1e9] bg-[#fbfcfa] px-4 py-6 sm:px-5 sm:py-7">
                        <label for="wizard-ambiente" class="mb-2 block text-sm font-semibold text-[#344638]">Tipo de ambiente</label>
                        <select name="tipo_ambiente" id="wizard-ambiente"
                                class="profile-select w-full"
                                required>
                            <option value="">Selecciona una opción</option>
                            <option value="interiores" {{ old('tipo_ambiente', optional($perfil)->tipo_ambiente) == 'interiores' ? 'selected' : '' }}>
                                Interiores
                            </option>
                            <option value="exteriores" {{ old('tipo_ambiente', optional($perfil)->tipo_ambiente) == 'exteriores' ? 'selected' : '' }}>
                                Exteriores
                            </option>
                            <option value="ambos" {{ old('tipo_ambiente', optional($perfil)->tipo_ambiente) == 'ambos' ? 'selected' : '' }}>
                                Ambos
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Paso 3: Experiencia y estética -->
            <div class="wizard-step p-5 sm:p-8 lg:p-12" id="step-3">
                <div class="mb-7 border-l-4 border-[#7cb22b] pl-4 sm:mb-9">
                    <p class="text-sm font-bold uppercase tracking-[0.14em] text-[#78905e]">Paso 3</p>
                    <h3 class="mt-1 text-2xl font-bold text-[#0f3c2b] sm:text-3xl">Tu experiencia y estilo</h3>
                    <p class="mt-2 text-sm text-[#718071] sm:text-base">Buscaremos plantas acordes a tu experiencia y a lo que te gusta.</p>
                </div>
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:gap-8">
                    <div class="rounded-2xl border border-[#edf1e9] bg-[#fbfcfa] px-4 py-6 sm:px-5 sm:py-7">
                        <label for="wizard-nivel" class="mb-2 block text-sm font-semibold text-[#344638]">Nivel de experiencia en cuidado de plantas</label>
                        <select name="nivel_cuidado" id="wizard-nivel"
                                class="profile-select w-full"
                                required>
                            <option value="">Selecciona una opción</option>
                            <option value="principiante" {{ old('nivel_cuidado', optional($perfil)->nivel_cuidado) == 'principiante' ? 'selected' : '' }}>
                                Principiante
                            </option>
                            <option value="intermedio" {{ old('nivel_cuidado', optional($perfil)->nivel_cuidado) == 'intermedio' ? 'selected' : '' }}>
                                Intermedio
                            </option>
                            <option value="experto" {{ old('nivel_cuidado', optional($perfil)->nivel_cuidado) == 'experto' ? 'selected' : '' }}>
                                Experto
                            </option>
                        </select>
                    </div>
                    <div class="rounded-2xl border border-[#edf1e9] bg-[#fbfcfa] px-4 py-6 sm:px-5 sm:py-7">
                        <label for="wizard-estetica" class="mb-2 block text-sm font-semibold text-[#344638]">Estética preferida</label>
                        <select name="estetica" id="wizard-estetica"
                                class="profile-select w-full"
                                required>
                            <option value="">Selecciona una opción</option>
                            <option value="follaje" {{ old('estetica', optional($perfil)->estetica) == 'follaje' ? 'selected' : '' }}>
                                Follaje
                            </option>
                            <option value="flor" {{ old('estetica', optional($perfil)->estetica) == 'flor' ? 'selected' : '' }}>
                                Flor
                            </option>
                            <option value="colgantes" {{ old('estetica', optional($perfil)->estetica) == 'colgantes' ? 'selected' : '' }}>
                                Colgantes
                            </option>
                            <option value="suculenta" {{ old('estetica', optional($perfil)->estetica) == 'suculenta' ? 'selected' : '' }}>
                                Suculenta
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Paso 4: Seguridad -->
            <div class="wizard-step p-5 sm:p-8 lg:p-12" id="step-4">
                <div class="mb-7 border-l-4 border-[#7cb22b] pl-4 sm:mb-9">
                    <p class="text-sm font-bold uppercase tracking-[0.14em] text-[#78905e]">Paso 4</p>
                    <h3 class="mt-1 text-2xl font-bold text-[#0f3c2b] sm:text-3xl">Un hogar seguro</h3>
                    <p class="mt-2 text-sm text-[#718071] sm:text-base">Indícanos si hay algo que debamos considerar por seguridad.</p>
                </div>
                <div class="space-y-4">
                    <label class="flex cursor-pointer items-start gap-4 rounded-2xl border border-[#dce9d0] bg-[#f6faef] p-5 transition-colors hover:bg-[#eff6e5] sm:p-7">
                        <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center">
                            <input type="checkbox" name="toxicidad" id="wizard-toxicidad" value="1"
                                   class="h-5 w-5 rounded border-[#b7c9a6] text-[#689b25] focus:ring-2 focus:ring-[#7cb22b]"
                                   {{ old('toxicidad', optional($perfil)->toxicidad) ? 'checked' : '' }}>
                        </span>
                        <span class="text-start">
                            <span class="block text-base font-semibold text-[#29452f]">
                                Prefiero evitar plantas tóxicas (por seguridad de mascotas o niños)
                            </span>
                            <span class="mt-1 block text-sm leading-6 text-[#718071]">Tendremos en cuenta esta preferencia al recomendarte plantas.</span>
                        </span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Barra de acciones -->
        <div class="flex w-full flex-col-reverse gap-3 border-t border-[#edf1e9] bg-[#fbfcfa] px-5 py-5 sm:flex-row sm:justify-between sm:px-8 lg:px-12">
            <button id="wizard-prev"
                    class="inline-flex min-h-12 items-center justify-center rounded-xl border border-[#dce5dc] bg-white px-6 py-3 font-semibold text-[#526454] transition duration-200 hover:border-[#b9c9ae] hover:bg-[#f5f8f2] disabled:cursor-not-allowed disabled:opacity-45 sm:min-w-36"
                    disabled>
                Anterior
            </button>
            <button id="wizard-next"
                    class="inline-flex min-h-12 items-center justify-center rounded-xl bg-[#7cb22b] px-8 py-3 font-bold text-white shadow-md shadow-[#7cb22b]/20 transition duration-200 hover:-translate-y-0.5 hover:bg-[#6eab26] hover:shadow-lg hover:shadow-[#7cb22b]/25 disabled:cursor-not-allowed disabled:opacity-60 sm:min-w-44">
                Siguiente
            </button>
        </div>
    </div>
</main>
@endsection

@push('scripts')
<script>
    const PERFIL_UPDATE_URL = "{{ route('perfil.update') }}";
    const PERFIL_EDIT_URL = "{{ route('perfil.edit') }}";
    const CLIENT_HOME_URL = "{{ route('home') }}";
    const CSRF_TOKEN = "{{ csrf_token() }}";
</script>
@endpush
