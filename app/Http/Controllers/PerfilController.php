<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PerfilCliente;
use Illuminate\Support\Facades\Auth;

class PerfilController extends Controller
{
    /**
     * Muestra el formulario de edición del perfil del cliente.
     */
    public function edit()
    {
        $user = Auth::user();
        $perfil = PerfilCliente::where('user_id', $user->id)->first();

        return view('perfiles.edit', compact('perfil'));
    }

    /**
     * Actualiza los datos del perfil del cliente.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validar_datos = $request->validate([
            'espacio' => 'required|string|in:pequena,mediana,grande',
            'luz' => 'required|string|in:baja,media,alta,siempre_en_el_sol',
            'mascotas_ninos' => 'nullable|boolean',
            'nivel_cuidado' => 'required|string|in:principiante,intermedio,experto',
        ]);

        // Manejo del checkbox mascotas_ninos: si no viene en el request, es false (0)
        $validar_datos['mascotas_ninos'] = $request->has('mascotas_ninos') ? 1 : 0;

        // Actualizamos o creamos el perfil del usuario autenticado
        PerfilCliente::updateOrCreate(
            ['user_id' => $user->id],
            $validar_datos
        );

        return redirect()->back()->with('success', 'Tu perfil ha sido actualizado correctamente.');
    }
}
