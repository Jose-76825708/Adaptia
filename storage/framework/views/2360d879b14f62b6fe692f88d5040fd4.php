<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="<?php echo e(asset('images/logo.png')); ?>">
    <?php echo app('Illuminate\Foundation\Vite')('resources/css/app.css'); ?>
    <title>Movimientos de Inventario - Adaptia</title>
</head>
<body class="flex font-sans h-screen">
    <?php echo $__env->make('partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <main class="flex flex-5 flex-col p-25 bg-[#fbfbfb] h-full gap-4">
        <section class="flex items-center justify-between">
            <div class="flex-4">
                <h1 class="text-[#103928] font-bold text-[2.5em]">Movimientos de Inventario</h1>
                <p class="text-[#8b8d8f] text-[1em]">Registra las entradas y salidas de stock de tus plantas.</p>
            </div>
            <div class="flex-1">
                <a href="<?php echo e(route('movimientos-inventario.create')); ?>"
                    class="flex items-center justify-between py-3 px-6 bg-[#629f22] text-[#fbfbfb] rounded-[10px] gap-3 hover:scale-110 transition duration-300">
                    <img class="w-[10%] h-auto" src="<?php echo e(asset('images/anadir.png')); ?>" alt="Añadir">
                    Registrar Movimiento
                </a>
            </div>
        </section>

        <?php if(session('success')): ?>
            <div class="p-4 bg-green-100 text-green-700 rounded-[10px] border border-green-200 text-center font-medium">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        <?php if(session('error')): ?>
            <div class="p-4 bg-red-100 text-red-700 rounded-[10px] border border-red-200 text-center font-medium">
                <?php echo e(session('error')); ?>

            </div>
        <?php endif; ?>

        <section class="flex p-8 bg-[#fefdfe] text-[#304e42] rounded-[10px] shadow-xl overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-[#f3f5f3]">
                    <tr>
                        <th class="text-center rounded-tl-[20px] p-4 w-10">#</th>
                        <th class="p-4">Planta</th>
                        <th class="p-4">Tipo</th>
                        <th class="p-4">Cantidad</th>
                        <th class="p-4">Usuario</th>
                        <th class="p-4">Fecha</th>
                        <th class="rounded-tr-[20px] p-4 w-64 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $movimientos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $movimiento): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="border-t-2 border-[#c2c4c7]">
                            <td class="p-4 font-bold text-center"><?php echo e($loop->iteration); ?></td>
                            <td class="p-4"><?php echo e($movimiento->planta->nombre); ?></td>
                            <td class="p-4">
                                <span class="px-2 py-1 rounded-full text-xs font-bold
                                    <?php echo e($movimiento->tipo == 'entrada' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600'); ?>">
                                    <?php echo e(ucfirst($movimiento->tipo)); ?>

                                </span>
                            </td>
                            <td class="p-4"><?php echo e($movimiento->cantidad); ?></td>
                            <td class="p-4"><?php echo e($movimiento->user->name); ?></td>
                            <td class="p-4"><?php echo e($movimiento->created_at->format('d/m/Y H:i')); ?></td>
                            <td class="flex gap-2 p-4 justify-center">
                                <a href="<?php echo e(route('movimientos-inventario.edit', $movimiento->id)); ?>"
                                    class="flex flex-1 items-center justify-center gap-2 bg-[#f5f9f0] py-1 text-[#77a856] font-bold rounded-[8px] hover:scale-105 transition duration-200">
                                    <img class="w-3 h-auto" src="<?php echo e(asset('images/editar.png')); ?>" alt=""> Editar
                                </a>
                                <form action="<?php echo e(route('movimientos-inventario.destroy', $movimiento->id)); ?>" method="POST" class="flex flex-1">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit"
                                        class="flex flex-1 items-center justify-center gap-2 bg-[#fdebeb] py-1 text-[#f06f73] font-bold rounded-[8px] hover:scale-105 transition duration-200"
                                        onclick="return confirm('¿Estás seguro de eliminar este movimiento de inventario?');">
                                        <img class="w-3 h-auto" src="<?php echo e(asset('images/eliminar.png')); ?>" alt=""> Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="p-4 text-center text-[#8b8d8f]">
                                No hay movimientos de inventario registrados.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html><?php /**PATH C:\xampp\htdocs\Adaptia\resources\views/movimientos-inventario/index.blade.php ENDPATH**/ ?>