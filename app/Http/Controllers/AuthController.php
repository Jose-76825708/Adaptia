<?php

namespace App\Http\Controllers;

use App\Models\PerfilCliente;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

            return redirect()->intended(route('home'));
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
        ]);

        $user = User::create([
            'name' => $validar_datos['name'],
            'email' => $validar_datos['email'],
            'password' => Hash::make($validar_datos['password']),
            'rol' => 'cliente',
        ]);

        PerfilCliente::create([
            'user_id' => $user->id,
            'tamano_adulto' => null,
            'luz_requerida' => null,
            'toxicidad' => false,
            'nivel_cuidado' => null,
            'frecuencia_riego' => null,
            'tipo_ambiente' => null,
            'estetica' => null,
        ]);

        Auth::login($user);

        return redirect()->intended(route('home'))
            ->with('success', 'Cuenta creada exitosamente. Bienvenido a Adaptia.');
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
