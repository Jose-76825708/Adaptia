<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="<?php echo e(asset('images/logo.png')); ?>">
    <?php echo app('Illuminate\Foundation\Vite')('resources/css/app.css'); ?>
    <title>Gestión de Plantas</title>
</head>

<body class="flex font-sans h-screen">

    <?php echo $__env->make('partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <main class="flex flex-5 flex-col p-25 bg-[#fbfbfb] h-full gap-4">
        <section class="flex items-center justify-between">
            <div class="flex-4">
                <h1 class="text-[#103928] font-bold text-[2.5em]">Gestión de Plantas</h1>
                <p class="text-[#8b8d8f] text-[1em]">Administra el catálogo completo de plantas y su disponibilidad en Adaptia.</p>
            </div>
            <div class="flex-1">
                <a href="<?php echo e(route('plantas.create')); ?>"
                    class="flex items-center justify-between py-3 px-6 bg-[#629f22] text-[#fbfbfb] rounded-[10px] gap-3 hover:scale-110 transition duration-300">
                    <img class="w-[10%] h-auto" src="<?php echo e(asset('images/anadir.png')); ?>" alt="Añadir">
                    Crear Planta
                </a>
            </div>
        </section>

        <section class="flex p-8 bg-[#fefdfe] text-[#304e42] rounded-[10px] shadow-xl overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-[#f3f5f3]">
                    <tr>
                        <th class="text-center rounded-tl-[20px] p-4 w-20">#</th>
                        <th class="p-4">Nombre</th>
                        <th class="p-4">Tipo</th>
                        <th class="p-4">Precio</th>
                        <th class="p-4">Stock</th>
                        <th class="p-4">Cuidado</th>
                        <th class="rounded-tr-[20px] p-4 w-64 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $planta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="border-t-2 border-[#c2c4c7]">
                            <td class="p-4 font-bold text-center"><?php echo e($loop->iteration); ?></td>
                            <td class="p-4 font-medium"><?php echo e($planta->nombre); ?></td>
                            <td class="p-4"><?php echo e($planta->tipoPlanta->nombre); ?></td>
                            <td class="p-4">S/. <?php echo e(number_format($planta->precio, 2)); ?></td>
                            <td class="p-4">
                                <span class="px-3 py-1 rounded-full text-xs font-bold <?php echo e($planta->stock_actual <= $planta->stock_minimo ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-600'); ?>">
                                    <?php echo e($planta->stock_actual); ?> und.
                                </span>
                            </td>
                            <td class="p-4"><?php echo e(ucfirst($planta->nivel_cuidado)); ?></td>
                            <td class="flex gap-4 p-4 justify-center">
                                <a class="flex flex-1 items-center justify-center gap-2 bg-[#f5f9f0] py-2 text-[#77a856] font-bold rounded-[10px] hover:scale-110 transition duration-300"
                                    href="<?php echo e(route('plantas.edit', $planta->id)); ?>">
                                    <img class="w-4 h-auto" src="<?php echo e(asset('images/editar.png')); ?>" alt=""> Editar
                                </a>
                                <form action="<?php echo e(route('plantas.destroy', $planta->id)); ?>" method="POST" class="flex flex-1">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit"
                                        class="flex flex-1 items-center justify-center gap-2 bg-[#fdebeb] py-2 text-[#f06f73] font-bold rounded-[10px] hover:scale-110 transition duration-300">
                                        <img class="w-4 h-auto" src="<?php echo e(asset('images/eliminar.png')); ?>" alt=""> Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </section>
    </main>
</body>

</html>
<?php /**PATH C:\xampp\htdocs\Adaptia\resources\views/plantas/index.blade.php ENDPATH**/ ?>