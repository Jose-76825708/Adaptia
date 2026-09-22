<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    @vite('resources/css/app.css')
    <title>Crear Cuenta - Adaptia</title>
</head>

<body class="font-sans h-screen w-full overflow-hidden">

    <div class="flex flex-col lg:flex-row h-full w-full">
        <!-- Sección Izquierda: Branding/Logo -->
        <div class="hidden lg:flex w-1/2 h-full bg-[#103928] items-center justify-center relative overflow-hidden">
            <!-- Elemento decorativo de fondo para darle profundidad -->
            <div class="absolute w-[150%] h-[150%] bg-gradient-to-br from-[#1a5c3f] to-[#082016] rounded-full -top-1/4 -right-1/4 opacity-60"></div>

            <div class="relative z-10 flex flex-col items-center text-center p-12">
                <img class="w-full max-w-lg h-auto drop-shadow-2xl animate-pulse-slow" src="{{ asset('images/logotipo_blanco.png') }}" alt="Adaptia Logo Blanco">
            </div>
        </div>

        <!-- Sección Derecha: Formulario -->
        <div class="w-full lg:w-1/2 h-full flex items-center justify-center bg-[#fbfbfb] p-6">
            <div class="flex flex-col p-12 bg-[#fefdfe] rounded-[30px] shadow-2xl w-full max-w-2xl gap-10 border border-[#ecedea]">

                <div class="flex flex-col items-start text-left gap-3">
                    <h1 class="text-[#103928] font-bold text-[2.5em]">Unirse a Adaptia</h1>
                    <p class="text-[#8b8d8f] text-[1.1em]">Crea tu cuenta para empezar a gestionar tu catálogo de plantas y recibir recomendaciones personalizadas.</p>
                </div>

                <form action="{{ route('register') }}" method="POST" class="flex flex-col gap-8">
                    @csrf

                    <div class="flex flex-col gap-3">
                        <label for="name" class="text-[#304e42] font-semibold text-[1.2em]">Nombre Completo</label>
                        <input type="text" name="name" id="name"
                               class="p-4 bg-[#f3f5f3] border-2 @error('name') border-red-500 @else border-[#ecedea] @enderror rounded-[12px] outline-none focus:border-[#629f22] transition duration-300 text-[1.1em]"
                               placeholder="Ej: Jose Perez" value="{{ old('name') }}" required>
                        @error('name')
                            <span class="text-red-500 text-[1em] font-medium">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex flex-col gap-3">
                        <label for="email" class="text-[#304e42] font-semibold text-[1.2em]">Correo Electrónico</label>
                        <input type="email" name="email" id="email"
                               class="p-4 bg-[#f3f5f3] border-2 @error('email') border-red-500 @else border-[#ecedea] @enderror rounded-[12px] outline-none focus:border-[#629f22] transition duration-300 text-[1.1em]"
                               placeholder="ejemplo@correo.com" value="{{ old('email') }}" required>
                        @error('email')
                            <span class="text-red-500 text-[1em] font-medium">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="flex flex-col gap-3">
                            <label for="password" class="text-[#304e42] font-semibold text-[1.2em]">Contraseña</label>
                            <input type="password" name="password" id="password"
                                   class="p-4 bg-[#f3f5f3] border-2 @error('password') border-red-500 @else border-[#ecedea] @enderror rounded-[12px] outline-none focus:border-[#629f22] transition duration-300 text-[1.1em]"
                                   placeholder="********" required>
                        </div>
                        <div class="flex flex-col gap-3">
                            <label for="password_confirmation" class="text-[#304e42] font-semibold text-[1.2em]">Confirmar</label>
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                   class="p-4 bg-[#f3f5f3] border-2 border-[#ecedea] rounded-[12px] outline-none focus:border-[#629f22] transition duration-300 text-[1.1em]"
                                   placeholder="********" required>
                        </div>
                    </div>
                    @error('password')
                        <span class="text-red-500 text-[1em] font-medium">{{ $message }}</span>
                    @enderror

                    <div class="flex flex-col gap-3">
                        <label for="rol" class="text-[#304e42] font-semibold text-[1.2em]">Tipo de Usuario (Rol)</label>
                        <select name="rol" id="rol"
                                class="p-4 bg-[#f3f5f3] border-2 @error('rol') border-red-500 @else border-[#ecedea] @enderror rounded-[12px] outline-none focus:border-[#629f22] transition duration-300 text-[1.1em]" required>
                            <option value="" disabled selected>Seleccione su rol...</option>
                            <option value="cliente" {{ old('rol') == 'cliente' ? 'selected' : '' }}>Cliente</option>
                            <option value="vendedor" {{ old('rol') == 'vendedor' ? 'selected' : '' }}>Vendedor</option>
                            <option value="administrador" {{ old('rol') == 'administrador' ? 'selected' : '' }}>Administrador</option>
                        </select>
                        @error('rol')
                            <span class="text-red-500 text-[1em] font-medium">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex justify-center mt-4">
                        <button type="submit"
                                class="w-full flex items-center justify-center py-4 px-8 bg-[#103928] text-[#fbfbfb] rounded-[12px] gap-3 hover:scale-[1.02] transition duration-300 font-bold text-[1.2em] shadow-lg hover:bg-[#0a261b]">
                            Crear Cuenta
                        </button>
                    </div>
                </form>

                <div class="text-center mt-4">
                    <p class="text-[#8b8d8f] text-[1em]">¿Ya tienes una cuenta? <a href="{{ route('login') }}" class="text-[#103928] font-bold hover:underline">Inicia sesión aquí</a></p>
                </div>
            </div>
        </div>
    </div>

</body>

</html>
