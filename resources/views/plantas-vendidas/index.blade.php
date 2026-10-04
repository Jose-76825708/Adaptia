<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    @vite('resources/css/app.css')
    <title>Asignar Sensores</title>
</head>

<body class="flex font-sans min-h-screen">

    @include('partials.sidebar')

    <main class="flex flex-5 flex-col p-25 bg-[#fbfbfb] min-h-screen gap-4 overflow-y-auto">
        <section>
            <h1 class="text-[#103928] font-bold text-[2.5em]">Asignar Sensores</h1>
            <p class="text-[#8b8d8f] text-[1em]">
                Asocia un sensor activo y disponible a cada unidad comprada por un cliente.
            </p>
        </section>

        @if (session('success'))
            <div class="p-4 bg-[#d4edda] text-[#155724] border border-[#c3e6cb] rounded-[10px]" role="status">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 bg-[#f8d7da] text-[#721c24] border border-[#f5c6cb] rounded-[10px]" role="alert">
                <p class="font-bold">No se pudo asignar el sensor:</p>
                <ul class="list-disc pl-6">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="flex p-8 bg-[#fefdfe] text-[#304e42] rounded-[10px] shadow-xl overflow-x-auto">
            @if ($unidadesPendientes->isEmpty())
                <p class="w-full py-8 text-center text-[#8b8d8f]">
                    No hay unidades pendientes de asignación de sensor.
                </p>
            @else
                <table class="w-full min-w-[850px]">
                    <thead class="bg-[#f3f5f3] text-left">
                        <tr>
                            <th class="text-center rounded-tl-[20px] p-4">Unidad</th>
                            <th class="p-4">Planta</th>
                            <th class="p-4">Cliente</th>
                            <th class="p-4">Fecha de venta</th>
                            <th class="rounded-tr-[20px] p-4">Asignación</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($unidadesPendientes as $unidad)
                            <tr class="border-t-2 border-[#c2c4c7]">
                                <td class="p-4 text-center font-bold">#{{ $unidad->id }}</td>
                                <td class="p-4">{{ $unidad->venta->planta->nombre }}</td>
                                <td class="p-4">
                                    <div class="font-semibold">{{ $unidad->cliente->name }}</div>
                                    <div class="text-sm text-[#8b8d8f]">{{ $unidad->cliente->email }}</div>
                                </td>
                                <td class="p-4">{{ $unidad->venta->fecha->format('d/m/Y H:i') }}</td>
                                <td class="p-4">
                                    @if ($sensoresDisponibles->isEmpty())
                                        <span class="text-sm text-[#856404]">
                                            No hay sensores activos disponibles.
                                        </span>
                                    @else
                                        <form action="{{ route('plantas-vendidas.asignar-sensor', $unidad) }}"
                                              method="POST" class="flex min-w-[260px] flex-col gap-2">
                                            @csrf
                                            <select name="sensor_id"
                                                    class="p-3 bg-[#f3f5f3] border border-[#ecedea] rounded-[10px] outline-none focus:border-[#629f22]"
                                                    aria-label="Sensor para la unidad {{ $unidad->id }}" required>
                                                <option value="" disabled
                                                    @selected(old('sensor_id') === null)>
                                                    Seleccione un sensor...
                                                </option>
                                                @foreach ($sensoresDisponibles as $sensor)
                                                    <option value="{{ $sensor->id }}"
                                                        @selected(old('sensor_id') == $sensor->id)>
                                                        {{ $sensor->identificador_fisico }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <button type="submit"
                                                    class="py-2 px-4 bg-[#629f22] text-white rounded-[10px] font-bold hover:bg-[#568f1d] transition duration-300">
                                                Asignar sensor
                                            </button>
                                        </form>
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
