<?php $__env->startSection('title', 'Panel de Cliente - Adaptia'); ?>

<?php $__env->startSection('content'); ?>
<main class="w-full space-y-16 bg-[#f8faf6] px-4 py-8 sm:space-y-20 sm:px-8 sm:py-10 lg:px-12">
    <!-- Encabezado de bienvenida animado -->
    <section class="reveal overflow-hidden rounded-[2rem] bg-gradient-to-br from-[#0f3c2b] via-[#18543a] to-[#2d7141] shadow-xl shadow-[#0f3c2b]/10">
        <div class="relative flex flex-col items-center px-6 py-10 text-center sm:px-10 sm:py-14">
            <div class="absolute -right-12 -top-16 h-56 w-56 rounded-full border-[32px] border-white/5"></div>
            <div class="absolute -bottom-24 -left-12 h-64 w-64 rounded-full border-[36px] border-white/5"></div>
            <div class="relative mb-5 flex h-20 w-20 items-center justify-center rounded-3xl bg-white/15 shadow-inner ring-1 ring-white/20 backdrop-blur sm:h-24 sm:w-24">
                <img class="h-12 w-12 sm:h-14 sm:w-14" src="<?php echo e(asset('images/logo.png')); ?>" alt="Logo Adaptia">
            </div>
            <p class="relative mb-2 text-sm font-semibold uppercase tracking-[0.2em] text-[#d4e8ba]">Tu espacio verde empieza aquí</p>
            <h1 class="relative text-3xl font-bold tracking-tight text-white sm:text-4xl lg:text-5xl">
                ¡Hola, <?php echo e(Auth::user()->name); ?>!
            </h1>
            <p class="relative mt-3 max-w-xl text-base leading-7 text-white/80 sm:text-lg">
                Bienvenido a tu espacio personal. Encuentra recomendaciones y consulta tus plantas.
            </p>
            <div class="relative mt-6 flex flex-wrap items-center justify-center gap-3 text-sm text-white/85">
                <div class="flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-2 backdrop-blur">
                    <img class="h-4 w-4 brightness-0 invert" src="<?php echo e(asset('images/user.png')); ?>" alt="">
                    <span class="font-semibold"><?php echo e(ucfirst(Auth::user()->rol)); ?></span>
                </div>
                <div class="flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-2 backdrop-blur">
                    <img class="h-4 w-4 brightness-0 invert" src="<?php echo e(asset('images/reloj.png')); ?>" alt="">
                    <span><?php echo e(Auth::user()->updated_at->diffForHumans()); ?></span>
                </div>
            </div>
        </div>
    </section>

    <!-- Resumen del perfil como tarjetas destacadas -->
    <?php if(Auth::user()->perfilCliente): ?>
        <section class="reveal">
            <div class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="mb-2 text-xs font-bold uppercase tracking-[0.18em] text-[#7a965f]">Tus preferencias</p>
                    <h2 class="text-2xl font-bold tracking-tight text-[#0f3c2b] sm:text-3xl">
                    Tu Perfil de Cliente
                    </h2>
                </div>
                <?php
                    $perfil = Auth::user()->perfilCliente;
                    $completion = 0;
                    if ($perfil) {
                        $fields = [
                            $perfil->tamaño_adulto,
                            $perfil->luz_requerida,
                            $perfil->frecuencia_riego,
                            $perfil->tipo_ambiente,
                            $perfil->estetica,
                            !is_null($perfil->toxicidad) // toxicidad es boolean, siempre tiene valor (0 o 1) si se estableció
                        ];
                        $filled = array_filter($fields, function($field) {
                            return $field !== null && $field !== '';
                        });
                        $completion = floor((count($filled) / count($fields)) * 100);
                    }
                ?>
                <div class="w-full rounded-2xl border border-[#e5ebdf] bg-white p-4 shadow-sm sm:max-w-xs">
                    <div class="mb-2 flex items-center justify-between text-sm">
                        <span class="font-medium text-[#667466]">Perfil completado</span>
                        <span class="font-bold text-[#52752d]"><?php echo e($completion); ?>%</span>
                    </div>
                    <div class="h-2 overflow-hidden rounded-full bg-[#e9eee4]">
                        <div class="h-full rounded-full bg-gradient-to-r from-[#7cb22b] to-[#a4cc5e]" style="width: <?php echo e($completion); ?>%"></div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <!-- Tamaño adulto -->
                <div class="group flex items-center gap-4 rounded-2xl border border-[#e8ede4] bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-[#cdddbe] hover:shadow-lg sm:p-6">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#eff6e8] text-lg font-bold text-[#52752d] transition group-hover:bg-[#e4f0d8]">T</div>
                    <div class="min-w-0">
                        <h4 class="text-sm font-medium text-[#718071]">Tamaño adulto</h4>
                        <p class="mt-1 break-words text-lg font-bold text-[#0f3c2b]">
                            <?php echo e(ucfirst(Auth::user()->perfilCliente->tamaño_adulto ?? 'No especificado')); ?>

                        </p>
                    </div>
                </div>

                <!-- Luz requerida -->
                <div class="group flex items-center gap-4 rounded-2xl border border-[#e8ede4] bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-[#cdddbe] hover:shadow-lg sm:p-6">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#eff6e8] transition group-hover:bg-[#e4f0d8]">
                        <img class="h-6 w-6" src="<?php echo e(asset('images/sun.png')); ?>" alt="">
                    </div>
                    <div class="min-w-0">
                        <h4 class="text-sm font-medium text-[#718071]">Luz requerida</h4>
                        <p class="mt-1 break-words text-lg font-bold text-[#0f3c2b]">
                            <?php echo e(ucfirst(Auth::user()->perfilCliente->luz_requerida ?? 'No especificado')); ?>

                        </p>
                    </div>
                </div>

                <!-- Frecuencia de riego -->
                <div class="group flex items-center gap-4 rounded-2xl border border-[#e8ede4] bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-[#cdddbe] hover:shadow-lg sm:p-6">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#eff6e8] transition group-hover:bg-[#e4f0d8]">
                        <img class="h-6 w-6" src="<?php echo e(asset('images/water.png')); ?>" alt="">
                    </div>
                    <div class="min-w-0">
                        <h4 class="text-sm font-medium text-[#718071]">Frecuencia de riego</h4>
                        <p class="mt-1 break-words text-lg font-bold text-[#0f3c2b]">
                            <?php echo e(ucfirst(Auth::user()->perfilCliente->frecuencia_riego ?? 'No especificado')); ?>

                        </p>
                    </div>
                </div>

                <!-- Tipo de ambiente -->
                <div class="group flex items-center gap-4 rounded-2xl border border-[#e8ede4] bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-[#cdddbe] hover:shadow-lg sm:p-6">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#eff6e8] transition group-hover:bg-[#e4f0d8]">
                        <img class="h-6 w-6" src="<?php echo e(asset('images/environment.png')); ?>" alt="">
                    </div>
                    <div class="min-w-0">
                        <h4 class="text-sm font-medium text-[#718071]">Tipo de ambiente</h4>
                        <p class="mt-1 break-words text-lg font-bold text-[#0f3c2b]">
                            <?php echo e(ucfirst(Auth::user()->perfilCliente->tipo_ambiente ?? 'No especificado')); ?>

                        </p>
                    </div>
                </div>

                <!-- Estética preferida -->
                <div class="group flex items-center gap-4 rounded-2xl border border-[#e8ede4] bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-[#cdddbe] hover:shadow-lg sm:p-6">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#eff6e8] transition group-hover:bg-[#e4f0d8]">
                        <img class="h-6 w-6" src="<?php echo e(asset('images/style.png')); ?>" alt="">
                    </div>
                    <div class="min-w-0">
                        <h4 class="text-sm font-medium text-[#718071]">Estética preferida</h4>
                        <p class="mt-1 break-words text-lg font-bold text-[#0f3c2b]">
                            <?php echo e(ucfirst(Auth::user()->perfilCliente->estetica ?? 'No especificado')); ?>

                        </p>
                    </div>
                </div>

                <!-- Evita plantas tóxicas -->
                <div class="group flex items-center gap-4 rounded-2xl border border-[#e8ede4] bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-[#cdddbe] hover:shadow-lg sm:p-6">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#eff6e8] transition group-hover:bg-[#e4f0d8]">
                        <img class="h-6 w-6" src="<?php echo e(asset('images/toxicity.png')); ?>" alt="">
                    </div>
                    <div class="min-w-0">
                        <h4 class="text-sm font-medium text-[#718071]">Evita plantas tóxicas</h4>
                        <p class="mt-1 break-words text-lg font-bold text-[#0f3c2b]">
                            <?php echo e(Auth::user()->perfilCliente->toxicidad ? 'Sí' : 'No'); ?>

                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Botón para actualizar perfil -->
        <section class="reveal flex flex-col items-center justify-between gap-5 rounded-3xl border border-[#dce9d0] bg-[#eff6e8] p-6 text-center sm:flex-row sm:p-8 sm:text-left">
            <div>
                <h2 class="text-xl font-bold text-[#0f3c2b]">¿Quieres ajustar tus preferencias?</h2>
                <p class="mt-1 text-sm leading-6 text-[#63745f]">Actualiza tu perfil para afinar tus recomendaciones de plantas.</p>
            </div>
            <a href="<?php echo e(Auth::check() ? route('perfil.edit') : route('login')); ?>"
               class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-[#7cb22b] px-6 py-3 font-bold text-white shadow-md shadow-[#7cb22b]/20 transition duration-200 hover:-translate-y-0.5 hover:bg-[#6eab26] hover:shadow-lg">
                Actualizar mi perfil <img class="h-4 w-4 brightness-0 invert" src="<?php echo e(asset('images/refresh.png')); ?>" alt="">
            </a>
        </section>

        <!-- Sección de Sensores -->
        <section class="reveal">
            <?php
                // Intentamos obtener los sensores del usuario con sus lecturas más recientes
                $sensoresConLecturas = collect();
                try {
                    // Obtener ventas del usuario
                    $ventas = Auth::user()->ventas ?? collect();

                    // Obtener plantas vendidas de esas ventas
                    $plantasVendidas = $ventas->flatMap(function($venta) {
                        return $venta->plantas_vendidas ?? collect();
                    }) ?? collect();

                    // Filtrar solo aquellas plantas vendidas que tienen sensor asignado
                    $plantasConSensor = $plantasVendidas->filter(function($plantaVendida) {
                        return !is_null($plantaVendida->sensor_id);
                    });

                    // Para cada planta con sensor, obtener el sensor y su última lectura
                    foreach ($plantasConSensor as $plantaVendida) {
                        $sensor = $plantaVendida->sensor ?? null;
                        $ultimaLectura = $plantaVendida->lecturas_sensores
                                                ->sortByDesc('fecha_hora')
                                                ->first() ?? null;

                        if ($sensor && $ultimaLectura) {
                            $sensoresConLecturas->push([
                                'sensor' => $sensor,
                                'lectura' => $ultimaLectura,
                                'planta_vendida' => $plantaVendida
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    // Si hay algún error en las relaciones, continuar con colección vacía
                    $sensoresConLecturas = collect();
                }
            ?>

            <?php if($sensoresConLecturas->isNotEmpty()): ?>
                <div class="mb-6">
                    <p class="mb-2 text-xs font-bold uppercase tracking-[0.18em] text-[#7a965f]">Monitoreo</p>
                    <h2 class="text-2xl font-bold tracking-tight text-[#0f3c2b] sm:text-3xl">Tus sensores activos</h2>
                    <p class="mt-2 text-base text-[#718071]">Consulta las condiciones recientes de tus plantas.</p>
                </div>

                <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
                    <?php $__currentLoopData = $sensoresConLecturas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $sensor = $item['sensor'];
                            $lectura = $item['lectura'];
                            $plantaVendida = $item['planta_vendida'];

                            // Determinar estado basado en los valores (valores de ejemplo, ajustar según rangos reales)
                            $humedad = $lectura->humedad ?? 0;
                            $temperatura = $lectura->temperatura ?? 0;

                            // Lógica simple de estado (en producción esto sería más sofisticado)
                            $estadoTexto = 'Normal';
                            $estadoClase = 'bg-[#10b981] text-white'; // verde

                            if ($humedad < 30 || $humedad > 80) {
                                $estadoTexto = 'Humedad fuera de rango';
                                $estadoClase = 'bg-[#f97316] text-white'; // naranja
                            }

                            if ($temperatura < 10 || $temperatura > 30) {
                                $estadoTexto = 'Temperatura fuera de rango';
                                $estadoClase = 'bg-[#ef4444] text-white'; // rojo
                            }
                        ?>
                        <div class="rounded-3xl border border-[#e5ebdf] bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-lg sm:p-7">
                            <div class="mb-5 flex items-center justify-between gap-4">
                                <div class="flex min-w-0 items-center gap-3">
                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#eff6e8]">
                                        <img class="h-6 w-6" src="<?php echo e(asset('images/sensor.png')); ?>" alt="">
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold uppercase tracking-wider text-[#82907e]">Sensor conectado</p>
                                        <h3 class="truncate font-bold text-[#0f3c2b]">
                                            #<?php echo e($sensor->identificador_fisico ?? 'N/A'); ?>

                                        </h3>
                                    </div>
                                </div>
                                <span class="shrink-0 rounded-full px-3 py-1.5 text-xs font-bold <?php echo e($estadoClase); ?>">
                                    <?php echo e($estadoTexto); ?>

                                </span>
                            </div>

                            <div class="mb-5 flex flex-col gap-2 rounded-2xl bg-[#f7f9f5] px-4 py-3 text-sm sm:flex-row sm:items-center sm:justify-between">
                                <span class="font-medium text-[#718071]">Planta asociada</span>
                                <span class="font-semibold text-[#34533b]"><?php echo e($plantaVendida->planta->nombre ?? 'Planta desconocida'); ?></span>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="rounded-2xl border border-[#e8ede4] bg-white p-4">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-[#82907e]">Humedad</p>
                                    <p class="mt-2 text-3xl font-bold tracking-tight text-[#0f3c2b]"><?php echo e(number_format($humedad, 1)); ?><span class="text-lg text-[#7a965f]">%</span></p>
                                </div>
                                <div class="rounded-2xl border border-[#e8ede4] bg-white p-4">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-[#82907e]">Temperatura</p>
                                    <p class="mt-2 text-3xl font-bold tracking-tight text-[#0f3c2b]"><?php echo e(number_format($temperatura, 1)); ?><span class="text-lg text-[#7a965f]">°C</span></p>
                                </div>
                            </div>
                            <div class="mt-5 flex flex-col gap-3 border-t border-[#edf1e9] pt-4 sm:flex-row sm:items-center sm:justify-between">
                                <p class="text-xs text-[#82907e]">Última lectura: <?php echo e($lectura->fecha_hora ? $lectura->fecha_hora->format('d/m H:i') : 'Reciente'); ?></p>
                                <a href="<?php echo e(route('sensores.index')); ?>"
                                   class="font-semibold text-[#608d2e] transition hover:text-[#416d1e]">
                                    Ver todos mis sensores <span aria-hidden="true">→</span>
                                </a>
                            </div>
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
                            Cuando tengas sensores vinculados a tus plantas, podrás consultar aquí sus lecturas y condiciones.
                        </p>
                    </div>
                    <a href="<?php echo e(route('catalogo.plantas.index')); ?>"
                       class="inline-flex min-h-12 items-center justify-center rounded-xl bg-[#7cb22b] px-6 py-3 font-bold text-white shadow-md shadow-[#7cb22b]/20 transition hover:-translate-y-0.5 hover:bg-[#6eab26]">
                        Explorar plantas disponibles
                    </a>
                </div>
            <?php endif; ?>
    <?php endif; ?>

    <!-- Recomendaciones personalizadas -->
    <section class="reveal">
        <div class="mb-7">
            <p class="mb-2 text-xs font-bold uppercase tracking-[0.18em] text-[#7a965f]">Selección personalizada</p>
            <h2 class="text-2xl font-bold tracking-tight text-[#0f3c2b] sm:text-3xl">
                Recomendaciones para ti
            </h2>
            <p class="mt-2 text-base text-[#718071]">
                Plantas que pueden encajar con las condiciones de tu perfil.
            </p>
        </div>

        <?php if(count($recomendaciones) > 0): ?>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
                <?php $__currentLoopData = $recomendaciones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $planta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <article class="group overflow-hidden rounded-3xl border border-[#e8ede4] bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl">
                        <div class="relative flex h-56 items-center justify-center overflow-hidden bg-gradient-to-br from-[#f3f8ee] to-[#eaf2e3] p-6">
                            <span class="absolute left-4 top-4 rounded-full border border-white/80 bg-white/85 px-3 py-1 text-xs font-bold text-[#52752d] shadow-sm">Recomendada</span>
                            <?php if($planta->imagen): ?>
                                <img src="<?php echo e(asset('storage/' . $planta->imagen)); ?>"
                                     alt="<?php echo e($planta->nombre); ?>"
                                     class="h-full max-h-44 w-3/4 object-contain transition duration-500 group-hover:scale-105">
                            <?php else: ?>
                                <img src="<?php echo e(asset('images/hoja_verde.png')); ?>"
                                     alt=""
                                     class="h-28 w-28 object-contain opacity-70">
                            <?php endif; ?>
                        </div>
                        <div class="p-5 sm:p-6">
                            <h3 class="mb-2 text-xl font-bold text-[#0f3c2b]"><?php echo e($planta->nombre); ?></h3>
                            <p class="mb-5 line-clamp-2 min-h-10 text-sm leading-5 text-[#718071]">
                                <?php echo e($planta->descripcion ?? 'Descripción no disponible'); ?>

                            </p>
                            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-[#edf1e9] pt-4">
                                <span class="rounded-full bg-[#eff6e8] px-3 py-1.5 text-xs font-bold text-[#52752d]">
                                    Compatibilidad <?php echo e(number_format($planta->compatibilidad_porcentaje, 1)); ?>%
                                </span>
                                <a href="<?php echo e(route('catalogo.plantas.index')); ?>"
                                   class="font-semibold text-[#608d2e] transition hover:text-[#416d1e]">
                                    Explorar catálogo <span aria-hidden="true">→</span>
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php else: ?>
            <div class="rounded-3xl border border-dashed border-[#ccd9c1] bg-white px-6 py-10 text-center">
                <p class="text-base font-semibold text-[#34533b]">Todavía no hay recomendaciones disponibles.</p>
                <p class="mt-2 text-sm text-[#718071]">Completa tus preferencias para descubrir plantas adecuadas para ti.</p>
                <a href="<?php echo e(route('perfil.edit')); ?>"
                   class="mt-5 inline-flex min-h-11 items-center justify-center rounded-xl bg-[#7cb22b] px-5 py-2.5 font-bold text-white transition hover:bg-[#6eab26]">
                    Completar mi perfil
                </a>
            </div>
        <?php endif; ?>
    </section>

    <!-- Acciones rápidas -->
    <section class="reveal pt-12 sm:pt-16">
        <div class="mb-7">
            <p class="mb-2 text-xs font-bold uppercase tracking-[0.18em] text-[#7a965f]">Tu cuenta</p>
            <h2 class="text-2xl font-bold tracking-tight text-[#0f3c2b] sm:text-3xl">
                Accesos rápidos
            </h2>
            <p class="mt-2 text-base text-[#718071]">
                Accede directamente a las funciones principales.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <!-- Editar perfil -->
            <a href="<?php echo e(route('perfil.edit')); ?>"
               class="group flex min-h-48 flex-col rounded-2xl border border-[#e5ebdf] bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-[#cdddbe] hover:shadow-lg sm:p-6">
                <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-[#eff6e8] transition group-hover:bg-[#e4f0d8]">
                    <img class="h-6 w-6" src="<?php echo e(asset('images/editar.png')); ?>" alt="">
                </div>
                <span class="font-bold text-[#0f3c2b]">Editar perfil</span>
                <span class="mt-1 text-sm leading-5 text-[#718071]">Actualizar tus preferencias</span>
                <span class="mt-auto pt-4 text-sm font-semibold text-[#608d2e]">Abrir perfil <span aria-hidden="true">→</span></span>
            </a>

            <!-- Ver catálogo -->
            <a href="<?php echo e(route('catalogo.plantas.index')); ?>"
               class="group flex min-h-48 flex-col rounded-2xl border border-[#e5ebdf] bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-[#cdddbe] hover:shadow-lg sm:p-6">
                <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-[#eff6e8] transition group-hover:bg-[#e4f0d8]">
                    <img class="h-6 w-6" src="<?php echo e(asset('images/plantas.png')); ?>" alt="">
                </div>
                <span class="font-bold text-[#0f3c2b]">Ver catálogo</span>
                <span class="mt-1 text-sm leading-5 text-[#718071]">Explora nuestras plantas</span>
                <span class="mt-auto pt-4 text-sm font-semibold text-[#608d2e]">Explorar <span aria-hidden="true">→</span></span>
            </a>

            <!-- Mis sensores -->
            <a href="<?php echo e(route('sensores.index')); ?>"
               class="group flex min-h-48 flex-col rounded-2xl border border-[#e5ebdf] bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-[#cdddbe] hover:shadow-lg sm:p-6">
                <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-[#eff6e8] transition group-hover:bg-[#e4f0d8]">
                    <img class="h-6 w-6" src="<?php echo e(asset('images/sensores.png')); ?>" alt="">
                </div>
                <span class="font-bold text-[#0f3c2b]">Mis sensores</span>
                <span class="mt-1 text-sm leading-5 text-[#718071]">Monitorea tus sensores</span>
                <span class="mt-auto pt-4 text-sm font-semibold text-[#608d2e]">Ver sensores <span aria-hidden="true">→</span></span>
            </a>

            <!-- Historial y Alertas -->
            <a href="<?php echo e(route('historial-alertas')); ?>"
               class="group flex min-h-48 flex-col rounded-2xl border border-[#e5ebdf] bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-[#cdddbe] hover:shadow-lg sm:p-6">
                <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-[#eff6e8] transition group-hover:bg-[#e4f0d8]">
                    <img class="h-6 w-6" src="<?php echo e(asset('images/historial.png')); ?>" alt="">
                </div>
                <span class="font-bold text-[#0f3c2b]">Historial y alertas</span>
                <span class="mt-1 text-sm leading-5 text-[#718071]">Consulta lecturas y notificaciones</span>
                <span class="mt-auto pt-4 text-sm font-semibold text-[#608d2e]">Ver historial <span aria-hidden="true">→</span></span>
            </a>
        </div>
    </section>
</main>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    // Wizard de perfil animado
    document.addEventListener('DOMContentLoaded', function() {
        const wizardTrigger = document.getElementById('wizard-trigger');
        if (!wizardTrigger) return;

        // Crear el modal del wizard
        const wizardModal = document.createElement('div');
        wizardModal.innerHTML = `
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden" id="profile-wizard-modal">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-4 p-6">
                    <div class="flex justify-between items-start mb-4">
                        <h2 class="text-xl font-bold text-[#0f3c2b]">Actualizar mi perfil</h2>
                        <button id="wizard-close" class="text-gray-500 hover:text-gray-700">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="space-y-6" id="wizard-steps">
                        <!-- Paso 1: Información básica -->
                        <div class="wizard-step active">
                            <h3 class="text-lg font-bold text-[#0f3c2b] mb-4">Paso 1: Tu espacio y luz</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Tamaño adulto de planta preferido</label>
                                    <select class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#7cb22b]"
                                            id="wizard-tamanio">
                                        <option value="">Selecciona una opción</option>
                                        <option value="pequena">Pequeña (Estante/Mesa)</option>
                                        <option value="mediana">Mediana (Habitación)</option>
                                        <option value="grande">Grande (Jardín/Patio)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Luz requerida en tu espacio</label>
                                    <select class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#7cb22b]"
                                            id="wizard-luz">
                                        <option value="">Selecciona una opción</option>
                                        <option value="baja">Baja (Sombra)</option>
                                        <option value="media">Media (Luz indirecta)</option>
                                        <option value="alta">Alta (Mucha luz)</option>
                                        <option value="siempre_en_el_sol">Siempre al sol</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Paso 2: Cuidado y ambiente -->
                        <div class="wizard-step">
                            <h3 class="text-lg font-bold text-[#0f3c2b] mb-4">Paso 2: Cuidado y ambiente</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Nivel de experiencia en cuidado de plantas</label>
                                    <select class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#7cb22b]"
                                            id="wizard-nivel">
                                        <option value="">Selecciona una opción</option>
                                        <option value="principiante">Principiante</option>
                                        <option value="intermedio">Intermedio</option>
                                        <option value="experto">Experto</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de ambiente</label>
                                    <select class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#7cb22b]"
                                            id="wizard-ambiente">
                                        <option value="">Selecciona una opción</option>
                                        <option value="interiores">Interiores</option>
                                        <option value="exteriores">Exteriores</option>
                                        <option value="ambos">Ambos</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Paso 3: Preferencias adicionales -->
                        <div class="wizard-step">
                            <h3 class="text-lg font-bold text-[#0f3c2b] mb-4">Paso 3: Preferencias adicionales</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Frecuencia de riego que prefieres</label>
                                    <select class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#7cb22b]"
                                            id="wizard-riego">
                                        <option value="">Selecciona una opción</option>
                                        <option value="diario">Diario</option>
                                        <option value="cada_3_dias">Cada 3 días</option>
                                        <option value="semanal">Semanal</option>
                                        <option value="quincenal">Quincenal</option>
                                        <option value="mensualmente">Mensualmente</option>
                                    </select>
                                </div>
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="wizard-toxicidad" type="checkbox" value="" class="w-4 h-4 text-[#7cb22b] bg-gray-100 border-gray-300 rounded focus:ring-2 focus:ring-[#7cb22b]">
                                    </div>
                                    <div class="ml-3 text-start">
                                        <label for="wizard-toxicidad" class="text-sm font-medium text-gray-700">
                                            Prefiero evitar plantas tóxicas (por seguridad de mascotas o niños)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-200">
                        <div id="wizard-progress" class="flex justify-between text-sm text-gray-500 mb-3">
                            <span>Paso <span id="wizard-current-step">1</span> de 3</span>
                            <span id="wizard-completion">33% completado</span>
                        </div>
                        <div class="flex w-full space-x-3">
                            <button id="wizard-prev"
                                    class="px-4 py-2 bg-gray-200 text-gray-600 rounded-md hover:bg-gray-300 disabled:opacity-50"
                                    disabled>
                                Anterior
                            </button>
                            <button id="wizard-next"
                                    class="px-6 py-2 bg-[#7cb22b] text-white rounded-md hover:bg-[#6eab26]">
                                Siguiente
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(wizardModal);

        const modal = document.getElementById('profile-wizard-modal');
        const closeBtn = document.getElementById('wizard-close');
        const prevBtn = document.getElementById('wizard-prev');
        const nextBtn = document.getElementById('wizard-next');
        const steps = document.querySelectorAll('.wizard-step');
        const progressText = document.getElementById('wizard-progress');
        const currentStepSpan = document.getElementById('wizard-current-step');
        const completionSpan = document.getElementById('wizard-completion');

        let currentStep = 0;

        function showStep(stepIndex) {
            steps.forEach((step, index) => {
                step.classList.toggle('active', index === stepIndex);
            });
            currentStep = stepIndex;
            currentStepSpan.textContent = stepIndex + 1;
            completionSpan.textContent = `${Math.floor((stepIndex + 1) / steps.length * 100)}% completado`;
            prevBtn.disabled = stepIndex === 0;
            nextBtn.textContent = stepIndex === steps.length - 1 ? 'Finalizar' : 'Siguiente';
        }

        function openWizard() {
            modal.classList.remove('hidden');
            // Prellenar con valores actuales si existen
            const perfil = Auth::user()->perfilCliente;
            if (perfil) {
                document.getElementById('wizard-tamanio').value = perfil.tamaño_adulto || '';
                document.getElementById('wizard-luz').value = perfil.luz_requerida || '';
                document.getElementById('wizard-riego').value = perfil.frecuencia_riego || '';
                document.getElementById('wizard-nivel').value = perfil.nivel_cuidado || '';
                document.getElementById('wizard-ambiente').value = perfil.tipo_ambiente || '';
                document.getElementById('wizard-toxicidad').checked = perfil.toxicidad;
            }
            showStep(0);
        }

        function closeWizard() {
            modal.classList.add('hidden');
        }

        function handleNext() {
            if (currentStep < steps.length - 1) {
                showStep(currentStep + 1);
            } else {
                // Recopilar datos y guardar
                const formData = {
                    tamanho_adulto: document.getElementById('wizard-tamanio').value,
                    luz_requerida: document.getElementById('wizard-luz').value,
                    frecuencia_riego: document.getElementById('wizard-riego').value,
                    nivel_cuidado: document.getElementById('wizard-nivel').value,
                    tipo_ambiente: document.getElementById('wizard-ambiente').value,
                    toxicidad: document.getElementById('wizard-toxicidad').checked ? 1 : 0
                };

                // Validar que todos los campos requeridos estén llenos
                const requiredFields = ['tamanio_adulto', 'luz_requerida', 'frecuencia_riego', 'nivel_cuidado', 'tipo_ambiente'];
                const missingFields = requiredFields.filter(field => !formData[field]);

                if (missingFields.length > 0) {
                    alert('Por favor completa todos los campos requeridos');
                    return;
                }

                // Aquí iría la llamada AJAX para guardar el perfil
                // Por ahora simulamos el guardado y recargamos la página
                alert('Perfil actualizado correctamente');
                closeWizard();
                location.reload();
            }
        }

        function handlePrev() {
            if (currentStep > 0) {
                showStep(currentStep - 1);
            }
        }

        // Event listeners
        wizardTrigger.addEventListener('click', function(e) {
            e.preventDefault();
            openWizard();
        });

        closeBtn.addEventListener('click', closeWizard);
        prevBtn.addEventListener('click', handlePrev);
        nextBtn.addEventListener('click', handleNext);

        // Clic fuera del modal para cerrar
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeWizard();
            }
        });

        // Tecla Escape para cerrar
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeWizard();
            }
        });
    });

    // Función para calcular el porcentaje de completion del perfil
    function calculateProfileCompletion(perfil) {
        if (!perfil) return 0;

        const fields = [
            perfil.tamaño_adulto,
            perfil.luz_requerida,
            perfil.frecuencia_riego,
            perfil.tipo_ambiente,
            perfil.estetica,
            perfil.toxicidad !== null ? true : false // Consideramos que toxicidad siempre tiene valor (0 o 1)
        ];

        const filledFields = fields.filter(field => field !== null && field !== '' && field !== undefined).length;
        return Math.floor((filledFields / fields.length) * 100);
    }
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Adaptia\resources\views/home/client.blade.php ENDPATH**/ ?>