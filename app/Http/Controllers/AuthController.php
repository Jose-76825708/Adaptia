<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\PerfilCliente;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Muestra la vista del formulario de login.
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Procesa la solicitud de inicio de sesión.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // Aquí redirigiremos al home por ahora.
            // Más adelante implementaremos la redirección según el rol.
            return redirect()->intended('home');
        }

        return back()->withErrors([
            'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
        ])->onlyInput('email');
    }

    /**
     * Muestra la vista del formulario de registro.
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Procesa la solicitud de registro de nuevo usuario.
     */
    public function register(Request $request)
    {
        $validar_datos = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'rol' => ['required', 'string', 'in:cliente,vendedor,administrador'],
        ]);

        $user = User::create([
            'name' => $validar_datos['name'],
            'email' => $validar_datos['email'],
            'password' => Hash::make($validar_datos['password']),
            'rol' => $validar_datos['rol'],
        ]);

        // Si el usuario es cliente, crear automáticamente su perfil vacío
        if ($user->rol === 'cliente') {
            PerfilCliente::create([
                'user_id' => $user->id,
                'espacio' => null,
                'luz' => null,
                'mascotas_ninos' => null,
                'nivel_cuidado' => null,
            ]);
        }

        Auth::login($user);

        return redirect()->route('home')->with('success', 'Cuenta creada exitosamente. Bienvenido a Adaptia.');
    }

    /**
     * Cierra la sesión del usuario.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
