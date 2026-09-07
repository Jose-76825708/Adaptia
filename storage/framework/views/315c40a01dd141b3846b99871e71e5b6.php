<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="<?php echo e(asset('images/logo.png')); ?>">
    <?php echo app('Illuminate\Foundation\Vite')('resources/css/app.css'); ?>
    <title>Tipo Planta</title>
</head>

<body class="flex font-sans h-screen">

    <?php echo $__env->make('partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    
    <main class="flex flex-5 flex-col p-25 bg-[#fbfbfb] h-full gap-4">
        <section class="flex items-center justify-between">
            <div class="flex-4">
                <h1 class="text-[#103928] font-bold text-[2.5em]">Gestión de Tipos de Planta</h1>
                <p class="text-[#8b8d8f] text-[1em]">Administra las categorias de plantas registradas en Adaptia.</p>
            </div>
            <div class="flex-1">
                <a href="<?php echo e(route('tipoPlantas.create')); ?>"
                    class="flex items-center justify-between py-3 px-6 bg-[#629f22] text-[#fbfbfb] rounded-[10px] gap-3 hover:scale-110 transition duration-300">
                    <img class="w-[10%] h-auto" src="<?php echo e(asset('images/anadir.png')); ?>" alt="Añadir">
                    Crear Tipo de Planta
                </a>
            </div>

        </section>
        <section class="flex p-8  bg-[#fefdfe] text-[#304e42] rounded-[10px] shadow-xl">
            <table class="w-full">
                <thead class="bg-[#f3f5f3] text-left">
                    <th class="text-center rounded-tl-[20px] w-20 p-4">Id</th>
                    <th class="p-4 w-190">Nombre</th>
                    <th class="rounded-tr-[20px] w-100 p-4">Acciones</th>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tipo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="border-t-2 border-[#c2c4c7]">
                            <td class="p-4 font-bold text-center"><?php echo e($tipo->id); ?></td>
                            <td class="p-4"><?php echo e($tipo->nombre); ?></td>
                            <td class="flex gap-6 p-4">
                                <a class="flex flex-1 items-center justify-center gap-3 bg-[#f5f9f0] py-2 text-[#77a856] font-bold rounded-[10px] hover:scale-110 transition duration-300"
                                    href="<?php echo e(route('tipoPlantas.edit', $tipo->id)); ?>"><img class="w-[10%] h-auto"
                                        src="<?php echo e(asset('images/editar.png')); ?>" alt=""> Editar</a>
                                <form action="<?php echo e(route('tipoPlantas.destroy', $tipo->id)); ?>" method="POST"
                                    class="flex flex-1">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit"
                                        class="flex flex-1 items-center justify-center gap-3 bg-[#fdebeb] py-2 text-[#f06f73] font-bold rounded-[10px] hover:scale-110 transition duration-300">
                                        <img class="w-[10%] h-auto" src="<?php echo e(asset('images/eliminar.png')); ?>"
                                            alt=""> Eliminar
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
<?php /**PATH C:\xampp\htdocs\Adaptia\resources\views/tipoPlantas/index.blade.php ENDPATH**/ ?>