@extends('layouts.app')

@section('title', 'Panel de Cliente - Adaptia')

@section('content')
<main class="w-full space-y-16 bg-[#f8faf6] px-4 py-8 sm:space-y-20 sm:px-8 sm:py-10 lg:px-12">
    <!-- Encabezado de bienvenida animado -->
    <section class="reveal overflow-hidden rounded-[2rem] bg-gradient-to-br from-[#0f3c2b] via-[#18543a] to-[#2d7141] shadow-xl shadow-[#0f3c2b]/10">
        <div class="relative flex flex-col items-center px-6 py-10 text-center sm:px-10 sm:py-14">
            <div class="absolute -right-12 -top-16 h-56 w-56 rounded-full border-[32px] border-white/5"></div>
            <div class="absolute -bottom-24 -left-12 h-64 w-64 rounded-full border-[36px] border-white/5"></div>
            <div class="relative mb-5 flex h-20 w-20 items-center justify-center rounded-3xl bg-white/15 shadow-inner ring-1 ring-white/20 backdrop-blur sm:h-24 sm:w-24">
                <img class="h-12 w-12 sm:h-14 sm:w-14" src="{{ asset('images/logo.png') }}" alt="Logo Adaptia">
            </div>
            <p class="relative mb-2 text-sm font-semibold uppercase tracking-[0.2em] text-[#d4e8ba]">Tu espacio verde empieza aquí</p>
            <h1 class="relative text-3xl font-bold tracking-tight text-white sm:text-4xl lg:text-5xl">
                ¡Hola, {{ Auth::user()->name }}!
            </h1>
            <p class="relative mt-3 max-w-xl text-base leading-7 text-white/80 sm:text-lg">
                Bienvenido a tu espacio personal. Encuentra recomendaciones y consulta tus plantas.
            </p>
            <div class="relative mt-6 flex flex-wrap items-center justify-center gap-3 text-sm text-white/85">
                <div class="flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-2 backdrop-blur">
                    <img class="h-4 w-4 brightness-0 invert" src="{{ asset('images/user.png') }}" alt="">
                    <span class="font-semibold">{{ ucfirst(Auth::user()->rol) }}</span>
                </div>
                <div class="flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-2 backdrop-blur">
                    <img class="h-4 w-4 brightness-0 invert" src="{{ asset('images/reloj.png') }}" alt="">
                    <span>{{ Auth::user()->updated_at->diffForHumans() }}</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Resumen del perfil como tarjetas destacadas -->
    @if (Auth::user()->perfilCliente)
        <section class="reveal">
            <div class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="mb-2 text-xs font-bold uppercase tracking-[0.18em] text-[#7a965f]">Tus preferencias</p>
                    <h2 class="text-2xl font-bold tracking-tight text-[#0f3c2b] sm:text-3xl">
                    Tu Perfil de Cliente
                    </h2>
                </div>
                @php
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
                @endphp
                <div class="w-full rounded-2xl border border-[#e5ebdf] bg-white p-4 shadow-sm sm:max-w-xs">
                    <div class="mb-2 flex items-center justify-between text-sm">
                        <span class="font-medium text-[#667466]">Perfil completado</span>
                        <span class="font-bold text-[#52752d]">{{ $completion }}%</span>
                    </div>
                    <div class="h-2 overflow-hidden rounded-full bg-[#e9eee4]">
                        <div class="h-full rounded-full bg-gradient-to-r from-[#7cb22b] to-[#a4cc5e]" style="width: {{ $completion }}%"></div>
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
                            {{ ucfirst(Auth::user()->perfilCliente->tamaño_adulto ?? 'No especificado') }}
                        </p>
                    </div>
                </div>

                <!-- Luz requerida -->
                <div class="group flex items-center gap-4 rounded-2xl border border-[#e8ede4] bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-[#cdddbe] hover:shadow-lg sm:p-6">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#eff6e8] transition group-hover:bg-[#e4f0d8]">
                        <img class="h-6 w-6" src="{{ asset('images/sun.png') }}" alt="">
                    </div>
                    <div class="min-w-0">
                        <h4 class="text-sm font-medium text-[#718071]">Luz requerida</h4>
                        <p class="mt-1 break-words text-lg font-bold text-[#0f3c2b]">
                            {{ ucfirst(Auth::user()->perfilCliente->luz_requerida ?? 'No especificado') }}
                        </p>
                    </div>
                </div>

                <!-- Frecuencia de riego -->
                <div class="group flex items-center gap-4 rounded-2xl border border-[#e8ede4] bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-[#cdddbe] hover:shadow-lg sm:p-6">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#eff6e8] transition group-hover:bg-[#e4f0d8]">
                        <img class="h-6 w-6" src="{{ asset('images/water.png') }}" alt="">
                    </div>
                    <div class="min-w-0">
                        <h4 class="text-sm font-medium text-[#718071]">Frecuencia de riego</h4>
                        <p class="mt-1 break-words text-lg font-bold text-[#0f3c2b]">
                            {{ ucfirst(Auth::user()->perfilCliente->frecuencia_riego ?? 'No especificado') }}
                        </p>
                    </div>
                </div>

                <!-- Tipo de ambiente -->
                <div class="group flex items-center gap-4 rounded-2xl border border-[#e8ede4] bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-[#cdddbe] hover:shadow-lg sm:p-6">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#eff6e8] transition group-hover:bg-[#e4f0d8]">
                        <img class="h-6 w-6" src="{{ asset('images/environment.png') }}" alt="">
                    </div>
                    <div class="min-w-0">
                        <h4 class="text-sm font-medium text-[#718071]">Tipo de ambiente</h4>
                        <p class="mt-1 break-words text-lg font-bold text-[#0f3c2b]">
                            {{ ucfirst(Auth::user()->perfilCliente->tipo_ambiente ?? 'No especificado') }}
                        </p>
                    </div>
                </div>

                <!-- Estética preferida -->
                <div class="group flex items-center gap-4 rounded-2xl border border-[#e8ede4] bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-[#cdddbe] hover:shadow-lg sm:p-6">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#eff6e8] transition group-hover:bg-[#e4f0d8]">
                        <img class="h-6 w-6" src="{{ asset('images/style.png') }}" alt="">
                    </div>
                    <div class="min-w-0">
                        <h4 class="text-sm font-medium text-[#718071]">Estética preferida</h4>
                        <p class="mt-1 break-words text-lg font-bold text-[#0f3c2b]">
                            {{ ucfirst(Auth::user()->perfilCliente->estetica ?? 'No especificado') }}
                        </p>
                    </div>
                </div>

                <!-- Evita plantas tóxicas -->
                <div class="group flex items-center gap-4 rounded-2xl border border-[#e8ede4] bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-[#cdddbe] hover:shadow-lg sm:p-6">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#eff6e8] transition group-hover:bg-[#e4f0d8]">
                        <img class="h-6 w-6" src="{{ asset('images/toxicity.png') }}" alt="">
                    </div>
                    <div class="min-w-0">
                        <h4 class="text-sm font-medium text-[#718071]">Evita plantas tóxicas</h4>
                        <p class="mt-1 break-words text-lg font-bold text-[#0f3c2b]">
                            {{ Auth::user()->perfilCliente->toxicidad ? 'Sí' : 'No' }}
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
            <a href="{{ Auth::check() ? route('perfil.edit') : route('login') }}"
               class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-[#7cb22b] px-6 py-3 font-bold text-white shadow-md shadow-[#7cb22b]/20 transition duration-200 hover:-translate-y-0.5 hover:bg-[#6eab26] hover:shadow-lg">
                Actualizar mi perfil <img class="h-4 w-4 brightness-0 invert" src="{{ asset('images/refresh.png') }}" alt="">
            </a>
        </section>
    @endif

        <!-- Sección de Sensores -->
        <section id="mis-sensores" class="reveal">
            @if ($sensoresAsignados->isNotEmpty())
                <div class="mb-6">
                    <p class="mb-2 text-xs font-bold uppercase tracking-[0.18em] text-[#7a965f]">Dispositivos</p>
                    <h2 class="text-2xl font-bold tracking-tight text-[#0f3c2b] sm:text-3xl">Tus sensores asignados</h2>
                    <p class="mt-2 text-base text-[#718071]">Consulta la lectura más reciente y las alertas activas de cada planta.</p>
                </div>

                <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
                    @foreach ($sensoresAsignados as $plantaVendida)
                        <div class="rounded-3xl border border-[#e5ebdf] bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-lg sm:p-7">
                            @php
                                $ultimaLectura = $plantaVendida->lecturasSensores->first();
                                $alertasActivas = $plantaVendida->alertas->whereNull('resuelta_en');
                            @endphp
                            <div class="mb-5 flex items-center justify-between gap-4">
                                <div class="flex min-w-0 items-center gap-3">
                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#eff6e8]">
                                        <img class="h-6 w-6" src="{{ asset('images/sensor.png') }}" alt="">
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold uppercase tracking-wider text-[#82907e]">Sensor asignado</p>
                                        <h3 class="truncate font-bold text-[#0f3c2b]">
                                            #{{ $plantaVendida->sensor->identificador_fisico }}
                                        </h3>
                                    </div>
                                </div>
                                <span class="shrink-0 rounded-full px-3 py-1.5 text-xs font-bold {{ $plantaVendida->sensor->estado !== 'activo' ? 'bg-[#f8d7da] text-[#721c24]' : ($alertasActivas->isNotEmpty() ? 'bg-[#fff1dc] text-[#8a5700]' : 'bg-[#eff6e8] text-[#52752d]') }}">
                                    {{ $plantaVendida->sensor->estado !== 'activo' ? 'Sensor inactivo' : ($alertasActivas->isNotEmpty() ? 'Requiere atención' : ($ultimaLectura ? 'Sin alertas activas' : 'Esperando lectura')) }}
                                </span>
                            </div>

                            <div class="mb-5 flex flex-col gap-2 rounded-2xl bg-[#f7f9f5] px-4 py-3 text-sm sm:flex-row sm:items-center sm:justify-between">
                                <span class="font-medium text-[#718071]">Planta asociada</span>
                                <span class="font-semibold text-[#34533b]">{{ $plantaVendida->venta->planta->nombre ?? 'Planta no disponible' }}</span>
                            </div>
                            @if ($ultimaLectura)
                                <div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
                                    @foreach ([
                                        ['label' => 'Humedad del suelo', 'value' => $ultimaLectura->humedad_suelo, 'unit' => '%'],
                                        ['label' => 'Temperatura', 'value' => $ultimaLectura->temperatura, 'unit' => '°C'],
                                        ['label' => 'Humedad ambiental', 'value' => $ultimaLectura->humedad_ambiental, 'unit' => '%'],
                                        ['label' => 'Luz', 'value' => $ultimaLectura->luz, 'unit' => 'lux'],
                                    ] as $medicion)
                                        <div class="rounded-2xl bg-[#f7f9f5] p-3">
                                            <p class="text-xs font-medium text-[#718071]">{{ $medicion['label'] }}</p>
                                            <p class="mt-1 text-lg font-bold text-[#0f3c2b]">{{ rtrim(rtrim(number_format((float) $medicion['value'], 2), '0'), '.') }} <span class="text-sm font-semibold">{{ $medicion['unit'] }}</span></p>
                                        </div>
                                    @endforeach
                                </div>
                                <p class="text-xs text-[#82907e]">
                                    Última lectura: {{ $ultimaLectura->fecha_hora->timezone(config('app.timezone'))->format('d/m/Y H:i') }}
                                </p>
                            @else
                                <div class="rounded-2xl border border-dashed border-[#dce9d0] bg-[#f7f9f5] p-4 text-sm text-[#718071]">
                                    Aún no hay lecturas disponibles para este sensor.
                                </div>
                            @endif

                            @if ($alertasActivas->isNotEmpty())
                                <div class="mt-5 space-y-3">
                                    <h4 class="font-bold text-[#8a5700]">Alertas activas</h4>
                                    @foreach ($alertasActivas as $alerta)
                                        <article class="rounded-2xl border border-[#f1d7a9] bg-[#fff9ed] p-4">
                                            <p class="text-sm font-semibold text-[#684b1b]">
                                                {{ $alerta->mensaje ?? 'Esta planta requiere atención (' . str_replace('_', ' ', $alerta->tipo) . ').' }}
                                            </p>
                                            <p class="mt-1 text-xs text-[#8c7650]">
                                                {{ $alerta->created_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}
                                            </p>
                                        </article>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

            @else
                <div class="flex flex-col items-center gap-5 rounded-3xl border border-[#e4ebdb] bg-white p-7 text-center shadow-sm sm:p-10">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-[#eff6e8]">
                        <img class="h-9 w-9" src="{{ asset('images/plantas.png') }}" alt="">
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-[#0f3c2b]">Aún no tienes sensores asignados</h3>
                        <p class="mx-auto mt-2 max-w-xl text-sm leading-6 text-[#718071]">
                            Cuando se asigne un sensor a una de tus plantas, aparecerá aquí junto con sus lecturas disponibles.
                        </p>
                    </div>
                    <a href="{{ route('catalogo.plantas.index') }}"
                       class="inline-flex min-h-12 items-center justify-center rounded-xl bg-[#7cb22b] px-6 py-3 font-bold text-white shadow-md shadow-[#7cb22b]/20 transition hover:-translate-y-0.5 hover:bg-[#6eab26]">
                        Explorar plantas disponibles
                    </a>
                </div>
            @endif
        </section>

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

        @if(count($recomendaciones) > 0)
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
                @foreach($recomendaciones as $planta)
                    <article class="group overflow-hidden rounded-3xl border border-[#e8ede4] bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl">
                        <div class="relative flex h-56 items-center justify-center overflow-hidden bg-gradient-to-br from-[#f3f8ee] to-[#eaf2e3] p-6">
                            <span class="absolute left-4 top-4 rounded-full border border-white/80 bg-white/85 px-3 py-1 text-xs font-bold text-[#52752d] shadow-sm">Recomendada</span>
                            @if ($planta->imagen)
                                <img src="{{ asset('storage/' . $planta->imagen) }}"
                                     alt="{{ $planta->nombre }}"
                                     class="h-full max-h-44 w-3/4 object-contain transition duration-500 group-hover:scale-105">
                            @else
                                <img src="{{ asset('images/hoja_verde.png') }}"
                                     alt=""
                                     class="h-28 w-28 object-contain opacity-70">
                            @endif
                        </div>
                        <div class="p-5 sm:p-6">
                            <h3 class="mb-2 text-xl font-bold text-[#0f3c2b]">{{ $planta->nombre }}</h3>
                            <p class="mb-5 line-clamp-2 min-h-10 text-sm leading-5 text-[#718071]">
                                {{ $planta->descripcion ?? 'Descripción no disponible' }}
                            </p>
                            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-[#edf1e9] pt-4">
                                <span class="rounded-full bg-[#eff6e8] px-3 py-1.5 text-xs font-bold text-[#52752d]">
                                    Compatibilidad {{ number_format($planta->compatibilidad_porcentaje, 1) }}%
                                </span>
                                <a href="{{ route('catalogo.plantas.index') }}"
                                   class="font-semibold text-[#608d2e] transition hover:text-[#416d1e]">
                                    Explorar catálogo <span aria-hidden="true">→</span>
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="rounded-3xl border border-dashed border-[#ccd9c1] bg-white px-6 py-10 text-center">
                <p class="text-base font-semibold text-[#34533b]">Todavía no hay recomendaciones disponibles.</p>
                <p class="mt-2 text-sm text-[#718071]">Completa tus preferencias para descubrir plantas adecuadas para ti.</p>
                <a href="{{ route('perfil.edit') }}"
                   class="mt-5 inline-flex min-h-11 items-center justify-center rounded-xl bg-[#7cb22b] px-5 py-2.5 font-bold text-white transition hover:bg-[#6eab26]">
                    Completar mi perfil
                </a>
            </div>
        @endif
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
            <a href="{{ route('perfil.edit') }}"
               class="group flex min-h-48 flex-col rounded-2xl border border-[#e5ebdf] bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-[#cdddbe] hover:shadow-lg sm:p-6">
                <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-[#eff6e8] transition group-hover:bg-[#e4f0d8]">
                    <img class="h-6 w-6" src="{{ asset('images/editar.png') }}" alt="">
                </div>
                <span class="font-bold text-[#0f3c2b]">Editar perfil</span>
                <span class="mt-1 text-sm leading-5 text-[#718071]">Actualizar tus preferencias</span>
                <span class="mt-auto pt-4 text-sm font-semibold text-[#608d2e]">Abrir perfil <span aria-hidden="true">→</span></span>
            </a>

            <!-- Ver catálogo -->
            <a href="{{ route('catalogo.plantas.index') }}"
               class="group flex min-h-48 flex-col rounded-2xl border border-[#e5ebdf] bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-[#cdddbe] hover:shadow-lg sm:p-6">
                <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-[#eff6e8] transition group-hover:bg-[#e4f0d8]">
                    <img class="h-6 w-6" src="{{ asset('images/plantas.png') }}" alt="">
                </div>
                <span class="font-bold text-[#0f3c2b]">Ver catálogo</span>
                <span class="mt-1 text-sm leading-5 text-[#718071]">Explora nuestras plantas</span>
                <span class="mt-auto pt-4 text-sm font-semibold text-[#608d2e]">Explorar <span aria-hidden="true">→</span></span>
            </a>

            <!-- Mis sensores -->
            <a href="#mis-sensores"
               class="group flex min-h-48 flex-col rounded-2xl border border-[#e5ebdf] bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-[#cdddbe] hover:shadow-lg sm:p-6">
                <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-[#eff6e8] transition group-hover:bg-[#e4f0d8]">
                    <img class="h-6 w-6" src="{{ asset('images/sensores.png') }}" alt="">
                </div>
                <span class="font-bold text-[#0f3c2b]">Mis sensores</span>
                <span class="mt-1 text-sm leading-5 text-[#718071]">Consulta los sensores asignados a tus plantas</span>
                <span class="mt-auto pt-4 text-sm font-semibold text-[#608d2e]">Ver sensores <span aria-hidden="true">→</span></span>
            </a>

            <!-- Historial y Alertas -->
            <a href="{{ route('historial-alertas') }}"
               class="group flex min-h-48 flex-col rounded-2xl border border-[#e5ebdf] bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-[#cdddbe] hover:shadow-lg sm:p-6">
                <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-[#eff6e8] transition group-hover:bg-[#e4f0d8]">
                    <img class="h-6 w-6" src="{{ asset('images/historial.png') }}" alt="">
                </div>
                <span class="font-bold text-[#0f3c2b]">Historial y alertas</span>
                <span class="mt-1 text-sm leading-5 text-[#718071]">Consulta lecturas y notificaciones</span>
                <span class="mt-auto pt-4 text-sm font-semibold text-[#608d2e]">Ver historial <span aria-hidden="true">→</span></span>
            </a>
        </div>
    </section>
</main>
@endsection