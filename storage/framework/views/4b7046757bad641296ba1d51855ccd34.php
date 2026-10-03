<?php $__env->startSection('title', 'Historial y Alertas - Adaptia'); ?>

<?php $__env->startSection('content'); ?>
<main class="w-full space-y-16 bg-[#f8faf6] px-4 py-8 sm:space-y-20 sm:px-8 sm:py-10 lg:px-12">
    <section class="reveal overflow-hidden rounded-[2rem] bg-gradient-to-br from-[#0f3c2b] via-[#18543a] to-[#2d7141] shadow-xl shadow-[#0f3c2b]/10">
        <div class="relative flex flex-col items-center px-6 py-10 text-center sm:px-10 sm:py-14">
            <div class="absolute -right-12 -top-16 h-56 w-56 rounded-full border-[32px] border-white/5"></div>
            <div class="absolute -bottom-24 -left-12 h-64 w-64 rounded-full border-[36px] border-white/5"></div>
            <div class="relative mb-5 flex h-20 w-20 items-center justify-center rounded-3xl bg-white/15 shadow-inner ring-1 ring-white/20 backdrop-blur sm:h-24 sm:w-24">
                <img class="h-11 w-11 sm:h-12 sm:w-12" src="<?php echo e(asset('images/logo.png')); ?>" alt="">
            </div>
            <p class="relative mb-2 text-sm font-semibold uppercase tracking-[0.2em] text-[#d4e8ba]">Cuidado conectado</p>
            <h1 class="relative text-3xl font-bold tracking-tight text-white sm:text-4xl lg:text-5xl">
                Historial y alertas
            </h1>
            <p class="relative mt-3 max-w-2xl text-base leading-7 text-white/80 sm:text-lg">
                Sigue las condiciones de tus plantas y consulta sus cambios a lo largo del tiempo.
            </p>
            <span class="relative mt-6 inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-2 text-sm font-semibold text-white backdrop-blur">
                <span class="h-2 w-2 rounded-full bg-[#c3df8c]"></span>
                Monitoreo próximamente
            </span>
        </div>
    </section>

    <section class="reveal">
        <div class="mb-7">
            <p class="mb-2 text-xs font-bold uppercase tracking-[0.18em] text-[#7a965f]">En preparación</p>
            <h2 class="text-2xl font-bold tracking-tight text-[#0f3c2b] sm:text-3xl">Todo el cuidado de tus plantas, en un solo lugar</h2>
            <p class="mt-2 max-w-3xl text-base leading-7 text-[#718071]">
                Esta sección se habilitará con la implementación del monitoreo IoT. Por ahora todavía no hay lecturas ni alertas disponibles.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <article class="rounded-2xl border border-[#e5ebdf] bg-white p-5 shadow-sm sm:p-6">
                <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-[#eff6e8]">
                    <img class="h-6 w-6" src="<?php echo e(asset('images/termometro.png')); ?>" alt="">
                </div>
                <h3 class="text-lg font-bold text-[#0f3c2b]">Lecturas de sensores</h3>
                <p class="mt-2 text-sm leading-6 text-[#718071]">
                    Consulta datos de humedad, temperatura y otras condiciones del entorno de tus plantas.
                </p>
            </article>

            <article class="rounded-2xl border border-[#e5ebdf] bg-white p-5 shadow-sm sm:p-6">
                <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-[#eff6e8]">
                    <img class="h-6 w-6" src="<?php echo e(asset('images/historial.png')); ?>" alt="">
                </div>
                <h3 class="text-lg font-bold text-[#0f3c2b]">Tendencias históricas</h3>
                <p class="mt-2 text-sm leading-6 text-[#718071]">
                    Revisa gráficos para observar cómo cambian las condiciones con el paso del tiempo.
                </p>
            </article>

            <article class="rounded-2xl border border-[#e5ebdf] bg-white p-5 shadow-sm sm:p-6">
                <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-[#eff6e8]">
                    <img class="h-6 w-6" src="<?php echo e(asset('images/alerta.png')); ?>" alt="">
                </div>
                <h3 class="text-lg font-bold text-[#0f3c2b]">Alertas importantes</h3>
                <p class="mt-2 text-sm leading-6 text-[#718071]">
                    Aquí aparecerán avisos cuando las mediciones requieran tu atención.
                </p>
            </article>
        </div>
    </section>

    <section class="reveal rounded-3xl border border-[#dce9d0] bg-[#eff6e8] p-6 sm:p-8">
        <div class="flex flex-col items-start gap-5 sm:flex-row sm:items-center">
            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white shadow-sm">
                <img class="h-8 w-8" src="<?php echo e(asset('images/notificacion.png')); ?>" alt="">
            </div>
            <div class="flex-1">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#7a965f]">Próximamente</p>
                <h2 class="mt-1 text-xl font-bold text-[#0f3c2b] sm:text-2xl">Tus datos aparecerán aquí cuando el monitoreo esté disponible</h2>
                <p class="mt-2 text-sm leading-6 text-[#63745f]">
                    La vista se actualizará cuando se complete la integración con los sensores y el sistema de alertas.
                </p>
            </div>
            <a href="<?php echo e(route('home')); ?>"
               class="inline-flex min-h-11 shrink-0 items-center justify-center rounded-xl bg-[#7cb22b] px-5 py-2.5 font-bold text-white transition hover:-translate-y-0.5 hover:bg-[#6eab26]">
                Volver al inicio
            </a>
        </div>
    </section>
</main>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    // Script placeholder para futura funcionalidad de historial/alertas
    document.addEventListener('DOMContentLoaded', function() {
        // Aquí irá el JavaScript para:
        // - Mostrar gráficos históricos de sensores
        // - Manejar marcas de alertas como leídas/no leídas
        // - Filtrar por rango de fechas y tipo de alerta
        // - Actualizar datos en tiempo real (cuando esté disponible)
        console.log('Historial y Alertas - Funcionalidad pendiente de implementación (Fase 4)');
    });
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Adaptia\resources\views/home/historial-alertas.blade.php ENDPATH**/ ?>