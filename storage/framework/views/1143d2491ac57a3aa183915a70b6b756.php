<section id="mis-sensores" class="reveal"
         data-monitoring-poll-url="<?php echo e(route('cliente.monitoreo.inicio')); ?>"
         data-monitoring-poll-interval="15000">
    <?php if($sensoresAsignados->isNotEmpty()): ?>
        <div class="mb-6">
            <p class="mb-2 text-xs font-bold uppercase tracking-[0.18em] text-[#7a965f]">Dispositivos</p>
            <h2 class="text-2xl font-bold tracking-tight text-[#0f3c2b] sm:text-3xl">Tus sensores asignados</h2>
            <p class="mt-2 text-base text-[#718071]">Consulta la lectura más reciente y las alertas activas de cada planta.</p>
        </div>

        <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
            <?php $__currentLoopData = $sensoresAsignados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plantaVendida): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="rounded-3xl border border-[#e5ebdf] bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-lg sm:p-7">
                    <?php
                        $ultimaLectura = $plantaVendida->lecturasSensores->first();
                        $alertasActivas = $plantaVendida->alertas->whereNull('resuelta_en');
                    ?>
                    <div class="mb-5 flex items-center justify-between gap-4">
                        <div class="flex min-w-0 items-center gap-3">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#eff6e8]">
                                <img class="h-6 w-6" src="<?php echo e(asset('images/sensor.png')); ?>" alt="">
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-bold uppercase tracking-wider text-[#82907e]">Sensor asignado</p>
                                <h3 class="truncate font-bold text-[#0f3c2b]">
                                    #<?php echo e($plantaVendida->sensor->identificador_fisico); ?>

                                </h3>
                            </div>
                        </div>
                        <span class="shrink-0 rounded-full px-3 py-1.5 text-xs font-bold <?php echo e($plantaVendida->sensor->estado !== 'activo' ? 'bg-[#f8d7da] text-[#721c24]' : ($alertasActivas->isNotEmpty() ? 'bg-[#fff1dc] text-[#8a5700]' : 'bg-[#eff6e8] text-[#52752d]')); ?>">
                            <?php echo e($plantaVendida->sensor->estado !== 'activo' ? 'Sensor inactivo' : ($alertasActivas->isNotEmpty() ? 'Requiere atención' : ($ultimaLectura ? 'Sin alertas activas' : 'Esperando lectura'))); ?>

                        </span>
                    </div>

                    <div class="mb-5 flex flex-col gap-2 rounded-2xl bg-[#f7f9f5] px-4 py-3 text-sm sm:flex-row sm:items-center sm:justify-between">
                        <span class="font-medium text-[#718071]">Planta asociada</span>
                        <span class="font-semibold text-[#34533b]"><?php echo e($plantaVendida->venta->planta->nombre ?? 'Planta no disponible'); ?></span>
                    </div>
                    <?php if($ultimaLectura): ?>
                        <div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
                            <?php $__currentLoopData = [
                                ['label' => 'Humedad del suelo', 'value' => $ultimaLectura->humedad_suelo, 'unit' => '%'],
                                ['label' => 'Temperatura', 'value' => $ultimaLectura->temperatura, 'unit' => '°C'],
                                ['label' => 'Humedad ambiental', 'value' => $ultimaLectura->humedad_ambiental, 'unit' => '%'],
                                ['label' => 'Luz', 'value' => $ultimaLectura->luz, 'unit' => 'lux'],
                            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $medicion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="rounded-2xl bg-[#f7f9f5] p-3">
                                    <p class="text-xs font-medium text-[#718071]"><?php echo e($medicion['label']); ?></p>
                                    <p class="mt-1 text-lg font-bold text-[#0f3c2b]"><?php echo e(rtrim(rtrim(number_format((float) $medicion['value'], 2), '0'), '.')); ?> <span class="text-sm font-semibold"><?php echo e($medicion['unit']); ?></span></p>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <p class="text-xs text-[#82907e]">
                            Última lectura: <?php echo e($ultimaLectura->fecha_hora->timezone(config('app.timezone'))->format('d/m/Y H:i')); ?>

                        </p>
                    <?php else: ?>
                        <div class="rounded-2xl border border-dashed border-[#dce9d0] bg-[#f7f9f5] p-4 text-sm text-[#718071]">
                            Aún no hay lecturas disponibles para este sensor.
                        </div>
                    <?php endif; ?>

                    <?php if($alertasActivas->isNotEmpty()): ?>
                        <div class="mt-5 space-y-3">
                            <h4 class="font-bold text-[#8a5700]">Alertas activas</h4>
                            <?php $__currentLoopData = $alertasActivas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alerta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <article class="rounded-2xl border border-[#f1d7a9] bg-[#fff9ed] p-4">
                                    <p class="text-sm font-semibold text-[#684b1b]">
                                        <?php echo e($alerta->mensaje ?? 'Esta planta requiere atención (' . str_replace('_', ' ', $alerta->tipo) . ').'); ?>

                                    </p>
                                    <p class="mt-1 text-xs text-[#8c7650]">
                                        <?php echo e($alerta->created_at->timezone(config('app.timezone'))->format('d/m/Y H:i')); ?>

                                    </p>
                                </article>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php else: ?>
        <div class="flex flex-col items-center gap-5 rounded-3xl border border-[#e4ebdb] bg-white p-7 text-center shadow-sm sm:p-10">
            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-[#eff6e8]">
                <img class="h-9 w-9" src="<?php echo e(asset('images/plantas.png')); ?>" alt="">
            </div>
            <div>
                <h3 class="text-xl font-bold text-[#0f3c2b]">Aún no tienes sensores asignados</h3>
                <p class="mx-auto mt-2 max-w-xl text-sm leading-6 text-[#718071]">
                    Cuando se asigne un sensor a una de tus plantas, aparecerá aquí junto con sus lecturas disponibles.
                </p>
            </div>
            <a href="<?php echo e(route('catalogo.plantas.index')); ?>"
               class="inline-flex min-h-12 items-center justify-center rounded-xl bg-[#7cb22b] px-6 py-3 font-bold text-white shadow-md shadow-[#7cb22b]/20 transition hover:-translate-y-0.5 hover:bg-[#6eab26]">
                Explorar plantas disponibles
            </a>
        </div>
    <?php endif; ?>
</section>
<?php /**PATH C:\xampp\htdocs\Adaptia\resources\views/home/partials/sensores-cliente.blade.php ENDPATH**/ ?>