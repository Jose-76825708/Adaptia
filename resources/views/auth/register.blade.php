<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    @vite('resources/css/app.css')
    <title>Crear Cuenta - Adaptia</title>
</head>

<body class="flex font-sans h-screen bg-[#fbfbfb] items-center justify-center">

    <div class="flex flex-col p-10 bg-[#fefdfe] rounded-[20px] shadow-xl w-full max-w-md gap-8 border border-[#ecedea]">

        <div class="flex flex-col items-center text-center gap-2">
            <img class="w-24 h-auto mb-4" src="{{ asset('images/logo.png') }}" alt="Adaptia Logo">
            <h1 class="text-[#103928] font-bold text-[2em]">Unirse a Adaptia</h1>
            <p class="text-[#8b8d8f] text-[1em]">Crea tu cuenta para empezar a gestionar tu catálogo de plantas.</p>
        </div>

        <form action="{{ route('register') }}" method="POST" class="flex flex-col gap-6">
            @csrf

            <div class="flex flex-col gap-2">
                <label for="name" class="text-[#304e42] font-semibold text-[1.1em]">Nombre Completo</label>
                <input type="text" name="name" id="name"
                       class="p-4 bg-[#f3f5f3] border-2 @error('name') border-red-500 @else border-[#ecedea] @enderror rounded-[10px] outline-none focus:border-[#629f22] transition duration-300"
                       placeholder="Ej: Jose Perez" value="{{ old('name') }}" required>
                @error('name')
                    <span class="text-red-500 text-[0.9em] font-medium">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex flex-col gap-2">
                <label for="email" class="text-[#304e42] font-semibold text-[1.1em]">Correo Electrónico</label>
                <input type="email" name="email" id="email"
                       class="p-4 bg-[#f3f5f3] border-2 @error('email') border-red-500 @else border-[#ecedea] @enderror rounded-[10px] outline-none focus:border-[#629f22] transition duration-300"
                       placeholder="ejemplo@correo.com" value="{{ old('email') }}" required>
                @error('email')
                    <span class="text-red-500 text-[0.9em] font-medium">{{ $message }}</span>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="flex flex-col gap-2">
                    <label for="password" class="text-[#304e42] font-semibold text-[1.1em]">Contraseña</label>
                    <input type="password" name="password" id="password"
                           class="p-4 bg-[#f3f5f3] border-2 @error('password') border-red-500 @else border-[#ecedea] @enderror rounded-[10px] outline-none focus:border-[#629f22] transition duration-300"
                           placeholder="********" required>
                </div>
                <div class="flex flex-col gap-2">
                    <label for="password_confirmation" class="text-[#304e42] font-semibold text-[1.1em]">Confirmar</label>
                    <input type="password" name="password_confirmation" id="password_confirmation"
                           class="p-4 bg-[#f3f5f3] border-2 border-[#ecedea] rounded-[10px] outline-none focus:border-[#629f22] transition duration-300"
                           placeholder="********" required>
                </div>
            </div>
            @error('password')
                <span class="text-red-500 text-[0.9em] font-medium">{{ $message }}</span>
            @enderror

            <div class="flex flex-col gap-2">
                <label for="rol" class="text-[#304e42] font-semibold text-[1.1em]">Tipo de Usuario (Rol)</label>
                <select name="rol" id="rol"
                        class="p-4 bg-[#f3f5f3] border-2 @error('rol') border-red-500 @else border-[#ecedea] @enderror rounded-[10px] outline-none focus:border-[#629f22] transition duration-300" required>
                    <option value="" disabled selected>Seleccione su rol...</option>
                    <option value="cliente" {{ old('rol') == 'cliente' ? 'selected' : '' }}>Cliente</option>
                    <option value="vendedor" {{ old('rol') == 'vendedor' ? 'selected' : '' }}>Vendedor</option>
                    <option value="administrador" {{ old('rol') == 'administrador' ? 'selected' : '' }}>Administrador</option>
                </select>
                @error('rol')
                    <span class="text-red-500 text-[0.9em] font-medium">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex justify-center mt-4">
                <button type="submit"
                        class="w-full flex items-center justify-center py-3 px-8 bg-[#629f22] text-[#fbfbfb] rounded-[10px] gap-3 hover:scale-105 transition duration-300 font-bold text-[1.1em]">
                    Crear Cuenta
                </button>
            </div>
        </form>

        <div class="text-center mt-4">
            <p class="text-[#8b8d8f] text-[0.9em]">¿Ya tienes una cuenta? <a href="{{ route('login') }}" class="text-[#629f22] font-bold hover:underline">Inicia sesión aquí</a></p>
        </div>

    </div>

</body>

</html>
