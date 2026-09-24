<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="<?php echo e(asset('images/logo.png')); ?>">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <title><?php echo $__env->yieldContent('title', 'Adaptia'); ?></title>
</head>

<body class="font-sans">
    <header class="flex items-center content-center p-4 px-8">

        <div class="flex-1">

            <img class="w-50 h-auto" src="<?php echo e(asset('images/logotipo.png')); ?>" alt="logotipo de adaptia">

        </div>
        <div class="flex-1">

            <ul class="flex justify-between gap-4 text-[#3e5a51] font-bold">
                <li><a href="#funcionamiento">Cómo funciona</a></li>
                <li><a href="#catalogo">Catálogo</a></li>
                <li><a href="#nosotros">Nosotros</a></li>
            </ul>

        </div>
        <div class="flex-1 flex justify-end">

            <a class="bg-[#7cb22b] text-white px-7 py-3 rounded-[20px] text-[19px] font-bold hover:scale-110 transition duration-300"
                href="<?php echo e(Auth::check() ? route('perfil.edit') : route('login')); ?>">Empezar</a>

        </div>

    </header>

    <?php echo $__env->yieldContent('content'); ?>

    <footer class="flex flex-col items-center px-50 justify-center text-white bg-[#053a28] gap-10 w-full reveal">

        <div class="flex gap-20 w-full pt-10">

            <div class="flex-1 flex flex-col items-start justify-center text-left gap-6 text-[1.1em]">

                <img class="w-[60%] h-auto" src="<?php echo e(asset('images/logotipo_blanco.png')); ?>" alt="logotipo de adaptia con color blanco">

                <p>Tecnología y naturaleza para <br> ayudarte a vivir en armonía <br> con las plantas</p>

            </div>
            <div class="flex-2 flex justify-between text-[1.1em]">

                <div class="flex flex-col pt-11 gap-6">
                    <h3 class="font-bold">Navegación</h3>
                    <ul class="flex flex-col gap-3">
                        <li><a href="#funcionamiento">Cómo funciona</a></li>
                        <li><a href="#catalogo">Catálogo</a></li>
                        <li><a href="#nosotros">Nosotros</a></li>
                    </ul>
                </div>

                <div class="flex flex-col pt-11 gap-6">
                    <h3 class="font-bold">Recursos</h3>
                    <ul class="flex flex-col gap-3">
                        <li>Guía de cuidados</li>
                        <li>Preguntas frecuentes</li>
                        <li>Blog</li>
                    </ul>
                </div>

                <div class="flex flex-col pt-11 gap-6">
                    <h3 class="font-bold">Legal</h3>
                    <ul class="flex flex-col gap-3">
                        <li>Términos y condiciones</li>
                        <li>Política de privacidad</li>
                        <li>Cookies</li>
                    </ul>
                </div>

            </div>

        </div>



        <p>© 2026 Adaptia. Todos los derechos reservados</p>
    </footer>
</body>

</html><?php /**PATH C:\xampp\htdocs\Adaptia\resources\views/layouts/app.blade.php ENDPATH**/ ?>