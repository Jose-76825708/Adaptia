<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    @vite('resources/css/app.css')
    <title>Registrar Venta</title>
</head>

<body class="flex font-sans min-h-screen">

    @include('partials.sidebar')

    <main class="flex flex-5 flex-col px-25 py-10 bg-[#fbfbfb] min-h-screen gap-4 overflow-y-auto">
        <section>
            <a class="flex items-center text-[#0c251c] font-bold text-[0.8em]" href="{{ route('ventas.index') }}">
                <img class="w-[3%] h-auto" src="{{ asset('images/regreso-flecha.png') }}" alt="">
                Volver a Ventas
            </a>
        </section>

        <section class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-[#103928] font-bold text-[2.5em]">Registrar Venta</h1>
                <p class="text-[#8b8d8f] text-[1em]">Selecciona el cliente, la planta y la cantidad vendida.</p>
            </div>
        </section>

        @if ($errors->any())
            <div class="p-4 bg-[#f8d7da] text-[#721c24] border border-[#f5c6cb] rounded-[10px]" role="alert">
                <p class="font-bold">No se pudo registrar la venta. Revisa los datos indicados.</p>
            </div>
        @endif

        @if ($clientes->isEmpty())
            <output class="block p-4 bg-[#fff3cd] text-[#856404] border border-[#ffeeba] rounded-[10px]">
                No hay clientes registrados para asociar a la venta.
            </output>
        @endif

        @if ($plantas->isEmpty())
            <output class="block p-4 bg-[#fff3cd] text-[#856404] border border-[#ffeeba] rounded-[10px]">
                No hay plantas con stock disponible para vender.
            </output>
        @endif

        <section class="flex justify-center pb-10">
            <div class="flex flex-col p-10 bg-[#fefdfe] rounded-[20px] shadow-xl w-full gap-8 border border-[#ecedea]">
                <form action="{{ route('ventas.store') }}" method="POST" class="flex flex-col gap-10">
                    @csrf

                    <div class="flex flex-col gap-6">
                        <div class="flex items-center gap-3 border-b border-[#ecedea] pb-2">
                            <div class="w-2 h-6 bg-[#629f22] rounded-full"></div>
                            <h2 class="text-[#103928] font-bold text-[1.3em]">Datos de la Venta</h2>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="flex flex-col gap-2">
                                <label for="user_id" class="text-[#304e42] font-semibold text-[1.1em]">Cliente</label>
                                <select name="user_id" id="user_id"
                                        class="p-4 bg-[#f3f5f3] border-2 @error('user_id') border-red-500 @else border-[#ecedea] @enderror rounded-[10px] outline-none focus:border-[#629f22] transition duration-300"
                                        @disabled($clientes->isEmpty()) required>
                                    <option value="" disabled @selected(old('user_id') === null)>Seleccione un cliente...</option>
                                    @foreach ($clientes as $cliente)
                                        <option value="{{ $cliente->id }}" @selected(old('user_id') == $cliente->id)>
                                            {{ $cliente->name }} ({{ $cliente->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('user_id')
                                    <span class="text-red-500 text-[0.9em] font-medium">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="planta_id" class="text-[#304e42] font-semibold text-[1.1em]">Planta</label>
                                <select name="planta_id" id="planta_id"
                                        class="p-4 bg-[#f3f5f3] border-2 @error('planta_id') border-red-500 @else border-[#ecedea] @enderror rounded-[10px] outline-none focus:border-[#629f22] transition duration-300"
                                        @disabled($plantas->isEmpty()) required>
                                    <option value="" disabled @selected(old('planta_id') === null)>Seleccione una planta...</option>
                                    @foreach ($plantas as $planta)
                                        <option value="{{ $planta->id }}" @selected(old('planta_id') == $planta->id)>
                                            {{ $planta->nombre }} (Stock disponible: {{ $planta->stock_actual }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('planta_id')
                                    <span class="text-red-500 text-[0.9em] font-medium">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="cantidad" class="text-[#304e42] font-semibold text-[1.1em]">Cantidad</label>
                                <input type="number" name="cantidad" id="cantidad" min="1"
                                       class="p-4 bg-[#f3f5f3] border-2 @error('cantidad') border-red-500 @else border-[#ecedea] @enderror rounded-[10px] outline-none focus:border-[#629f22] transition duration-300"
                                       placeholder="Ej: 2" value="{{ old('cantidad') }}" required>
                                @error('cantidad')
                                    <span class="text-red-500 text-[0.9em] font-medium">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end mt-4">
                        <button type="submit"
                                class="flex items-center justify-between py-4 px-10 bg-[#629f22] text-[#fbfbfb] rounded-[10px] gap-3 hover:scale-105 transition duration-300 font-bold text-[1.1em]"
                                @disabled($clientes->isEmpty() || $plantas->isEmpty())>
                            <img class="w-[1.2em] h-auto" src="{{ asset('images/anadir.png') }}" alt="">
                            Registrar Venta
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </main>
</body>

</html>
