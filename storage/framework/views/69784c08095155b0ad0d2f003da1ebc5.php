<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="<?php echo e(asset('images/logo.png')); ?>">
    <?php echo app('Illuminate\Foundation\Vite')('resources/css/app.css'); ?>
    <title>Sensores</title>
</head>

<body class="flex font-sans h-screen">

    <?php echo $__env->make('partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <main class="flex flex-5 flex-col p-25 bg-[#fbfbfb] h-full gap-4">
        <section class="flex items-center justify-between">
            <div class="flex-4">
                <h1 class="text-[#103928] font-bold text-[2.5em]">Gestión de Sensores</h1>
                <p class="text-[#8b8d8f] text-[1em]">Administra los sensores registrados en Adaptia.</p>
            </div>
            <div class="flex-1">
                <a href="<?php echo e(route('sensores.create')); ?>"
                    class="flex items-center justify-between py-3 px-6 bg-[#629f22] text-[#fbfbfb] rounded-[10px] gap-3 hover:scale-110 transition duration-300">
                    <img class="w-[10%] h-auto" src="<?php echo e(asset('images/anadir.png')); ?>" alt="Añadir">
                    Crear Sensor
                </a>
            </div>
        </section>

        <?php if(session('success')): ?>
            <div class="p-4 bg-[#d4edda] text-[#155724] border border-[#c3e6cb] rounded-[10px]" role="status">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        <?php if(session('sensor_credencial')): ?>
            <section class="rounded-[10px] border border-[#f0d98a] bg-[#fff9df] p-5" role="alert">
                <h2 class="font-bold text-[#6b5011]">Credencial del ESP32 — copiar ahora</h2>
                <p class="mt-2 text-sm text-[#6b5011]">
                    Esta clave solo se muestra una vez. Configúrala en el dispositivo y guárdala de forma segura.
                    No la compartas ni la subas al repositorio. Si la pierdes, tendrás que generar otra.
                </p>
                <code class="mt-3 block break-all rounded bg-white p-3 font-mono text-sm text-[#304e42]"><?php echo e(session('sensor_credencial')); ?></code>
            </section>
        <?php endif; ?>

        <?php if($errors->any()): ?>
            <div class="p-4 bg-[#f8d7da] text-[#721c24] border border-[#f5c6cb] rounded-[10px]" role="alert">
                <p class="font-bold">No se pudo completar la operación:</p>
                <ul class="list-disc pl-6">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <section class="flex p-8 bg-[#fefdfe] text-[#304e42] rounded-[10px] shadow-xl">
            <div class="w-full overflow-x-auto">
                <table class="w-full min-w-[640px]">
                    <thead class="bg-[#f3f5f3] text-left">
                        <tr>
                            <th class="text-center rounded-tl-[20px] w-20 p-4">Id</th>
                            <th class="p-4">Identificador Físico</th>
                            <th class="p-4">Estado</th>
                            <th class="rounded-tr-[20px] p-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $sensores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sensor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="border-t-2 border-[#c2c4c7]">
                                <td class="p-4 font-bold text-center"><?php echo e($sensor->id); ?></td>
                                <td class="p-4"><?php echo e($sensor->identificador_fisico); ?></td>
                                <td class="p-4">
                                    <?php if($sensor->estado === 'activo'): ?>
                                        <span class="px-3 py-1 bg-[#d4edda] text-[#155724] rounded-full text-[0.9em]">Activo</span>
                                    <?php else: ?>
                                        <span class="px-3 py-1 bg-[#f8d7da] text-[#721c24] rounded-full text-[0.9em]">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4">
                                    <div class="flex flex-wrap gap-3">
                                        <a class="inline-flex items-center justify-center gap-2 rounded-[10px] bg-[#f5f9f0] px-4 py-2 font-bold text-[#77a856] transition duration-300 hover:scale-105"
                                           href="<?php echo e(route('sensores.edit', $sensor->id)); ?>">
                                            <img class="h-4 w-4" src="<?php echo e(asset('images/editar.png')); ?>" alt="">
                                            Editar
                                        </a>
                                        <form action="<?php echo e(route('sensores.destroy', $sensor->id)); ?>" method="POST">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit"
                                                    onclick="return confirm('¿Estás seguro de eliminar este sensor?');"
                                                    class="inline-flex items-center justify-center gap-2 rounded-[10px] bg-[#fdebeb] px-4 py-2 font-bold text-[#f06f73] transition duration-300 hover:scale-105">
                                                <img class="h-4 w-4" src="<?php echo e(asset('images/eliminar.png')); ?>" alt="">
                                                Eliminar
                                            </button>
                                        </form>
                                    <?php if(auth()->user()->rol === 'administrador'): ?>
                                        <form action="<?php echo e(route('sensores.credencial.generar', $sensor->id)); ?>" method="POST">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit"
                                                    onclick="return confirm('Generar una credencial nueva invalidará la anterior. ¿Deseas continuar?');"
                                                    class="inline-flex items-center justify-center rounded-[10px] bg-[#eff6e8] px-4 py-2 font-bold text-[#52752d] transition hover:bg-[#dceccc]">
                                                <?php echo e($sensor->token_hash ? 'Renovar credencial' : 'Generar credencial'); ?>

                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="4" class="p-8 text-center text-[#8b8d8f]">
                                    No hay sensores registrados todavía.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

    </main>
</body>

</html><?php /**PATH C:\xampp\htdocs\Adaptia\resources\views/sensores/index.blade.php ENDPATH**/ ?>