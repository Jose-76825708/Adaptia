<div id="historial-monitoreo"
     data-monitoring-poll-url="<?php echo e(route('cliente.monitoreo.historial')); ?>"
     data-monitoring-poll-interval="30000">
    <?php if($sensoresAsignados->isEmpty()): ?>
        <section class="rounded-3xl border border-[#e4ebdb] bg-white p-8 text-center shadow-sm">
            <h2 class="text-xl font-bold text-[#0f3c2b]">Aún no tienes sensores asignados</h2>
            <p class="mt-2 text-sm text-[#718071]">Cuando se asigne un sensor a una de tus plantas, aquí aparecerán sus lecturas y alertas.</p>
        </section>
    <?php else: ?>
        <?php $__currentLoopData = $sensoresAsignados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plantaVendida): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $planta = $plantaVendida->venta->planta;
                $lecturas = $plantaVendida->lecturasSensores->sortBy('fecha_hora')->values();
                $alertas = $plantaVendida->alertas;
            ?>
            <section class="space-y-6">
                <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#7a965f]">#<?php echo e($plantaVendida->sensor->identificador_fisico); ?></p>
                        <h2 class="mt-1 text-2xl font-bold tracking-tight text-[#0f3c2b]"><?php echo e($planta->nombre ?? 'Planta no disponible'); ?></h2>
                    </div>
                    <a href="<?php echo e(route('home')); ?>#mis-sensores" class="font-semibold text-[#608d2e] hover:underline">Ver sensor en inicio</a>
                </div>

                <?php if($lecturas->isEmpty()): ?>
                    <div class="rounded-2xl border border-dashed border-[#dce9d0] bg-white p-5 text-sm text-[#718071]">
                        Aún no hay lecturas disponibles para este sensor.
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
                        <?php $__currentLoopData = [
                            ['key' => 'humedad_suelo', 'label' => 'Humedad del suelo', 'unit' => '%', 'min' => 'humedad_suelo_min', 'max' => 'humedad_suelo_max'],
                            ['key' => 'temperatura', 'label' => 'Temperatura', 'unit' => '°C', 'min' => 'temperatura_min', 'max' => 'temperatura_max'],
                            ['key' => 'humedad_ambiental', 'label' => 'Humedad ambiental', 'unit' => '%', 'min' => 'humedad_ambiental_min', 'max' => 'humedad_ambiental_max'],
                            ['key' => 'luz', 'label' => 'Iluminación', 'unit' => 'lux', 'min' => 'luz_min', 'max' => 'luz_max'],
                        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grafico): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $valores = $lecturas->map(fn ($lectura) => (float) $lectura->{$grafico['key']});
                                $minimoValor = $valores->min();
                                $maximoValor = $valores->max();
                                $amplitud = max($maximoValor - $minimoValor, 1);
                                $puntos = $lecturas->map(function ($lectura, $indice) use ($lecturas, $grafico, $minimoValor, $amplitud) {
                                    $x = $lecturas->count() === 1 ? 320 : 30 + ($indice * 580 / ($lecturas->count() - 1));
                                    $y = 170 - (((float) $lectura->{$grafico['key']} - $minimoValor) / $amplitud * 130);

                                    return ['x' => round($x, 2), 'y' => round($y, 2), 'valor' => (float) $lectura->{$grafico['key']}, 'fecha' => $lectura->fecha_hora];
                                });
                                $polilinea = $puntos->map(fn ($punto) => $punto['x'] . ',' . $punto['y'])->implode(' ');
                                $rangoMinimo = $planta?->{$grafico['min']};
                                $rangoMaximo = $planta?->{$grafico['max']};
                            ?>
                            <article class="rounded-3xl border border-[#e5ebdf] bg-white p-5 shadow-sm sm:p-6">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <h3 class="font-bold text-[#0f3c2b]"><?php echo e($grafico['label']); ?></h3>
                                        <p class="mt-1 text-xs text-[#718071]">
                                            <?php if($rangoMinimo !== null && $rangoMaximo !== null): ?>
                                                Rango de referencia: <?php echo e(rtrim(rtrim(number_format((float) $rangoMinimo, 2), '0'), '.')); ?>–<?php echo e(rtrim(rtrim(number_format((float) $rangoMaximo, 2), '0'), '.')); ?> <?php echo e($grafico['unit']); ?>

                                            <?php else: ?>
                                                Rango de referencia no configurado
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                    <span class="rounded-full bg-[#eff6e8] px-3 py-1 text-xs font-bold text-[#52752d]"><?php echo e($lecturas->count()); ?> lecturas</span>
                                </div>
                                <svg class="mt-5 h-48 w-full" viewBox="0 0 640 200" role="img" aria-label="Tendencia histórica de <?php echo e(strtolower($grafico['label'])); ?>">
                                    <line x1="30" y1="40" x2="610" y2="40" stroke="#e8eee3" stroke-width="1" />
                                    <line x1="30" y1="105" x2="610" y2="105" stroke="#e8eee3" stroke-width="1" />
                                    <line x1="30" y1="170" x2="610" y2="170" stroke="#e8eee3" stroke-width="1" />
                                    <?php if($puntos->count() > 1): ?>
                                        <polyline points="<?php echo e($polilinea); ?>" fill="none" stroke="#629f22" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" />
                                    <?php endif; ?>
                                    <?php $__currentLoopData = $puntos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $punto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <circle cx="<?php echo e($punto['x']); ?>" cy="<?php echo e($punto['y']); ?>" r="5" fill="#629f22" stroke="white" stroke-width="2">
                                            <title><?php echo e($punto['fecha']->timezone(config('app.timezone'))->format('d/m/Y H:i')); ?>: <?php echo e($punto['valor']); ?> <?php echo e($grafico['unit']); ?></title>
                                        </circle>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </svg>
                                <div class="flex justify-between text-xs text-[#82907e]">
                                    <span><?php echo e($lecturas->first()->fecha_hora->timezone(config('app.timezone'))->format('d/m H:i')); ?></span>
                                    <span><?php echo e($lecturas->last()->fecha_hora->timezone(config('app.timezone'))->format('d/m H:i')); ?></span>
                                </div>
                            </article>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>

                <div class="rounded-3xl border border-[#e5ebdf] bg-white p-5 shadow-sm sm:p-6">
                    <div class="mb-5 flex items-center justify-between gap-4">
                        <div>
                            <h3 class="text-xl font-bold text-[#0f3c2b]">Alertas de <?php echo e($planta->nombre ?? 'esta planta'); ?></h3>
                            <p class="mt-1 text-sm text-[#718071]">Avisos activos y episodios ya resueltos.</p>
                        </div>
                        <img class="h-7 w-7" src="<?php echo e(asset('images/alerta.png')); ?>" alt="">
                    </div>
                    <?php if($alertas->isEmpty()): ?>
                        <p class="rounded-2xl bg-[#f7f9f5] p-4 text-sm text-[#718071]">Todavía no hay alertas para esta planta.</p>
                    <?php else: ?>
                        <div class="space-y-3">
                            <?php $__currentLoopData = $alertas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alerta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <article class="rounded-2xl border p-4 <?php echo e($alerta->resuelta_en ? 'border-[#e5ebdf] bg-[#f7f9f5]' : 'border-[#f1d7a9] bg-[#fff9ed]'); ?>">
                                    <div class="flex flex-col justify-between gap-2 sm:flex-row sm:items-start">
                                        <div>
                                            <p class="text-sm font-semibold <?php echo e($alerta->resuelta_en ? 'text-[#536252]' : 'text-[#684b1b]'); ?>">
                                                <?php echo e($alerta->mensaje ?? 'Alerta de tipo ' . str_replace('_', ' ', $alerta->tipo)); ?>

                                            </p>
                                            <p class="mt-1 text-xs text-[#82907e]">
                                                <?php echo e($alerta->created_at->timezone(config('app.timezone'))->format('d/m/Y H:i')); ?>

                                                <?php if($alerta->resuelta_en): ?>
                                                    · Resuelta <?php echo e($alerta->resuelta_en->timezone(config('app.timezone'))->format('d/m/Y H:i')); ?>

                                                <?php endif; ?>
                                            </p>
                                        </div>
                                        <span class="w-fit rounded-full px-3 py-1 text-xs font-bold <?php echo e($alerta->resuelta_en ? 'bg-[#e6eee1] text-[#536252]' : 'bg-[#ffedcc] text-[#8a5700]'); ?>">
                                            <?php echo e($alerta->resuelta_en ? 'Resuelta' : 'Activa'); ?>

                                        </span>
                                    </div>
                                </article>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\Adaptia\resources\views/home/partials/historial-monitoreo.blade.php ENDPATH**/ ?>