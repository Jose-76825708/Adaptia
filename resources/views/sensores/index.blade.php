<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    @vite('resources/css/app.css')
    <title>Sensores</title>
</head>

<body class="flex font-sans h-screen">

    @include('partials.sidebar')

    <main class="flex flex-5 flex-col p-25 bg-[#fbfbfb] h-full gap-4">
        <section class="flex items-center justify-between">
            <div class="flex-4">
                <h1 class="text-[#103928] font-bold text-[2.5em]">Gestión de Sensores</h1>
                <p class="text-[#8b8d8f] text-[1em]">Administra los sensores registrados en Adaptia.</p>
            </div>
            <div class="flex-1">
                <a href="{{ route('sensores.create') }}"
                    class="flex items-center justify-between py-3 px-6 bg-[#629f22] text-[#fbfbfb] rounded-[10px] gap-3 hover:scale-110 transition duration-300">
                    <img class="w-[10%] h-auto" src="{{ asset('images/anadir.png') }}" alt="Añadir">
                    Crear Sensor
                </a>
            </div>
        </section>

        @if (session('success'))
            <div class="p-4 bg-[#d4edda] text-[#155724] border border-[#c3e6cb] rounded-[10px]" role="status">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 bg-[#f8d7da] text-[#721c24] border border-[#f5c6cb] rounded-[10px]" role="alert">
                <p class="font-bold">No se pudo completar la operación:</p>
                <ul class="list-disc pl-6">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="flex p-8 bg-[#fefdfe] text-[#304e42] rounded-[10px] shadow-xl">
            <div class="w-full overflow-x-auto">
                <table class="w-full min-w-[640px]">
                    <thead class="bg-[#f3f5f3] text-left">
                        <tr>
                            <th class="text-center rounded-tl-[20px] w-20 p-4">Id</th>
                            <th class="p-4">Identificador Físico</th>
                            <th class="p-4">Estado</th>
                            <th class="rounded-tr-[20px] p-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sensores as $sensor)
                            <tr class="border-t-2 border-[#c2c4c7]">
                                <td class="p-4 font-bold text-center">{{ $sensor->id }}</td>
                                <td class="p-4">{{ $sensor->identificador_fisico }}</td>
                                <td class="p-4">
                                    @if ($sensor->estado === 'activo')
                                        <span class="px-3 py-1 bg-[#d4edda] text-[#155724] rounded-full text-[0.9em]">Activo</span>
                                    @else
                                        <span class="px-3 py-1 bg-[#f8d7da] text-[#721c24] rounded-full text-[0.9em]">Inactivo</span>
                                    @endif
                                </td>
                                <td class="flex gap-6 p-4">
                                    <a class="flex flex-1 items-center justify-center gap-3 bg-[#f5f9f0] py-2 text-[#77a856] font-bold rounded-[10px] hover:scale-110 transition duration-300"
                                       href="{{ route('sensores.edit', $sensor->id) }}">
                                        <img class="w-[10%] h-auto" src="{{ asset('images/editar.png') }}" alt="">
                                        Editar
                                    </a>
                                    <form action="{{ route('sensores.destroy', $sensor->id) }}" method="POST" class="flex flex-1">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                onclick="return confirm('¿Estás seguro de eliminar este sensor?');"
                                                class="flex flex-1 items-center justify-center gap-3 bg-[#fdebeb] py-2 text-[#f06f73] font-bold rounded-[10px] hover:scale-110 transition duration-300">
                                            <img class="w-[10%] h-auto" src="{{ asset('images/eliminar.png') }}" alt="">
                                            Eliminar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-8 text-center text-[#8b8d8f]">
                                    No hay sensores registrados todavía.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

    </main>
</body>

</html>