<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    @vite('resources/css/app.css')
    <title>Ventas</title>
</head>

<body class="flex font-sans min-h-screen">

    @include('partials.sidebar')

    <main class="flex flex-5 flex-col p-25 bg-[#fbfbfb] min-h-screen gap-4 overflow-y-auto">
        <section class="flex items-center justify-between gap-6">
            <div>
                <h1 class="text-[#103928] font-bold text-[2.5em]">Ventas</h1>
                <p class="text-[#8b8d8f] text-[1em]">Consulta y registra las ventas realizadas.</p>
            </div>
            <a href="{{ route('ventas.create') }}"
               class="flex shrink-0 items-center justify-between py-3 px-6 bg-[#629f22] text-[#fbfbfb] rounded-[10px] gap-3 hover:scale-105 transition duration-300">
                <img class="w-5 h-auto" src="{{ asset('images/anadir.png') }}" alt="">
                Registrar Venta
            </a>
        </section>

        @if (session('success'))
            <div class="p-4 bg-[#d4edda] text-[#155724] border border-[#c3e6cb] rounded-[10px]" role="status">
                {{ session('success') }}
            </div>
        @endif

        @if (session('stock_warning'))
            <div class="p-4 bg-[#fff3cd] text-[#856404] border border-[#ffeeba] rounded-[10px]" role="alert">
                {{ session('stock_warning') }}
            </div>
        @endif

        <section class="flex p-8 bg-[#fefdfe] text-[#304e42] rounded-[10px] shadow-xl overflow-x-auto">
            @if ($ventas->isEmpty())
                <p class="w-full py-8 text-center text-[#8b8d8f]">
                    Todavía no hay ventas registradas.
                </p>
            @else
                <table class="w-full min-w-[900px]">
                    <thead class="bg-[#f3f5f3] text-left">
                        <tr>
                            <th class="text-center rounded-tl-[20px] p-4">Fecha</th>
                            <th class="p-4">Cliente</th>
                            <th class="p-4">Vendedor</th>
                            <th class="p-4">Planta</th>
                            <th class="text-center p-4">Cantidad</th>
                            <th class="rounded-tr-[20px] p-4">Sensores</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ventas as $venta)
                            <tr class="border-t-2 border-[#c2c4c7]">
                                <td class="p-4 text-center">{{ $venta->fecha->format('d/m/Y H:i') }}</td>
                                <td class="p-4">{{ $venta->cliente->name }}</td>
                                <td class="p-4">{{ $venta->vendedor->name }}</td>
                                <td class="p-4">{{ $venta->planta->nombre }}</td>
                                <td class="p-4 text-center font-bold">{{ $venta->cantidad }}</td>
                                <td class="p-4">
                                    @forelse ($venta->plantasVendidas as $unidad)
                                        <div class="mb-1 last:mb-0">
                                            <span class="font-semibold">Unidad #{{ $unidad->id }}:</span>
                                            @if ($unidad->sensor)
                                                <span>{{ $unidad->sensor->identificador_fisico }}</span>
                                            @else
                                                <span class="text-[#856404]">Pendiente de asignación</span>
                                            @endif
                                        </div>
                                    @empty
                                        <span class="text-[#8b8d8f]">Sin unidades individuales registradas</span>
                                    @endforelse

                                    @php
                                        $unidadesFaltantes = max(0, $venta->cantidad - $venta->plantasVendidas->count());
                                    @endphp
                                    @if ($unidadesFaltantes > 0)
                                        <div class="mt-2 text-sm text-[#856404]">
                                            {{ $unidadesFaltantes }}
                                            {{ $unidadesFaltantes === 1 ? 'unidad sin registro individual' : 'unidades sin registro individual' }}
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </section>
    </main>
</body>

</html>
