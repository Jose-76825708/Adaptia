<?php $__env->startSection('title', 'Actualizar mi perfil - Adaptia'); ?>

<?php $__env->startSection('content'); ?>
<style>
  .wizard-step {
    opacity: 0;
    transform: scale(0.95);
    transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
    pointer-events: none;
  }
  .wizard-step.active {
    opacity: 1;
    transform: scale(1);
    pointer-events: auto;
  }
</style>
<main>
    <!-- Encabezado -->
    <section class="mb-8 reveal text-center">
        <h1 class="text-3xl font-bold text-[#0f3c2b]">Actualizar mi perfil</h1>
        <p class="text-lg text-[#5f6b54]">Configura tus preferencias para recibir mejores recomendaciones</p>
    </section>

    <!-- Wizard container -->
    <div class="min-h-[80vh] max-w-3xl mx-auto bg-white rounded-2xl shadow-md overflow-hidden reveal flex flex-col">
        <!-- Barra de progreso -->
        <div id="wizard-progress" class="flex justify-between px-6 py-4 text-sm text-gray-500 border-b">
            <span>Paso <span id="wizard-current-step">1</span> de 4</span>
            <span id="wizard-completion">25% completado</span>
        </div>

        <!-- Pasos del wizard -->
        <div id="wizard-steps" class="flex-1 space-y-6 overflow-y-auto">
            <!-- Paso 1: Espacio y luz -->
            <div class="wizard-step active p-8 transition-all duration-500" id="step-1">
                <h3 class="text-xl font-bold text-[#0f3c2b] mb-4">Paso 1: Tu espacio y luz</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tamaño adulto de planta preferido</label>
                        <select name="tamaño_adulto" id="wizard-tamanio"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#7cb22b] focus:scale-105"
                                required>
                            <option value="">Selecciona una opción</option>
                            <option value="pequena" <?php echo e(old('tamaño_adulto', optional($perfil)->tamaño_adulto) == 'pequena' ? 'selected' : ''); ?>>
                                Pequeña (Estante/Mesa)
                            </option>
                            <option value="mediana" <?php echo e(old('tamaño_adulto', optional($perfil)->tamaño_adulto) == 'mediana' ? 'selected' : ''); ?>>
                                Mediana (Habitación)
                            </option>
                            <option value="grande" <?php echo e(old('tamaño_adulto', optional($perfil)->tamaño_adulto) == 'grande' ? 'selected' : ''); ?>>
                                Grande (Jardín/Patio)
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Luz requerida en tu espacio</label>
                        <select name="luz_requerida" id="wizard-luz"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#7cb22b] focus:scale-105"
                                required>
                            <option value="">Selecciona una opción</option>
                            <option value="baja" <?php echo e(old('luz_requerida', optional($perfil)->luz_requerida) == 'baja' ? 'selected' : ''); ?>>
                                Baja (Sombra)
                            </option>
                            <option value="media" <?php echo e(old('luz_requerida', optional($perfil)->luz_requerida) == 'media' ? 'selected' : ''); ?>>
                                Media (Luz indirecta)
                            </option>
                            <option value="alta" <?php echo e(old('luz_requerida', optional($perfil)->luz_requerida) == 'alta' ? 'selected' : ''); ?>>
                                Alta (Mucha luz)
                            </option>
                            <option value="siempre_en_el_sol" <?php echo e(old('luz_requerida', optional($perfil)->luz_requerida) == 'siempre_en_el_sol' ? 'selected' : ''); ?>>
                                Siempre al sol
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Paso 2: Hábitos y ambiente -->
            <div class="wizard-step p-8 transition-all duration-500" id="step-2">
                <h3 class="text-xl font-bold text-[#0f3c2b] mb-4">Paso 2: Hábitos y ambiente</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Frecuencia de riego que prefieres</label>
                        <select name="frecuencia_riego" id="wizard-riego"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#7cb22b] focus:scale-105"
                                required>
                            <option value="">Selecciona una opción</option>
                            <option value="diario" <?php echo e(old('frecuencia_riego', optional($perfil)->frecuencia_riego) == 'diario' ? 'selected' : ''); ?>>
                                Diario
                            </option>
                            <option value="cada_3_dias" <?php echo e(old('frecuencia_riego', optional($perfil)->frecuencia_riego) == 'cada_3_dias' ? 'selected' : ''); ?>>
                                Cada 3 días
                            </option>
                            <option value="semanal" <?php echo e(old('frecuencia_riego', optional($perfil)->frecuencia_riego) == 'semanal' ? 'selected' : ''); ?>>
                                Semanal
                            </option>
                            <option value="quincenal" <?php echo e(old('frecuencia_riego', optional($perfil)->frecuencia_riego) == 'quincenal' ? 'selected' : ''); ?>>
                                Quincenal
                            </option>
                            <option value="mensualmente" <?php echo e(old('frecuencia_riego', optional($perfil)->frecuencia_riego) == 'mensualmente' ? 'selected' : ''); ?>>
                                Mensualmente
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de ambiente</label>
                        <select name="tipo_ambiente" id="wizard-ambiente"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#7cb22b]"
                                required>
                            <option value="">Selecciona una opción</option>
                            <option value="interiores" <?php echo e(old('tipo_ambiente', optional($perfil)->tipo_ambiente) == 'interiores' ? 'selected' : ''); ?>>
                                Interiores
                            </option>
                            <option value="exteriores" <?php echo e(old('tipo_ambiente', optional($perfil)->tipo_ambiente) == 'exteriores' ? 'selected' : ''); ?>>
                                Exteriores
                            </option>
                            <option value="ambos" <?php echo e(old('tipo_ambiente', optional($perfil)->tipo_ambiente) == 'ambos' ? 'selected' : ''); ?>>
                                Ambos
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Paso 3: Experiencia y estética -->
            <div class="wizard-step p-8 transition-all duration-500" id="step-3">
                <h3 class="text-lg font-bold text-[#0f3c2b] mb-4">Paso 3: Experiencia y estética</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nivel de experiencia en cuidado de plantas</label>
                        <select name="nivel_cuidado" id="wizard-nivel"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#7cb22b]"
                                required>
                            <option value="">Selecciona una opción</option>
                            <option value="principiante" <?php echo e(old('nivel_cuidado', optional($perfil)->nivel_cuidado) == 'principiante' ? 'selected' : ''); ?>>
                                Principiante
                            </option>
                            <option value="intermedio" <?php echo e(old('nivel_cuidado', optional($perfil)->nivel_cuidado) == 'intermedio' ? 'selected' : ''); ?>>
                                Intermedio
                            </option>
                            <option value="experto" <?php echo e(old('nivel_cuidado', optional($perfil)->nivel_cuidado) == 'experto' ? 'selected' : ''); ?>>
                                Experto
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Estética preferida</label>
                        <select name="estetica" id="wizard-estetica"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#7cb22b]"
                                required>
                            <option value="">Selecciona una opción</option>
                            <option value="follaje" <?php echo e(old('estetica', optional($perfil)->estetica) == 'follaje' ? 'selected' : ''); ?>>
                                Follaje
                            </option>
                            <option value="flor" <?php echo e(old('estetica', optional($perfil)->estetica) == 'flor' ? 'selected' : ''); ?>>
                                Flor
                            </option>
                            <option value="colgantes" <?php echo e(old('estetica', optional($perfil)->estetica) == 'colgantes' ? 'selected' : ''); ?>>
                                Colgantes
                            </option>
                            <option value="suculenta" <?php echo e(old('estetica', optional($perfil)->estetica) == 'suculenta' ? 'selected' : ''); ?>>
                                Suculenta
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Paso 4: Seguridad -->
            <div class="wizard-step p-8 transition-all duration-500" id="step-4">
                <h3 class="text-lg font-bold text-[#0f3c2b] mb-4">Paso 4: Seguridad</h3>
                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="toxicidad" id="wizard-toxicidad" value="1"
                                   class="w-4 h-4 text-[#7cb22b] bg-gray-100 border-gray-300 rounded focus:ring-2 focus:ring-[#7cb22b]"
                                   <?php echo e(old('toxicidad', optional($perfil)->toxicidad) ? 'checked' : ''); ?>>
                        </div>
                        <div class="ml-3 text-start">
                            <label for="wizard-toxicidad" class="text-sm font-medium text-gray-700">
                                Prefiero evitar plantas tóxicas (por seguridad de mascotas o niños)
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Barra de acciones -->
        <div class="flex w-full space-x-3 px-6 py-4 bg-gray-50">
            <button id="wizard-prev"
                    class="px-4 py-2 bg-gray-200 text-gray-600 rounded-md hover:bg-gray-300 hover:-translate-y-1 transition-transform duration-200 disabled:opacity-50"
                    disabled>
                Anterior
            </button>
            <button id="wizard-next"
                    class="px-6 py-2 bg-[#7cb22b] text-white rounded-md hover:bg-[#6eab26] hover:-translate-y-1 transition-transform duration-200">
                Siguiente
            </button>
        </div>
    </div>
</main>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    const PERFIL_UPDATE_URL = "<?php echo e(route('perfil.update')); ?>";
    const PERFIL_EDIT_URL = "<?php echo e(route('perfil.edit')); ?>";
    const CLIENT_HOME_URL = "<?php echo e(route('home')); ?>";
    const CSRF_TOKEN = "<?php echo e(csrf_token()); ?>";
</script>
<?php $__env->stopPush(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Adaptia\resources\views/perfiles/edit.blade.php ENDPATH**/ ?>