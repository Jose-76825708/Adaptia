<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="<?php echo e(asset('images/logo.png')); ?>">
    <?php echo app('Illuminate\Foundation\Vite')('resources/css/app.css'); ?>
    <title>Asignar Sensores</title>
</head>

<body class="flex font-sans min-h-screen">

    <?php echo $__env->make('partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <main class="flex flex-5 flex-col p-25 bg-[#fbfbfb] min-h-screen gap-4 overflow-y-auto">
        <section>
            <h1 class="text-[#103928] font-bold text-[2.5em]">Asignar Sensores</h1>
            <p class="text-[#8b8d8f] text-[1em]">
                Asocia un sensor activo y disponible a cada unidad comprada por un cliente.
            </p>
        </section>

        <?php if(session('success')): ?>
            <div class="p-4 bg-[#d4edda] text-[#155724] border border-[#c3e6cb] rounded-[10px]" role="status">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        <?php if($errors->any()): ?>
            <div class="p-4 bg-[#f8d7da] text-[#721c24] border border-[#f5c6cb] rounded-[10px]" role="alert">
                <p class="font-bold">No se pudo asignar el sensor:</p>
                <ul class="list-disc pl-6">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <section class="flex p-8 bg-[#fefdfe] text-[#304e42] rounded-[10px] shadow-xl overflow-x-auto">
            <?php if($unidadesPendientes->isEmpty()): ?>
                <p class="w-full py-8 text-center text-[#8b8d8f]">
                    No hay unidades pendientes de asignación de sensor.
                </p>
            <?php else: ?>
                <table class="w-full min-w-[850px]">
                    <thead class="bg-[#f3f5f3] text-left">
                        <tr>
                            <th class="text-center rounded-tl-[20px] p-4">Unidad</th>
                            <th class="p-4">Planta</th>
                            <th class="p-4">Cliente</th>
                            <th class="p-4">Fecha de venta</th>
                            <th class="rounded-tr-[20px] p-4">Asignación</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $unidadesPendientes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unidad): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="border-t-2 border-[#c2c4c7]">
                                <td class="p-4 text-center font-bold">#<?php echo e($unidad->id); ?></td>
                                <td class="p-4"><?php echo e($unidad->venta->planta->nombre); ?></td>
                                <td class="p-4">
                                    <div class="font-semibold"><?php echo e($unidad->cliente->name); ?></div>
                                    <div class="text-sm text-[#8b8d8f]"><?php echo e($unidad->cliente->email); ?></div>
                                </td>
                                <td class="p-4"><?php echo e($unidad->venta->fecha->format('d/m/Y H:i')); ?></td>
                                <td class="p-4">
                                    <?php if($sensoresDisponibles->isEmpty()): ?>
                                        <span class="text-sm text-[#856404]">
                                            No hay sensores activos disponibles.
                                        </span>
                                    <?php else: ?>
                                        <form action="<?php echo e(route('plantas-vendidas.asignar-sensor', $unidad)); ?>"
                                              method="POST" class="flex min-w-[260px] flex-col gap-2">
                                            <?php echo csrf_field(); ?>
                                            <select name="sensor_id"
                                                    class="p-3 bg-[#f3f5f3] border border-[#ecedea] rounded-[10px] outline-none focus:border-[#629f22]"
                                                    aria-label="Sensor para la unidad <?php echo e($unidad->id); ?>" required>
                                                <option value="" disabled
                                                    <?php if(old('sensor_id') === null): echo 'selected'; endif; ?>>
                                                    Seleccione un sensor...
                                                </option>
                                                <?php $__currentLoopData = $sensoresDisponibles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sensor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($sensor->id); ?>"
                                                        <?php if(old('sensor_id') == $sensor->id): echo 'selected'; endif; ?>>
                                                        <?php echo e($sensor->identificador_fisico); ?>

                                                    </option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                            <button type="submit"
                                                    class="py-2 px-4 bg-[#629f22] text-white rounded-[10px] font-bold hover:bg-[#568f1d] transition duration-300">
                                                Asignar sensor
                                            </button>
                                        </form>
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
<?php /**PATH C:\xampp\htdocs\Adaptia\resources\views/plantas-vendidas/index.blade.php ENDPATH**/ ?>