<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="<?php echo e(asset('images/logo.png')); ?>">
    <?php echo app('Illuminate\Foundation\Vite')('resources/css/app.css'); ?>
    <title>Ventas</title>
</head>

<body class="flex font-sans min-h-screen">

    <?php echo $__env->make('partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <main class="flex flex-5 flex-col p-25 bg-[#fbfbfb] min-h-screen gap-4 overflow-y-auto">
        <section class="flex items-center justify-between gap-6">
            <div>
                <h1 class="text-[#103928] font-bold text-[2.5em]">Ventas</h1>
                <p class="text-[#8b8d8f] text-[1em]">Consulta y registra las ventas realizadas.</p>
            </div>
            <a href="<?php echo e(route('ventas.create')); ?>"
               class="flex shrink-0 items-center justify-between py-3 px-6 bg-[#629f22] text-[#fbfbfb] rounded-[10px] gap-3 hover:scale-105 transition duration-300">
                <img class="w-5 h-auto" src="<?php echo e(asset('images/anadir.png')); ?>" alt="">
                Registrar Venta
            </a>
        </section>

        <?php if(session('success')): ?>
            <div class="p-4 bg-[#d4edda] text-[#155724] border border-[#c3e6cb] rounded-[10px]" role="status">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        <?php if(session('stock_warning')): ?>
            <div class="p-4 bg-[#fff3cd] text-[#856404] border border-[#ffeeba] rounded-[10px]" role="alert">
                <?php echo e(session('stock_warning')); ?>

            </div>
        <?php endif; ?>

        <section class="flex p-8 bg-[#fefdfe] text-[#304e42] rounded-[10px] shadow-xl overflow-x-auto">
            <?php if($ventas->isEmpty()): ?>
                <p class="w-full py-8 text-center text-[#8b8d8f]">
                    Todavía no hay ventas registradas.
                </p>
            <?php else: ?>
                <table class="w-full min-w-[900px]">
                    <thead class="bg-[#f3f5f3] text-left">
                        <tr>
                            <th class="text-center rounded-tl-[20px] p-4">Fecha</th>
                            <th class="p-4">Cliente</th>
                            <th class="p-4">Vendedor</th>
                            <th class="p-4">Planta</th>
                            <th class="text-center p-4">Cantidad</th>
                            <th class="rounded-tr-[20px] p-4">Sensores</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $ventas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $venta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="border-t-2 border-[#c2c4c7]">
                                <td class="p-4 text-center"><?php echo e($venta->fecha->format('d/m/Y H:i')); ?></td>
                                <td class="p-4"><?php echo e($venta->cliente->name); ?></td>
                                <td class="p-4"><?php echo e($venta->vendedor->name); ?></td>
                                <td class="p-4"><?php echo e($venta->planta->nombre); ?></td>
                                <td class="p-4 text-center font-bold"><?php echo e($venta->cantidad); ?></td>
                                <td class="p-4">
                                    <?php $__empty_1 = true; $__currentLoopData = $venta->plantasVendidas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unidad): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <div class="mb-1 last:mb-0">
                                            <span class="font-semibold">Unidad #<?php echo e($unidad->id); ?>:</span>
                                            <?php if($unidad->sensor): ?>
                                                <span><?php echo e($unidad->sensor->identificador_fisico); ?></span>
                                            <?php else: ?>
                                                <span class="text-[#856404]">Pendiente de asignación</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <span class="text-[#8b8d8f]">Sin unidades individuales registradas</span>
                                    <?php endif; ?>

                                    <?php
                                        $unidadesFaltantes = max(0, $venta->cantidad - $venta->plantasVendidas->count());
                                    ?>
                                    <?php if($unidadesFaltantes > 0): ?>
                                        <div class="mt-2 text-sm text-[#856404]">
                                            <?php echo e($unidadesFaltantes); ?>

                                            <?php echo e($unidadesFaltantes === 1 ? 'unidad sin registro individual' : 'unidades sin registro individual'); ?>

                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    </main>
</body>

</html>
<?php /**PATH C:\xampp\htdocs\Adaptia\resources\views/ventas/index.blade.php ENDPATH**/ ?>