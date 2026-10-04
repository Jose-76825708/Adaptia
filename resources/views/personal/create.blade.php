<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    @vite('resources/css/app.css')
    <title>Crear cuenta de personal</title>
</head>

<body class="flex font-sans min-h-screen">

    @include('partials.sidebar')

    <main class="flex flex-5 flex-col px-25 py-10 bg-[#fbfbfb] min-h-screen gap-4">
        <section class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-[#103928] font-bold text-[2.5em]">Crear cuenta de personal</h1>
                <p class="text-[#8b8d8f] text-[1em]">
                    Registra una cuenta de vendedor o administrador. Esta opción solo está disponible para administradores.
                </p>
            </div>
        </section>

        @if (session('success'))
            <div class="p-4 bg-[#d4edda] text-[#155724] border border-[#c3e6cb] rounded-[10px]" role="status">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 bg-[#f8d7da] text-[#721c24] border border-[#f5c6cb] rounded-[10px]" role="alert">
                <p class="font-bold">No se pudo crear la cuenta:</p>
                <ul class="list-disc pl-6">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="flex justify-center">
            <div class="flex flex-col p-10 bg-[#fefdfe] rounded-[20px] shadow-xl w-full max-w-2xl gap-8 border border-[#ecedea]">
                <form action="{{ route('personal.store') }}" method="POST" class="flex flex-col gap-6">
                    @csrf

                    <div class="flex flex-col gap-2">
                        <label for="name" class="text-[#304e42] font-semibold text-[1.1em]">Nombre completo</label>
                        <input type="text" name="name" id="name"
                               class="p-4 bg-[#f3f5f3] border-2 @error('name') border-red-500 @else border-[#ecedea] @enderror rounded-[10px] outline-none focus:border-[#629f22] transition duration-300"
                               value="{{ old('name') }}" required>
                        @error('name')
                            <span class="text-red-500 text-[0.9em] font-medium">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex flex-col gap-2">
                        <label for="email" class="text-[#304e42] font-semibold text-[1.1em]">Correo electrónico</label>
                        <input type="email" name="email" id="email"
                               class="p-4 bg-[#f3f5f3] border-2 @error('email') border-red-500 @else border-[#ecedea] @enderror rounded-[10px] outline-none focus:border-[#629f22] transition duration-300"
                               value="{{ old('email') }}" required>
                        @error('email')
                            <span class="text-red-500 text-[0.9em] font-medium">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="flex flex-col gap-2">
                            <label for="password" class="text-[#304e42] font-semibold text-[1.1em]">Contraseña</label>
                            <input type="password" name="password" id="password"
                                   class="p-4 bg-[#f3f5f3] border-2 @error('password') border-red-500 @else border-[#ecedea] @enderror rounded-[10px] outline-none focus:border-[#629f22] transition duration-300"
                                   required>
                        </div>
                        <div class="flex flex-col gap-2">
                            <label for="password_confirmation" class="text-[#304e42] font-semibold text-[1.1em]">Confirmar contraseña</label>
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                   class="p-4 bg-[#f3f5f3] border-2 border-[#ecedea] rounded-[10px] outline-none focus:border-[#629f22] transition duration-300"
                                   required>
                        </div>
                    </div>
                    @error('password')
                        <span class="text-red-500 text-[0.9em] font-medium">{{ $message }}</span>
                    @enderror

                    <div class="flex flex-col gap-2">
                        <label for="rol" class="text-[#304e42] font-semibold text-[1.1em]">Rol de la cuenta</label>
                        <select name="rol" id="rol"
                                class="p-4 bg-[#f3f5f3] border-2 @error('rol') border-red-500 @else border-[#ecedea] @enderror rounded-[10px] outline-none focus:border-[#629f22] transition duration-300"
                                required>
                            <option value="" disabled @selected(old('rol') === null)>Seleccione un rol...</option>
                            <option value="vendedor" @selected(old('rol') === 'vendedor')>Vendedor</option>
                            <option value="administrador" @selected(old('rol') === 'administrador')>Administrador</option>
                        </select>
                        @error('rol')
                            <span class="text-red-500 text-[0.9em] font-medium">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex justify-end mt-4">
                        <button type="submit"
                                class="py-3 px-8 bg-[#629f22] text-white rounded-[10px] font-bold hover:bg-[#568f1d] transition duration-300">
                            Crear cuenta
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </main>
</body>

</html>
