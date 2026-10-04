<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="<?php echo e(asset('images/logo.png')); ?>">
    <?php echo app('Illuminate\Foundation\Vite')('resources/css/app.css'); ?>
    <title>Registrar Venta</title>
</head>

<body class="flex font-sans min-h-screen">

    <?php echo $__env->make('partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <main class="flex flex-5 flex-col px-25 py-10 bg-[#fbfbfb] min-h-screen gap-4 overflow-y-auto">
        <section>
            <a class="flex items-center text-[#0c251c] font-bold text-[0.8em]" href="<?php echo e(route('ventas.index')); ?>">
                <img class="w-[3%] h-auto" src="<?php echo e(asset('images/regreso-flecha.png')); ?>" alt="">
                Volver a Ventas
            </a>
        </section>

        <section class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-[#103928] font-bold text-[2.5em]">Registrar Venta</h1>
                <p class="text-[#8b8d8f] text-[1em]">Selecciona el cliente, la planta y la cantidad vendida.</p>
            </div>
        </section>

        <?php if($errors->any()): ?>
            <div class="p-4 bg-[#f8d7da] text-[#721c24] border border-[#f5c6cb] rounded-[10px]" role="alert">
                <p class="font-bold">No se pudo registrar la venta. Revisa los datos indicados.</p>
            </div>
        <?php endif; ?>

        <?php if($clientes->isEmpty()): ?>
            <div class="p-4 bg-[#fff3cd] text-[#856404] border border-[#ffeeba] rounded-[10px]" role="status">
                No hay clientes registrados para asociar a la venta.
            </div>
        <?php endif; ?>

        <?php if($plantas->isEmpty()): ?>
            <div class="p-4 bg-[#fff3cd] text-[#856404] border border-[#ffeeba] rounded-[10px]" role="status">
                No hay plantas con stock disponible para vender.
            </div>
        <?php endif; ?>

        <section class="flex justify-center pb-10">
            <div class="flex flex-col p-10 bg-[#fefdfe] rounded-[20px] shadow-xl w-full gap-8 border border-[#ecedea]">
                <form action="<?php echo e(route('ventas.store')); ?>" method="POST" class="flex flex-col gap-10">
                    <?php echo csrf_field(); ?>

                    <div class="flex flex-col gap-6">
                        <div class="flex items-center gap-3 border-b border-[#ecedea] pb-2">
                            <div class="w-2 h-6 bg-[#629f22] rounded-full"></div>
                            <h2 class="text-[#103928] font-bold text-[1.3em]">Datos de la Venta</h2>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="flex flex-col gap-2">
                                <label for="user_id" class="text-[#304e42] font-semibold text-[1.1em]">Cliente</label>
                                <select name="user_id" id="user_id"
                                        class="p-4 bg-[#f3f5f3] border-2 <?php $__errorArgs = ['user_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php else: ?> border-[#ecedea] <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> rounded-[10px] outline-none focus:border-[#629f22] transition duration-300"
                                        <?php if($clientes->isEmpty()): echo 'disabled'; endif; ?> required>
                                    <option value="" disabled <?php if(old('user_id') === null): echo 'selected'; endif; ?>>Seleccione un cliente...</option>
                                    <?php $__currentLoopData = $clientes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cliente): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($cliente->id); ?>" <?php if(old('user_id') == $cliente->id): echo 'selected'; endif; ?>>
                                            <?php echo e($cliente->name); ?> (<?php echo e($cliente->email); ?>)
                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['user_id'];
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
                                <label for="planta_id" class="text-[#304e42] font-semibold text-[1.1em]">Planta</label>
                                <select name="planta_id" id="planta_id"
                                        class="p-4 bg-[#f3f5f3] border-2 <?php $__errorArgs = ['planta_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php else: ?> border-[#ecedea] <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> rounded-[10px] outline-none focus:border-[#629f22] transition duration-300"
                                        <?php if($plantas->isEmpty()): echo 'disabled'; endif; ?> required>
                                    <option value="" disabled <?php if(old('planta_id') === null): echo 'selected'; endif; ?>>Seleccione una planta...</option>
                                    <?php $__currentLoopData = $plantas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $planta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($planta->id); ?>" <?php if(old('planta_id') == $planta->id): echo 'selected'; endif; ?>>
                                            <?php echo e($planta->nombre); ?> (Stock disponible: <?php echo e($planta->stock_actual); ?>)
                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['planta_id'];
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
                                <label for="cantidad" class="text-[#304e42] font-semibold text-[1.1em]">Cantidad</label>
                                <input type="number" name="cantidad" id="cantidad" min="1"
                                       class="p-4 bg-[#f3f5f3] border-2 <?php $__errorArgs = ['cantidad'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php else: ?> border-[#ecedea] <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> rounded-[10px] outline-none focus:border-[#629f22] transition duration-300"
                                       placeholder="Ej: 2" value="<?php echo e(old('cantidad')); ?>" required>
                                <?php $__errorArgs = ['cantidad'];
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
                        </div>
                    </div>

                    <div class="flex justify-end mt-4">
                        <button type="submit"
                                class="flex items-center justify-between py-4 px-10 bg-[#629f22] text-[#fbfbfb] rounded-[10px] gap-3 hover:scale-105 transition duration-300 font-bold text-[1.1em]"
                                <?php if($clientes->isEmpty() || $plantas->isEmpty()): echo 'disabled'; endif; ?>>
                            <img class="w-[1.2em] h-auto" src="<?php echo e(asset('images/anadir.png')); ?>" alt="">
                            Registrar Venta
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </main>
</body>

</html>
<?php /**PATH C:\xampp\htdocs\Adaptia\resources\views/ventas/create.blade.php ENDPATH**/ ?>