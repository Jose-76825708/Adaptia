<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="<?php echo e(asset('images/logo.png')); ?>">
    <?php echo app('Illuminate\Foundation\Vite')('resources/css/app.css'); ?>
    <title>Mi Perfil - Adaptia</title>
</head>

<body class="flex font-sans h-screen">

    <?php echo $__env->make('partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <main class="flex flex-5 flex-col px-25 py-10 bg-[#fbfbfb] h-full gap-4 overflow-y-auto">

        <section>
            <a class="flex items-center text-[#0c251c] font-bold text-[0.8em]" href="<?php echo e(route('home')); ?>">
                <img class="w-[3%] h-auto" src="<?php echo e(asset('images/regreso-flecha.png')); ?>" alt="">Volver al Inicio
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

                <?php if(session('success')): ?>
                    <div class="p-4 bg-green-100 text-green-700 rounded-[10px] border border-green-200 text-center font-medium">
                        <?php echo e(session('success')); ?>

                    </div>
                <?php endif; ?>

                <form action="<?php echo e(route('perfil.update')); ?>" method="POST" class="flex flex-col gap-6">
                    <?php echo csrf_field(); ?>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- TAMAÑO ADULTO -->
                        <div class="flex flex-col gap-2">
                            <label for="tamaño_adulto" class="text-[#304e42] font-semibold text-[1.1em]">Tamaño Adulto de la Planta</label>
                            <select name="tamaño_adulto" id="tamaño_adulto"
                                    class="p-4 bg-[#f3f5f3] border-2 <?php $__errorArgs = ['tamaño_adulto'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php else: ?> border-[#ecedea] <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> rounded-[10px] outline-none focus:border-[#629f22] transition duration-300" required>
                                <option value="" disabled <?php echo e(is_null($perfil) || old('tamaño_adulto', $perfil->tamaño_adulto) == '' ? 'selected' : ''); ?>>Seleccione tamaño...</option>
                                <option value="pequena" <?php echo e(old('tamaño_adulto', $perfil->tamaño_adulto ?? '') == 'pequena' ? 'selected' : ''); ?>>Pequeña (Estante/Mesa)</option>
                                <option value="mediana" <?php echo e(old('tamaño_adulto', $perfil->tamaño_adulto ?? '') == 'mediana' ? 'selected' : ''); ?>>Mediana (Habitación)</option>
                                <option value="grande" <?php echo e(old('tamaño_adulto', $perfil->tamaño_adulto ?? '') == 'grande' ? 'selected' : ''); ?>>Grande (Jardín/Patio)</option>
                            </select>
                            <?php $__errorArgs = ['tamaño_adulto'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-[0.9em] font-medium"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <!-- LUZ_REQUERIDA -->
                        <div class="flex flex-col gap-2">
                            <label for="luz_requerida" class="text-[#304e42] font-semibold text-[1.1em]">Luz Requerida</label>
                            <select name="luz_requerida" id="luz_requerida"
                                    class="p-4 bg-[#f3f5f3] border-2 <?php $__errorArgs = ['luz_requerida'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php else: ?> border-[#ecedea] <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> rounded-[10px] outline-none focus:border-[#629f22] transition duration-300" required>
                                <option value="" disabled <?php echo e(is_null($perfil) || old('luz_requerida', $perfil->luz_requerida) == '' ? 'selected' : ''); ?>>Seleccione luz requerida...</option>
                                <option value="baja" <?php echo e(old('luz_requerida', $perfil->luz_requerida ?? '') == 'baja' ? 'selected' : ''); ?>>Baja (Sombra)</option>
                                <option value="media" <?php echo e(old('luz_requerida', $perfil->luz_requerida ?? '') == 'media' ? 'selected' : ''); ?>>Media (Luz indirecta)</option>
                                <option value="alta" <?php echo e(old('luz_requerida', $perfil->luz_requerida ?? '') == 'alta' ? 'selected' : ''); ?>>Alta (Mucha luz)</option>
                                <option value="siempre_en_el_sol" <?php echo e(old('luz_requerida', $perfil->luz_requerida ?? '') == 'siempre_en_el_sol' ? 'selected' : ''); ?>>Siempre al sol</option>
                            </select>
                            <?php $__errorArgs = ['luz_requerida'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-[0.9em] font-medium"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <!-- FRECUENCIA DE RIEGO -->
                        <div class="flex flex-col gap-2">
                            <label for="frecuencia_riego" class="text-[#304e42] font-semibold text-[1.1em]">Frecuencia de Riego</label>
                            <select name="frecuencia_riego" id="frecuencia_riego"
                                    class="p-4 bg-[#f3f5f3] border-2 <?php $__errorArgs = ['frecuencia_riego'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php else: ?> border-[#ecedea] <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> rounded-[10px] outline-none focus:border-[#629f22] transition duration-300" required>
                                <option value="" disabled <?php echo e(is_null($perfil) || old('frecuencia_riego', $perfil->frecuencia_riego) == '' ? 'selected' : ''); ?>>Seleccione frecuencia...</option>
                                <option value="diario" <?php echo e(old('frecuencia_riego', $perfil->frecuencia_riego ?? '') == 'diario' ? 'selected' : ''); ?>>Diario</option>
                                <option value="cada_3_dias" <?php echo e(old('frecuencia_riego', $perfil->frecuencia_riego ?? '') == 'cada_3_dias' ? 'selected' : ''); ?>>Cada 3 días</option>
                                <option value="semanal" <?php echo e(old('frecuencia_riego', $perfil->frecuencia_riego ?? '') == 'semanal' ? 'selected' : ''); ?>>Semanal</option>
                                <option value="quincenal" <?php echo e(old('frecuencia_riego', $perfil->frecuencia_riego ?? '') == 'quincenal' ? 'selected' : ''); ?>>Quincenal</option>
                                <option value="mensualmente" <?php echo e(old('frecuencia_riego', $perfil->frecuencia_riego ?? '') == 'mensualmente' ? 'selected' : ''); ?>>Mensualmente</option>
                            </select>
                            <?php $__errorArgs = ['frecuencia_riego'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-[0.9em] font-medium"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <!-- TIPO DE AMBIENTE -->
                        <div class="flex flex-col gap-2">
                            <label for="tipo_ambiente" class="text-[#304e42] font-semibold text-[1.1em]">Tipo de Ambiente</label>
                            <select name="tipo_ambiente" id="tipo_ambiente"
                                    class="p-4 bg-[#f3f5f3] border-2 <?php $__errorArgs = ['tipo_ambiente'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php else: ?> border-[#ecedea] <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> rounded-[10px] outline-none focus:border-[#629f22] transition duration-300" required>
                                <option value="" disabled <?php echo e(is_null($perfil) || old('tipo_ambiente', $perfil->tipo_ambiente) == '' ? 'selected' : ''); ?>>Seleccione ambiente...</option>
                                <option value="interiores" <?php echo e(old('tipo_ambiente', $perfil->tipo_ambiente ?? '') == 'interiores' ? 'selected' : ''); ?>>Interiores</option>
                                <option value="exteriores" <?php echo e(old('tipo_ambiente', $perfil->tipo_ambiente ?? '') == 'exteriores' ? 'selected' : ''); ?>>Exteriores</option>
                                <option value="ambos" <?php echo e(old('tipo_ambiente', $perfil->tipo_ambiente ?? '') == 'ambos' ? 'selected' : ''); ?>>Ambos</option>
                            </select>
                            <?php $__errorArgs = ['tipo_ambiente'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-[0.9em] font-medium"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- NIVEL DE CUIDADO -->
                        <div class="flex flex-col gap-2">
                            <label for="nivel_cuidado" class="text-[#304e42] font-semibold text-[1.1em]">Nivel de Cuidado</label>
                            <select name="nivel_cuidado" id="nivel_cuidado"
                                    class="p-4 bg-[#f3f5f3] border-2 <?php $__errorArgs = ['nivel_cuidado'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php else: ?> border-[#ecedea] <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> rounded-[10px] outline-none focus:border-[#629f22] transition duration-300" required>
                                <option value="" disabled <?php echo e(is_null($perfil) || old('nivel_cuidado', $perfil->nivel_cuidado) == '' ? 'selected' : ''); ?>>Seleccione nivel...</option>
                                <option value="principiante" <?php echo e(old('nivel_cuidado', $perfil->nivel_cuidado ?? '') == 'principiante' ? 'selected' : ''); ?>>Principiante</option>
                                <option value="intermedio" <?php echo e(old('nivel_cuidado', $perfil->nivel_cuidado ?? '') == 'intermedio' ? 'selected' : ''); ?>>Intermedio</option>
                                <option value="experto" <?php echo e(old('nivel_cuidado', $perfil->nivel_cuidado ?? '') == 'experto' ? 'selected' : ''); ?>>Experto</option>
                            </select>
                            <?php $__errorArgs = ['nivel_cuidado'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-[0.9em] font-medium"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <!-- TOXICIDAD -->
                        <div class="flex items-center gap-3 py-6">
                            <input type="checkbox" name="toxicidad" id="toxicidad" value="1"
                                   class="w-6 h-6 accent-[#629f22] cursor-pointer" <?php echo e(old('toxicidad', $perfil->toxicidad ?? false) ? 'checked' : ''); ?>>
                            <label for="toxicidad" class="text-[#304e42] font-semibold text-[1.1em] cursor-pointer">
                                Prefiere evitar plantas tóxicas
                            </label>
                        </div>

                        <!-- ESTETICA -->
                        <div class="flex flex-col gap-2">
                            <label for="estetica" class="text-[#304e42] font-semibold text-[1.1em]">Estética Preferida</label>
                            <select name="estetica" id="estetica"
                                    class="p-4 bg-[#f3f5f3] border-2 <?php $__errorArgs = ['estetica'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php else: ?> border-[#ecedea] <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> rounded-[10px] outline-none focus:border-[#629f22] transition duration-300" required>
                                <option value="" disabled <?php echo e(is_null($perfil) || old('estetica', $perfil->estetica) == '' ? 'selected' : ''); ?>>Seleccione estética...</option>
                                <option value="follaje" <?php echo e(old('estetica', $perfil->estetica ?? '') == 'follaje' ? 'selected' : ''); ?>>Follaje</option>
                                <option value="flor" <?php echo e(old('estetica', $perfil->estetica ?? '') == 'flor' ? 'selected' : ''); ?>>Flor</option>
                                <option value="colgantes" <?php echo e(old('estetica', $perfil->estetica ?? '') == 'colgantes' ? 'selected' : ''); ?>>Colgantes</option>
                                <option value="suculenta" <?php echo e(old('estetica', $perfil->estetica ?? '') == 'suculenta' ? 'selected' : ''); ?>>Suculenta</option>
                            </select>
                            <?php $__errorArgs = ['estetica'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-[0.9em] font-medium"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
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
<?php /**PATH C:\xampp\htdocs\Adaptia\resources\views/perfiles/edit.blade.php ENDPATH**/ ?>