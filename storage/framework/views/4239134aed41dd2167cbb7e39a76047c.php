<?php $__env->startSection('title', 'Panel de Cliente - Adaptia'); ?>

<?php $__env->startSection('content'); ?>
<main>
    <!-- Encabezado de bienvenida animado -->
    <section class="mb-12 reveal">
        <div class="flex flex-col items-center p-8 bg-[#f0f9ff] rounded-2xl">
            <div class="w-24 h-24 bg-[#7cb22b] rounded-full flex items-center justify-center mb-4">
                <img class="w-12 h-12" src="<?php echo e(asset('images/logo.png')); ?>" alt="Logo Adaptia">
            </div>
            <h1 class="text-3xl font-bold text-[#0f3c2b] mb-2">
                ¡Hola, <?php echo e(Auth::user()->name); ?>!
            </h1>
            <p class="text-lg text-[#5f6b54] mb-4">
                Bienvenido a tu panel personal de Adaptia
            </p>
            <div class="flex items-center space-x-4 text-sm text-gray-500">
                <div class="flex items-center">
                    <img class="w-4 h-4 mr-1" src="<?php echo e(asset('images/user.png')); ?>" alt="">
                    <span class="font-semibold"><?php echo e(ucfirst(Auth::user()->rol)); ?></span>
                </div>
                <div class="flex items-center">
                    <img class="w-4 h-4 mr-1" src="<?php echo e(asset('images/reloj.png')); ?>" alt="">
                    <span><?php echo e(Auth::user()->updated_at->diffForHumans()); ?></span>
                </div>
            </div>
        </div>
    </section>

    <!-- Resumen del perfil como tarjetas destacadas -->
    <?php if(Auth::user()->perfilCliente): ?>
        <section class="mb-12 reveal">
            <div class="text-center mb-6">
                <h2 class="text-2xl font-bold text-[#0f3c2b]">
                    Tu Perfil de Cliente
                </h2>
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
                <p class="text-xl text-[#5f6b54]">
                    Completo en <?php echo e($completion); ?>%
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Tamaño adulto -->
                <div class="bg-white rounded-xl shadow-md p-6 flex flex-col items-center text-center hover:shadow-lg transition-shadow duration-300">
                    <div class="w-10 h-10 bg-[#f0f9ff] rounded-full flex items-center justify-center mb-3">
                        <div class="text-xs font-medium text-[#0f3c2b]">T</div>
                    </div>
                    <h4 class="font-semibold text-[#0f3c2b] mb-2">Tamaño adulto</h4>
                    <p class="text-lg font-bold text-[#7cb22b]">
                        <?php echo e(ucfirst(Auth::user()->perfilCliente->tamaño_adulto ?? 'No especificado')); ?>

                    </p>
                </div>

                <!-- Luz requerida -->
                <div class="bg-white rounded-xl shadow-md p-6 flex flex-col items-center text-center hover:shadow-lg transition-shadow duration-300">
                    <div class="w-10 h-10 bg-[#f0f9ff] rounded-full flex items-center justify-center mb-3">
                        <img class="w-5 h-5" src="<?php echo e(asset('images/sun.png')); ?>" alt="">
                    </div>
                    <h4 class="font-semibold text-[#0f3c2b] mb-2">Luz requerida</h4>
                    <p class="text-lg font-bold text-[#7cb22b]">
                        <?php echo e(ucfirst(Auth::user()->perfilCliente->luz_requerida ?? 'No especificado')); ?>

                    </p>
                </div>

                <!-- Frecuencia de riego -->
                <div class="bg-white rounded-xl shadow-md p-6 flex flex-col items-center text-center hover:shadow-lg transition-shadow duration-300">
                    <div class="w-10 h-10 bg-[#f0f9ff] rounded-full flex items-center justify-center mb-3">
                        <img class="w-5 h-5" src="<?php echo e(asset('images/water.png')); ?>" alt="">
                    </div>
                    <h4 class="font-semibold text-[#0f3c2b] mb-2">Frecuencia de riego</h4>
                    <p class="text-lg font-bold text-[#7cb22b]">
                        <?php echo e(ucfirst(Auth::user()->perfilCliente->frecuencia_riego ?? 'No especificado')); ?>

                    </p>
                </div>

                <!-- Tipo de ambiente -->
                <div class="bg-white rounded-xl shadow-md p-6 flex flex-col items-center text-center hover:shadow-lg transition-shadow duration-300">
                    <div class="w-10 h-10 bg-[#f0f9ff] rounded-full flex items-center justify-center mb-3">
                        <img class="w-5 h-5" src="<?php echo e(asset('images/environment.png')); ?>" alt="">
                    </div>
                    <h4 class="font-semibold text-[#0f3c2b] mb-2">Tipo de ambiente</h4>
                    <p class="text-lg font-bold text-[#7cb22b]">
                        <?php echo e(ucfirst(Auth::user()->perfilCliente->tipo_ambiente ?? 'No especificado')); ?>

                    </p>
                </div>

                <!-- Estética preferida -->
                <div class="bg-white rounded-xl shadow-md p-6 flex flex-col items-center text-center hover:shadow-lg transition-shadow duration-300">
                    <div class="w-10 h-10 bg-[#f0f9ff] rounded-full flex items-center justify-center mb-3">
                        <img class="w-5 h-5" src="<?php echo e(asset('images/style.png')); ?>" alt="">
                    </div>
                    <h4 class="font-semibold text-[#0f3c2b] mb-2">Estética preferida</h4>
                    <p class="text-lg font-bold text-[#7cb22b]">
                        <?php echo e(ucfirst(Auth::user()->perfilCliente->estetica ?? 'No especificado')); ?>

                    </p>
                </div>

                <!-- Evita plantas tóxicas -->
                <div class="bg-white rounded-xl shadow-md p-6 flex flex-col items-center text-center hover:shadow-lg transition-shadow duration-300">
                    <div class="w-10 h-10 bg-[#f0f9ff] rounded-full flex items-center justify-center mb-3">
                        <img class="w-5 h-5" src="<?php echo e(asset('images/toxicity.png')); ?>" alt="">
                    </div>
                    <h4 class="font-semibold text-[#0f3c2b] mb-2">Evita plantas tóxicas</h4>
                    <p class="text-lg font-bold text-[#7cb22b]">
                        <?php echo e(Auth::user()->perfilCliente->toxicidad ? 'Sí' : 'No'); ?>

                    </p>
                </div>
            </div>
        </section>

        <!-- Botón para actualizar perfil -->
        <section class="mb-12 reveal text-center">
            <a href="<?php echo e(Auth::check() ? route('perfil.edit') : route('login')); ?>"
               id="wizard-trigger"
               class="inline-block bg-[#7cb22b] text-white px-8 py-4 rounded-xl font-bold text-lg hover:scale-105 transition-transform duration-300 shadow-lg hover:shadow-xl">
                Actualizar mi perfil <img class="w-4 h-4 ml-2" src="<?php echo e(asset('images/refresh.png')); ?>" alt="">
            </a>
            <p class="mt-3 text-sm text-gray-500">
                Haz clic para revisar y actualizar tus preferencias de plantas
            </p>
        </section>

        <!-- Sección de Sensores -->
        <section class="mb-12 reveal">
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
                <div class="text-center mb-4">
                    <h3 class="text-xl font-bold text-[#0f3c2b]">Tus Sensores Activos</h3>
                    <p class="text-lg text-[#5f6b54]">Monitoreo en tiempo real de tus plantas</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                        <div class="bg-white rounded-2xl shadow-md p-6 hover:shadow-lg transition-shadow duration-300 border border-[#e5e7eb]">
                            <div class="flex items-center mb-4">
                                <div class="w-10 h-10 bg-[#f0f9ff] rounded-full flex items-center justify-center mr-3">
                                    <img class="w-6 h-6" src="<?php echo e(asset('images/sensor.png')); ?>" alt="Sensor">
                                </div>
                                <h4 class="font-semibold text-[#0f3c2b]">
                                    Sensor #<?php echo e($sensor->identificador_fisico ?? 'N/A'); ?>

                                </h4>
                            </div>

                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-sm font-medium text-gray-600">Planta asociada:</span>
                                    <span class="text-sm font-medium"><?php echo e($plantaVendida->planta->nombre ?? 'Planta desconocida'); ?></span>
                                </div>

                                <div class="flex justify-between">
                                    <span class="text-sm font-medium text-gray-600">Última lectura:</span>
                                    <span class="text-sm font-medium"><?php echo e($lectura->fecha_hora ? $lectura->fecha_hora->format('d/m H:i') : 'Reciente'); ?></span>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div class="text-center">
                                        <div class="text-xs text-gray-500">Humedad</div>
                                        <div class="text-2xl font-bold text-[#0f3c2b]"><?php echo e(number_format($humedad, 1)); ?>%</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-xs text-gray-500">Temperatura</div>
                                        <div class="text-2xl font-bold text-[#0f3c2b]"><?php echo e(number_format($temperatura, 1)); ?>°C</div>
                                    </div>
                                </div>

                                <div class="mt-4 p-3 rounded-lg <?php echo e($estadoClase); ?> text-center text-sm font-medium">
                                    Estado: <?php echo e($estadoTexto); ?>

                                </div>

                                <div class="mt-4 text-center">
                                    <a href="<?php echo e(route('sensores.index')); ?>"
                                       class="text-[#7cb22b] font-semibold hover:text-[#6eab26] transition-colors duration-200">
                                        Ver todos mis sensores →
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

            <?php else: ?>
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-8 text-center">
                    <h3 class="text-lg font-bold text-yellow-800 mb-4">Sensores no asignados</h3>
                    <p class="text-sm text-yellow-700 mb-6">
                        Asigna sensores a tus plantas para comenzar a monitorear su entorno en tiempo real.
                    </p>
                    <div class="space-y-3">
                        <div class="text-center">
                            <img class="w-16 h-16 mx-auto mb-3" src="<?php echo e(asset('images/plantas.png')); ?>" alt="Plantas">
                        </div>
                        <p class="text-sm text-yellow-600">
                            1. Compra o registra tus plantas en el catálogo<br>
                            2. Asigna sensores a esas plantas<br>
                            3. ¡Monitorea su entorno en tiempo real!
                        </p>
                        <a href="<?php echo e(route('plantas.index')); ?>"
                           class="inline-block mt-4 bg-[#7cb22b] text-white px-6 py-3 rounded-xl font-bold hover:bg-[#6eab26] transition-transform duration-300">
                            Explorar plantas disponibles
                        </a>
                    </div>
                </div>
            <?php endif; ?>
    <?php endif; ?>

    <!-- Recomendaciones personalizadas -->
    <section class="mb-12 reveal">
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-[#0f3c2b]">
                Recomendaciones para ti
            </h2>
            <p class="text-lg text-[#5f6b54]">
                Seleccionadas según las condiciones de tu perfil
            </p>
        </div>

        <?php if(count($recomendaciones) > 0): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php $__currentLoopData = $recomendaciones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $planta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="bg-white rounded-2xl shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300 group">
                        <div class="h-48 bg-gray-50 flex items-center justify-center">
                            <img src="<?php echo e(asset('images/' . strtolower(str_replace(' ', '_', $planta->nombre)) . '.png')); ?>"
                                 alt="<?php echo e($planta->nombre); ?>"
                                 class="max-w-full max-h-full object-contain w-3/4">
                        </div>
                        <div class="p-6">
                            <h4 class="font-bold text-[#0f3c2b] mb-2"><?php echo e($planta->nombre); ?></h4>
                            <p class="text-gray-600 text-sm mb-4 line-clamp-2">
                                <?php echo e($planta->descripcion ?? 'Descripción no disponible'); ?>

                            </p>
                            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                                <div class="flex items-center space-x-2">
                                    <span class="bg-[#f0f9ff] text-[#0f3c2b] px-3 py-1 rounded-full text-sm font-medium">
                                        Compatibilidad: <?php echo e(number_format($planta->pivot->score ?? 0, 1)); ?>%
                                    </span>
                                </div>
                                <a href="<?php echo e(route('plantas.show', $planta->id)); ?>"
                                   class="text-[#7cb22b] font-semibold hover:text-[#6eab26] transition-colors duration-200">
                                    Ver detalle
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php else: ?>
            <p class="text-gray-500 text-center py-8">
                No se encontraron recomendaciones basadas en tu perfil.
                <br>
                <a href="#"
                   id="wizard-trigger"
                   class="inline-block mt-4 bg-[#7cb22b] text-white px-6 py-2 rounded hover:bg-[#6eab26] transition">
                    Completa tu perfil para ver recomendaciones
                </a>
            </p>
        <?php endif; ?>
    </section>

    <!-- Acciones rápidas -->
    <section>
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-[#0f3c2b]">
                Accesos rápidos
            </h2>
            <p class="text-lg text-[#5f6b54]">
                Funciones principales de tu cuenta
            </p>
        </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Editar perfil -->
            <a href="#"
               id="wizard-trigger"
               class="bg-white rounded-2xl shadow-md p-6 flex flex-col items-center text-center hover:shadow-lg transition-shadow duration-300 border-2 border-[#7cb22b] hover:border-[#6eab26]">
                <div class="w-12 h-12 bg-[#f0f9ff] rounded-full flex items-center justify-center mb-3">
                    <img class="w-6 h-6" src="<?php echo e(asset('images/editar.png')); ?>" alt="Editar">
                </div>
                <span class="font-semibold text-[#0f3c2b] mt-2">Editar perfil</span>
                <span class="text-sm text-gray-500 mt-1">Actualizar tus preferencias</span>
            </a>

            <!-- Ver catálogo -->
            <a href="<?php echo e(route('plantas.index')); ?>"
               class="bg-white rounded-2xl shadow-md p-6 flex flex-col items-center text-center hover:shadow-lg transition-shadow duration-300 border-2 border-[#7cb22b] hover:border-[#6eab26]">
                <div class="w-12 h-12 bg-[#f0f9ff] rounded-full flex items-center justify-center mb-3">
                    <img class="w-6 h-6" src="<?php echo e(asset('images/plantas.png')); ?>" alt="Plantas">
                </div>
                <span class="font-semibold text-[#0f3c2b] mt-2">Ver catálogo</span>
                <span class="text-sm text-gray-500 mt-1">Explora nuestras plantas</span>
            </a>

            <!-- Mis sensores -->
            <a href="<?php echo e(route('sensores.index')); ?>"
               class="bg-white rounded-2xl shadow-md p-6 flex flex-col items-center text-center hover:shadow-lg transition-shadow duration-300 border-2 border-[#7cb22b] hover:border-[#6eab26]">
                <div class="w-12 h-12 bg-[#f0f9ff] rounded-full flex items-center justify-center mb-3">
                    <img class="w-6 h-6" src="<?php echo e(asset('images/sensores.png')); ?>" alt="Sensores">
                </div>
                <span class="font-semibold text-[#0f3c2b] mt-2">Mis sensores</span>
                <span class="text-sm text-gray-500 mt-1">Monitorea tus sensores</span>
            </a>

            <!-- Historial y Alertas -->
            <a href="<?php echo e(route('historial-alertas')); ?>"
               class="bg-white rounded-2xl shadow-md p-6 flex flex-col items-center text-center hover:shadow-lg transition-shadow duration-300 border-2 border-[#7cb22b] hover:border-[#6eab26]">
                <div class="w-12 h-12 bg-[#f0f9ff] rounded-full flex items-center justify-center mb-3">
                    <img class="w-6 h-6" src="<?php echo e(asset('images/historial.png')); ?>" alt="Historial">
                </div>
                <span class="font-semibold text-[#0f3c2b] mt-2">Historial y Alertas</span>
                <span class="text-sm text-gray-500 mt-1">Ver lecturas y notificaciones</span>
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