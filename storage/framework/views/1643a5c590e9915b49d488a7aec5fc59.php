<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="<?php echo e(asset('images/logo.png')); ?>">
    <?php echo app('Illuminate\Foundation\Vite')('resources/css/app.css'); ?>
    <title>Crear Sensor</title>
</head>

<body class="flex font-sans h-screen">

    <?php echo $__env->make('partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <main class="flex flex-5 flex-col px-25 py-10 bg-[#fbfbfb] h-full gap-4">

        <section>
            <a class="flex items-center text-[#0c251c] font-bold text-[0.8em]" href="<?php echo e(route('sensores.index')); ?>">
                <img class="w-[3%] h-auto" src="<?php echo e(asset('images/regreso-flecha.png')); ?>" alt="">Volver a Sensores
            </a>
        </section>

        <section class="flex items-center justify-between mb-8">
            <div class="flex-4">
                <h1 class="text-[#103928] font-bold text-[2.5em]">Crear Sensor</h1>
                <p class="text-[#8b8d8f] text-[1em]">Registra un nuevo sensor para monitorear tus plantas.</p>
            </div>
        </section>

        <section class="flex justify-center">
            <div class="flex flex-col p-10 bg-[#fefdfe] rounded-[20px] shadow-xl w-full max-w-2xl gap-8 border border-[#ecedea]">
                <form action="<?php echo e(route('sensores.store')); ?>" method="POST" class="flex flex-col gap-6">
                    <?php echo csrf_field(); ?>

                    <div class="flex flex-col gap-2">
                        <label for="identificador_fisico" class="text-[#304e42] font-semibold text-[1.1em]">Identificador Físico</label>
                        <input type="text" name="identificador_fisico" id="identificador_fisico"
                               class="p-4 bg-[#f3f5f3] border-2 <?php $__errorArgs = ['identificador_fisico'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php else: ?> border-[#ecedea] <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> rounded-[10px] outline-none focus:border-[#629f22] transition duration-300"
                               placeholder="Ej: SENSOR-001, TEMP-HUM-02..."
                               value="<?php echo e(old('identificador_fisico')); ?>" required>
                        <?php $__errorArgs = ['identificador_fisico'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <span class="text-red-500 text-[0.9em] font-medium"><?php echo e($message); ?></span>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label for="estado" class="text-[#304e42] font-semibold text-[1.1em]">Estado</label>
                        <select name="estado" id="estado"
                                class="p-4 bg-[#f3f5f3] border-2 <?php $__errorArgs = ['estado'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php else: ?> border-[#ecedea] <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> rounded-[10px] outline-none focus:border-[#629f22] transition duration-300">
                            <option value="">Seleccione estado</option>
                            <option value="activo" <?php echo e(old('estado') == 'activo' ? 'selected' : ''); ?>>Activo</option>
                            <option value="inactivo" <?php echo e(old('estado') == 'inactivo' ? 'selected' : ''); ?>>Inactivo</option>
                        </select>
                        <?php $__errorArgs = ['estado'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <span class="text-red-500 text-[0.9em] font-medium"><?php echo e($message); ?></span>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="flex justify-end mt-4">
                        <button type="submit"
                                class="flex items-center justify-between py-3 px-8 bg-[#629f22] text-[#fbfbfb] rounded-[10px] gap-3 hover:scale-110 transition duration-300 font-bold">
                            <img class="w-[1.2em] h-auto" src="<?php echo e(asset('images/anadir.png')); ?>" alt="Añadir">
                            Guardar Sensor
                        </button>
                    </div>
                </form>
            </div>
        </section>

    </main>
</body>

</html><?php /**PATH C:\xampp\htdocs\Adaptia\resources\views/sensores/create.blade.php ENDPATH**/ ?>