<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="<?php echo e(asset('images/logo.png')); ?>">
    <?php echo app('Illuminate\Foundation\Vite')('resources/css/app.css'); ?>
    <title>Crear cuenta de personal</title>
</head>

<body class="flex font-sans min-h-screen">

    <?php echo $__env->make('partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <main class="flex flex-5 flex-col px-25 py-10 bg-[#fbfbfb] min-h-screen gap-4">
        <section class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-[#103928] font-bold text-[2.5em]">Crear cuenta de personal</h1>
                <p class="text-[#8b8d8f] text-[1em]">
                    Registra una cuenta de vendedor o administrador. Esta opción solo está disponible para administradores.
                </p>
            </div>
        </section>

        <?php if(session('success')): ?>
            <div class="p-4 bg-[#d4edda] text-[#155724] border border-[#c3e6cb] rounded-[10px]" role="status">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        <?php if($errors->any()): ?>
            <div class="p-4 bg-[#f8d7da] text-[#721c24] border border-[#f5c6cb] rounded-[10px]" role="alert">
                <p class="font-bold">No se pudo crear la cuenta:</p>
                <ul class="list-disc pl-6">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <section class="flex justify-center">
            <div class="flex flex-col p-10 bg-[#fefdfe] rounded-[20px] shadow-xl w-full max-w-2xl gap-8 border border-[#ecedea]">
                <form action="<?php echo e(route('personal.store')); ?>" method="POST" class="flex flex-col gap-6">
                    <?php echo csrf_field(); ?>

                    <div class="flex flex-col gap-2">
                        <label for="name" class="text-[#304e42] font-semibold text-[1.1em]">Nombre completo</label>
                        <input type="text" name="name" id="name"
                               class="p-4 bg-[#f3f5f3] border-2 <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php else: ?> border-[#ecedea] <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> rounded-[10px] outline-none focus:border-[#629f22] transition duration-300"
                               value="<?php echo e(old('name')); ?>" required>
                        <?php $__errorArgs = ['name'];
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
                        <label for="email" class="text-[#304e42] font-semibold text-[1.1em]">Correo electrónico</label>
                        <input type="email" name="email" id="email"
                               class="p-4 bg-[#f3f5f3] border-2 <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php else: ?> border-[#ecedea] <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> rounded-[10px] outline-none focus:border-[#629f22] transition duration-300"
                               value="<?php echo e(old('email')); ?>" required>
                        <?php $__errorArgs = ['email'];
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

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="flex flex-col gap-2">
                            <label for="password" class="text-[#304e42] font-semibold text-[1.1em]">Contraseña</label>
                            <input type="password" name="password" id="password"
                                   class="p-4 bg-[#f3f5f3] border-2 <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php else: ?> border-[#ecedea] <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> rounded-[10px] outline-none focus:border-[#629f22] transition duration-300"
                                   required>
                        </div>
                        <div class="flex flex-col gap-2">
                            <label for="password_confirmation" class="text-[#304e42] font-semibold text-[1.1em]">Confirmar contraseña</label>
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                   class="p-4 bg-[#f3f5f3] border-2 border-[#ecedea] rounded-[10px] outline-none focus:border-[#629f22] transition duration-300"
                                   required>
                        </div>
                    </div>
                    <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <span class="text-red-500 text-[0.9em] font-medium"><?php echo e($message); ?></span>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                    <div class="flex flex-col gap-2">
                        <label for="rol" class="text-[#304e42] font-semibold text-[1.1em]">Rol de la cuenta</label>
                        <select name="rol" id="rol"
                                class="p-4 bg-[#f3f5f3] border-2 <?php $__errorArgs = ['rol'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php else: ?> border-[#ecedea] <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> rounded-[10px] outline-none focus:border-[#629f22] transition duration-300"
                                required>
                            <option value="" disabled <?php if(old('rol') === null): echo 'selected'; endif; ?>>Seleccione un rol...</option>
                            <option value="vendedor" <?php if(old('rol') === 'vendedor'): echo 'selected'; endif; ?>>Vendedor</option>
                            <option value="administrador" <?php if(old('rol') === 'administrador'): echo 'selected'; endif; ?>>Administrador</option>
                        </select>
                        <?php $__errorArgs = ['rol'];
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
                                class="py-3 px-8 bg-[#629f22] text-white rounded-[10px] font-bold hover:bg-[#568f1d] transition duration-300">
                            Crear cuenta
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </main>
</body>

</html>
<?php /**PATH C:\xampp\htdocs\Adaptia\resources\views/personal/create.blade.php ENDPATH**/ ?>