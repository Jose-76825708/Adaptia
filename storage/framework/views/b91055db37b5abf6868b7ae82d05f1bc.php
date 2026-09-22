<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="<?php echo e(asset('images/logo.png')); ?>">
    <?php echo app('Illuminate\Foundation\Vite')('resources/css/app.css'); ?>
    <title>Editar Planta</title>
</head>

<body class="flex font-sans h-screen">

    <?php echo $__env->make('partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <main class="flex flex-5 flex-col px-25 py-10 bg-[#fbfbfb] h-full gap-4 overflow-y-auto">

        <section>
            <a class="flex items-center text-[#0c251c] font-bold text-[0.8em]" href="<?php echo e(route('plantas.index')); ?>">
                <img class="w-[3%] h-auto" src="<?php echo e(asset('images/regreso-flecha.png')); ?>" alt="">Volver a Plantas
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
                <form action="<?php echo e(route('plantas.update', $find->id)); ?>" method="POST" class="flex flex-col gap-10">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>

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
                                       class="p-4 bg-[#f3f5f3] border-2 <?php $__errorArgs = ['nombre'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php else: ?> border-[#ecedea] <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> rounded-[10px] outline-none focus:border-[#629f22] transition duration-300"
                                       placeholder="Ej: Monstera Deliciosa" value="<?php echo e(old('nombre', $find->nombre)); ?>" required>
                                <?php $__errorArgs = ['nombre'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-[0.9em] font-medium"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="tipo_planta_id" class="text-[#304e42] font-semibold text-[1.1em]">Tipo de Planta</label>
                                <select name="tipo_planta_id" id="tipo_planta_id"
                                        class="p-4 bg-[#f3f5f3] border-2 <?php $__errorArgs = ['tipo_planta_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php else: ?> border-[#ecedea] <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> rounded-[10px] outline-none focus:border-[#629f22] transition duration-300" required>
                                    <option value="" disabled selected>Seleccione un tipo...</option>
                                    <?php $__currentLoopData = $tipos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tipo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($tipo->id); ?>" <?php echo e(old('tipo_planta_id', $find->tipo_planta_id) == $tipo->id ? 'selected' : ''); ?>><?php echo e($tipo->nombre); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['tipo_planta_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-[0.9em] font-medium"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="precio" class="text-[#304e42] font-semibold text-[1.1em]">Precio (S/.)</label>
                                <input type="number" step="0.01" name="precio" id="precio"
                                       class="p-4 bg-[#f3f5f3] border-2 <?php $__errorArgs = ['precio'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php else: ?> border-[#ecedea] <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> rounded-[10px] outline-none focus:border-[#629f22] transition duration-300"
                                       placeholder="0.00" value="<?php echo e(old('precio', $find->precio)); ?>" required>
                                <?php $__errorArgs = ['precio'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-[0.9em] font-medium"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
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
                                        class="p-4 bg-[#f3f5f3] border-2 <?php $__errorArgs = ['luz_requerida'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php else: ?> border-[#ecedea] <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> rounded-[10px] outline-none focus:border-[#629f22] transition duration-300" required>
                                    <option value="" disabled selected>Seleccione luz...</option>
                                    <option value="baja" <?php echo e(old('luz_requerida', $find->luz_requerida) == 'baja' ? 'selected' : ''); ?>>Baja</option>
                                    <option value="media" <?php echo e(old('luz_requerida', $find->luz_requerida) == 'media' ? 'selected' : ''); ?>>Media</option>
                                    <option value="alta" <?php echo e(old('luz_requerida', $find->luz_requerida) == 'alta' ? 'selected' : ''); ?>>Alta</option>
                                    <option value="siempre_en_el_sol" <?php echo e(old('luz_requerida', $find->luz_requerida) == 'siempre_en_el_sol' ? 'selected' : ''); ?>>Siempre al sol</option>
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

                            <div class="flex flex-col gap-2">
                                <label for="tamaño_adulto" class="text-[#304e42] font-semibold text-[1.1em]">Espacio Requerido</label>
                                <select name="tamaño_adulto" id="tamaño_adulto"
                                        class="p-4 bg-[#f3f5f3] border-2 <?php $__errorArgs = ['tamaño_adulto'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php else: ?> border-[#ecedea] <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> rounded-[10px] outline-none focus:border-[#629f22] transition duration-300" required>
                                    <option value="" disabled selected>Seleccione espacio...</option>
                                    <option value="pequena" <?php echo e(old('tamaño_adulto', $find->tamaño_adulto) == 'pequena' ? 'selected' : ''); ?>>Pequeña</option>
                                    <option value="mediana" <?php echo e(old('tamaño_adulto', $find->tamaño_adulto) == 'mediana' ? 'selected' : ''); ?>>Mediana</option>
                                    <option value="grande" <?php echo e(old('tamaño_adulto', $find->tamaño_adulto) == 'grande' ? 'selected' : ''); ?>>Grande</option>
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
                                    <option value="" disabled selected>Seleccione ambiente...</option>
                                    <option value="interiores" <?php echo e(old('tipo_ambiente', $find->tipo_ambiente) == 'interiores' ? 'selected' : ''); ?>>Interiores</option>
                                    <option value="exteriores" <?php echo e(old('tipo_ambiente', $find->tipo_ambiente) == 'exteriores' ? 'selected' : ''); ?>>Exteriores</option>
                                    <option value="ambos" <?php echo e(old('tipo_ambiente', $find->tipo_ambiente) == 'ambos' ? 'selected' : ''); ?>>Ambos</option>
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
                                    <option value="" disabled selected>Seleccione riego...</option>
                                    <option value="diario" <?php echo e(old('frecuencia_riego', $find->frecuencia_riego) == 'diario' ? 'selected' : ''); ?>>Diario</option>
                                    <option value="cada_3_dias" <?php echo e(old('frecuencia_riego', $find->frecuencia_riego) == 'cada_3_dias' ? 'selected' : ''); ?>>Cada 3 días</option>
                                    <option value="semanal" <?php echo e(old('frecuencia_riego', $find->frecuencia_riego) == 'semanal' ? 'selected' : ''); ?>>Semanal</option>
                                    <option value="quincenal" <?php echo e(old('frecuencia_riego', $find->frecuencia_riego) == 'quincenal' ? 'selected' : ''); ?>>Quincenal</option>
                                    <option value="mensualmente" <?php echo e(old('frecuencia_riego', $find->frecuencia_riego) == 'mensualmente' ? 'selected' : ''); ?>>Mensualmente</option>
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

                            <div class="flex flex-col gap-2">
                                <label for="estetica" class="text-[#304e42] font-semibold text-[1.1em]">Estética</label>
                                <select name="estetica" id="estetica"
                                        class="p-4 bg-[#f3f5f3] border-2 <?php $__errorArgs = ['estetica'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php else: ?> border-[#ecedea] <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> rounded-[10px] outline-none focus:border-[#629f22] transition duration-300" required>
                                    <option value="" disabled selected>Seleccione estética...</option>
                                    <option value="follaje" <?php echo e(old('estetica', $find->estetica) == 'follaje' ? 'selected' : ''); ?>>Follaje</option>
                                    <option value="flor" <?php echo e(old('estetica', $find->estetica) == 'flor' ? 'selected' : ''); ?>>Flor</option>
                                    <option value="colgantes" <?php echo e(old('estetica', $find->estetica) == 'colgantes' ? 'selected' : ''); ?>>Colgantes</option>
                                    <option value="suculentas" <?php echo e(old('estetica', $find->estetica) == 'suculentas' ? 'selected' : ''); ?>>Suculenta</option>
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
                                    <option value="" disabled selected>Seleccione nivel...</option>
                                    <option value="principiante" <?php echo e(old('nivel_cuidado', $find->nivel_cuidado) == 'principiante' ? 'selected' : ''); ?>>Principiante</option>
                                    <option value="intermedio" <?php echo e(old('nivel_cuidado', $find->nivel_cuidado) == 'intermedio' ? 'selected' : ''); ?>>Intermedio</option>
                                    <option value="experto" <?php echo e(old('nivel_cuidado', $find->nivel_cuidado) == 'experto' ? 'selected' : ''); ?>>Experto</option>
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

                            <div class="flex items-center gap-3 py-4">
                                <input type="checkbox" name="es_toxica" id="es_toxica" value="1"
                                       class="w-6 h-6 accent-[#629f22] cursor-pointer" <?php echo e(old('es_toxica', $find->es_toxica) ? 'checked' : ''); ?>>
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
                                       class="p-4 bg-[#f3f5f3] border-2 <?php $__errorArgs = ['stock_actual'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php else: ?> border-[#ecedea] <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> rounded-[10px] outline-none focus:border-[#629f22] transition duration-300"
                                       placeholder="0" value="<?php echo e(old('stock_actual', $find->stock_actual)); ?>" required>
                                <?php $__errorArgs = ['stock_actual'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-[0.9em] font-medium"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="stock_minimo" class="text-[#304e42] font-semibold text-[1.1em]">Stock Mínimo (Alerta)</label>
                                <input type="number" name="stock_minimo" id="stock_minimo"
                                       class="p-4 bg-[#f3f5f3] border-2 <?php $__errorArgs = ['stock_minimo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php else: ?> border-[#ecedea] <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> rounded-[10px] outline-none focus:border-[#629f22] transition duration-300"
                                       placeholder="0" value="<?php echo e(old('stock_minimo', $find->stock_minimo)); ?>" required>
                                <?php $__errorArgs = ['stock_minimo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-[0.9em] font-medium"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end mt-6">
                        <button type="submit"
                                class="flex items-center justify-between py-4 px-10 bg-[#629f22] text-[#fbfbfb] rounded-[10px] gap-3 hover:scale-110 transition duration-300 font-bold text-[1.1em]">
                            <img class="w-[1.2em] h-auto" src="<?php echo e(asset('images/anadir.png')); ?>" alt="Actualizar">
                            Actualizar Planta en Catálogo
                        </button>
                    </div>
                </form>
            </div>
        </section>

    </main>

</body>

</html>
<?php /**PATH C:\xampp\htdocs\Adaptia\resources\views/plantas/edit.blade.php ENDPATH**/ ?>