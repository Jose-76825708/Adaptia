<aside class="flex flex-1 flex-col items-left gap-15 p-2 bg-[#013623] h-full">
    <div class="flex items-center justify-center pt-8">

        <img src="<?php echo e(asset('images/logotipo_blanco.png')); ?>" class="w-[80%] h-auto" alt="logotipo de adaptia">

    </div>

    <div>
        <ul class="flex flex-col gap-8 p-3 text-[white]">
            <li
                class="flex items-center justify-left p-5 hover:scale-110 rounded-[20px]  hover:bg-[#6eab26] transition duration-300 cursor-pointer">
                <a class="flex w-full h-full items-center gap-3" href="<?php echo e(route('tipoPlantas.index')); ?>"><img class="w-5 h-auto" src="<?php echo e(asset('images/tipo-planta.png')); ?>" alt="">Tipos de plantas</a>
            </li>
            <li
                class="flex items-center justify-left p-5 hover:scale-110 rounded-[20px]  hover:bg-[#6eab26] transition duration-300 cursor-pointer">
                <a class="flex w-full h-full items-center gap-2" href="<?php echo e(route('plantas.index')); ?>"><img class="w-7 h-auto" src="<?php echo e(asset('images/plantas.png')); ?>" alt="">Plantas</a>
            </li>
            <li
                class="flex items-center justify-left p-5 hover:scale-110 rounded-[20px]  hover:bg-[#6eab26] transition duration-300 cursor-pointer">
                <a class="flex w-full h-full items-center gap-3" href="<?php echo e(route('movimientos-inventario.index')); ?>">
                    <img class="w-5 h-auto" src="<?php echo e(asset('images/inventario.png')); ?>" alt="">
                    Movimientos de Inventario
                </a>
            </li>
            <li
                class="flex items-center justify-left p-5 hover:scale-110 rounded-[20px]  hover:bg-[#6eab26] transition duration-300 cursor-pointer">
                <a class="flex w-full h-full items-center gap-3" href="<?php echo e(route('sensores.index')); ?>">
                    <img class="w-5 h-auto" src="<?php echo e(asset('images/sensores.png')); ?>" alt="">
                    Sensores
                </a>
            </li>
        </ul>
    </div>

    <div class="mt-auto pt-4 border-t border-[#014d33]">
        <!-- User Info -->
        <div class="flex items-center space-x-3 p-4">
            <div class="shrink-0">
                <img class="w-8 h-8 rounded-full" src="<?php echo e(asset('images/placeholder-user.png')); ?>" alt="Usuario">
            </div>
            <div class="flex-1">
                <div class="font-medium text-white"><?php echo e(auth()->user()->name ?? auth()->user()->email); ?></div>
                <div class="text-sm text-white/60"><?php echo e(auth()->user()->email); ?></div>
            </div>
        </div>

        <!-- Logout Card -->
        <div class="mt-4">
            <form id="logout-form" action="<?php echo e(route('logout')); ?>" method="POST" class="hidden">
                <?php echo csrf_field(); ?>
            </form>
            <div class="flex items-center justify-between p-4 bg-[#014d33] rounded-lg hover:bg-[#015d3c] transition duration-300 cursor-pointer"
                 onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <span class="text-white">Cerrar sesión</span>
                <img class="w-5 h-auto" src="<?php echo e(asset('images/placeholder-logout.png')); ?>" alt="Logout">
            </div>
        </div>
    </div>
</aside><?php /**PATH C:\xampp\htdocs\Adaptia\resources\views/partials/sidebar.blade.php ENDPATH**/ ?>