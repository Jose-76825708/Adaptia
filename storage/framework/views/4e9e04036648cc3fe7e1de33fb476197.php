<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="<?php echo e(asset('images/logo.png')); ?>">
    <?php echo app('Illuminate\Foundation\Vite')('resources/css/app.css'); ?>
    <title>Crear Tipo de Planta</title>
</head>

<body class="flex font-sans h-screen">

    <?php echo $__env->make('partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <main class="flex flex-5 flex-col px-25 py-10 bg-[#fbfbfb] h-full gap-4">

        <section>
            <a class="flex items-center text-[#0c251c] font-bold text-[0.8em]" href="<?php echo e(route('tipoPlantas.index')); ?>"><img class="w-[3%] h-auto"
                    src="<?php echo e(asset('images/regreso-flecha.png')); ?>" alt="">Volver a Tipos de Plantas</a>

        </section>

    </main>

</body>

</html>
<?php /**PATH C:\xampp\htdocs\Adaptia\resources\views/tipoPlantas/create.blade.php ENDPATH**/ ?>