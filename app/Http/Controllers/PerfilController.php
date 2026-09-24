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
            'tamaño_adulto' => 'required|string|in:pequena,mediana,grande',
            'luz_requerida' => 'required|string|in:baja,media,alta,siempre_en_el_sol',
            'toxicidad' => 'nullable|boolean',
            'nivel_cuidado' => 'required|string|in:principiante,intermedio,experto',
        ]);

        // Manejo del checkbox toxicidad: si no viene en el request, es false (0)
        $validar_datos['toxicidad'] = $request->has('toxicidad') ? 1 : 0;

        // Actualizamos o creamos el perfil del usuario autenticado
        PerfilCliente::updateOrCreate(
            ['user_id' => $user->id],
            $validar_datos
        );

        return redirect()->back()->with('success', 'Tu perfil ha sido actualizado correctamente.');
    }
}
