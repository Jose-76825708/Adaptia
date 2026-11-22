@php
    $rol = auth()->user()->rol;
    $navegacionGeneral = [
        ['nombre' => 'Inicio', 'ruta' => 'home', 'rutas' => ['home'], 'icono' => 'IN'],
        ['nombre' => 'Asistente interno', 'ruta' => 'asistente.index', 'rutas' => ['asistente.*'], 'icono' => 'IA'],
    ];
    $navegacionOperativa = [
        ['nombre' => 'Plantas', 'ruta' => 'plantas.index', 'rutas' => ['plantas.*'], 'icono' => 'PL'],
        ['nombre' => 'Tipos de plantas', 'ruta' => 'tipoPlantas.index', 'rutas' => ['tipoPlantas.*'], 'icono' => 'TP'],
        ['nombre' => 'Inventario', 'ruta' => 'movimientos-inventario.index', 'rutas' => ['movimientos-inventario.*'], 'icono' => 'IV'],
        ['nombre' => 'Sensores', 'ruta' => 'sensores.index', 'rutas' => ['sensores.*'], 'icono' => 'IoT'],
    ];

    if ($rol === 'administrador') {
        $navegacionOperativa[] = ['nombre' => 'Planeación comercial', 'ruta' => 'planeacion-comercial.campanas.index', 'rutas' => ['planeacion-comercial.*'], 'icono' => 'PC'];
        $navegacionOperativa[] = ['nombre' => 'Casos de mantenimiento', 'ruta' => 'mantenimiento.casos.index', 'rutas' => ['mantenimiento.casos.*'], 'icono' => 'MA'];
        $navegacionOperativa[] = ['nombre' => 'Personal', 'ruta' => 'personal.create', 'rutas' => ['personal.*'], 'icono' => 'PE'];
    } elseif ($rol === 'vendedor') {
        $navegacionOperativa[] = ['nombre' => 'Planeación comercial', 'ruta' => 'planeacion-comercial.campanas.index', 'rutas' => ['planeacion-comercial.*'], 'icono' => 'PC'];
        $navegacionOperativa[] = ['nombre' => 'Ventas', 'ruta' => 'ventas.index', 'rutas' => ['ventas.*'], 'icono' => 'VE'];
        $navegacionOperativa[] = ['nombre' => 'Asignar sensores', 'ruta' => 'plantas-vendidas.index', 'rutas' => ['plantas-vendidas.*'], 'icono' => 'AS'];
    }

    $enlaceActivo = fn (array $rutas) => collect($rutas)->contains(fn (string $ruta) => request()->routeIs($ruta));
@endphp

<aside class="sticky top-0 flex h-screen w-20 shrink-0 flex-col border-r border-white/10 bg-[#092f23] text-white lg:w-64">
    <a href="{{ route('home') }}" class="flex h-20 items-center justify-center border-b border-white/10 px-3 lg:justify-start lg:px-6" aria-label="Adaptia, inicio">
        <img src="{{ asset('images/logotipo_blanco.png') }}" class="h-auto w-11 lg:w-44" alt="Adaptia">
    </a>

    <div class="flex-1 space-y-7 overflow-y-auto px-2 py-6 lg:px-3">
        <nav aria-label="Navegación principal">
            <p class="mb-2 hidden px-3 text-[10px] font-bold uppercase tracking-[0.18em] text-white/40 lg:block">General</p>
            <ul class="space-y-1">
                @foreach ($navegacionGeneral as $enlace)
                    @php $activo = $enlaceActivo($enlace['rutas']); @endphp
                    <li>
                        <a href="{{ route($enlace['ruta']) }}" title="{{ $enlace['nombre'] }}" aria-label="{{ $enlace['nombre'] }}" @if ($activo) aria-current="page" @endif
                           class="group flex min-h-11 items-center justify-center gap-3 rounded-xl px-2 text-sm font-medium transition lg:justify-start lg:px-3 {{ $activo ? 'bg-[#2e6945] text-white shadow-sm' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/10 text-[10px] font-bold tracking-wide text-[#d7e9c6] group-hover:bg-white/15">{{ $enlace['icono'] }}</span>
                            <span class="hidden truncate lg:block">{{ $enlace['nombre'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <nav aria-label="Módulos de trabajo">
            <p class="mb-2 hidden px-3 text-[10px] font-bold uppercase tracking-[0.18em] text-white/40 lg:block">Operación</p>
            <ul class="space-y-1">
                @foreach ($navegacionOperativa as $enlace)
                    @php $activo = $enlaceActivo($enlace['rutas']); @endphp
                    <li>
                        <a href="{{ route($enlace['ruta']) }}" title="{{ $enlace['nombre'] }}" aria-label="{{ $enlace['nombre'] }}" @if ($activo) aria-current="page" @endif
                           class="group flex min-h-11 items-center justify-center gap-3 rounded-xl px-2 text-sm font-medium transition lg:justify-start lg:px-3 {{ $activo ? 'bg-[#2e6945] text-white shadow-sm' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/10 text-[10px] font-bold tracking-wide text-[#d7e9c6] group-hover:bg-white/15">{{ $enlace['icono'] }}</span>
                            <span class="hidden truncate lg:block">{{ $enlace['nombre'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>
    </div>

    <div class="border-t border-white/10 p-2 lg:p-4">
        <div class="mb-3 hidden min-w-0 px-2 lg:block">
            <p class="truncate text-sm font-semibold text-white">{{ auth()->user()->name }}</p>
            <p class="truncate text-xs capitalize text-white/50">{{ $rol }}</p>
        </div>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" title="Cerrar sesión" class="flex w-full items-center justify-center gap-3 rounded-xl bg-white/5 px-3 py-3 text-sm font-medium text-white/75 transition hover:bg-white/10 hover:text-white lg:justify-start">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/10 text-xs font-bold">↪</span>
                <span class="hidden lg:block">Cerrar sesión</span>
            </button>
        </form>
    </div>
</aside>
