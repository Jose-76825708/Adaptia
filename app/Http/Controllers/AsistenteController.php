<?php

namespace App\Http\Controllers;

use App\Exceptions\AsistenteModeloException;
use App\Services\AsistenteService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AsistenteController extends Controller
{
    public function index()
    {
        return view('asistente.index');
    }

    public function chat(Request $request, AsistenteService $service)
    {
        $camposPermitidos = ['message', '_token'];
        if (array_diff(array_keys($request->all()), $camposPermitidos) !== []) {
            throw ValidationException::withMessages([
                'message' => 'Envía solo el texto de tu consulta.',
            ]);
        }

        $datos = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ], [
            'message.required' => 'Escribe una consulta.',
            'message.max' => 'La consulta no puede superar 1000 caracteres.',
        ]);

        try {
            $respuesta = $service->responder($request->user(), $datos['message']);
        } catch (AsistenteModeloException) {
            return response()->json([
                'message' => 'El asistente no pudo responder ahora. Inténtalo nuevamente más tarde.',
            ], 503);
        }

        return response()->json(['respuesta' => $respuesta]);
    }
}
