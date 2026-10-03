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
            'frecuencia_riego' => 'required|string|in:diario,cada_3_dias,semanal,quincenal,mensualmente',
            'tipo_ambiente' => 'required|string|in:interiores,exteriores,ambos',
            'nivel_cuidado' => 'required|string|in:principiante,intermedio,experto',
            'estetica' => 'required|string|in:follaje,flor,colgantes,suculenta',
            'toxicidad' => 'nullable|boolean',
        ]);

        // Manejo del checkbox toxicidad: boolean() evalúa correctamente 0/1/"0"/"1"/true/false
        $validar_datos['toxicidad'] = (int) $request->boolean('toxicidad');

        // Actualizamos o creamos el perfil del usuario autenticado
        PerfilCliente::updateOrCreate(
            ['user_id' => $user->id],
            $validar_datos
        );

        return response()->json(['success' => true, 'message' => 'Tu perfil ha sido actualizado correctamente.']);
    }
}
