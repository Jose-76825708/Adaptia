<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class MovimientoInventarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check()
            && in_array(Auth::user()?->rol, ['administrador', 'vendedor'], true);
    }

    public function rules(): array
    {
        return [
            'planta_id' => ['required', 'exists:plantas,id'],
            'tipo' => ['required', 'in:entrada,salida'],
            'cantidad' => ['required', 'integer', 'min:1'],
            'motivo' => ['nullable', 'string', 'max:255'],
        ];
    }
}
