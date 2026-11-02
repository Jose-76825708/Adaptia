<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class FiltroMovimientosRequest extends FormRequest
{
    /**
     * Si la validación falla, se vuelve al listado (no a la página previa)
     * para que los errores se muestren aunque el usuario haya escrito la URL a mano.
     */
    protected $redirectRoute = 'movimientos-inventario.index';

    public function authorize(): bool
    {
        return Auth::check()
            && in_array(Auth::user()?->rol, ['administrador', 'vendedor'], true);
    }

    public function rules(): array
    {
        return [
            'planta_id' => ['nullable', 'integer', 'exists:plantas,id'],
            'tipo' => ['nullable', 'in:entrada,salida'],
            'desde' => ['nullable', 'date_format:Y-m-d'],
            'hasta' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:desde'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'planta_id.exists' => 'La planta seleccionada no existe.',
            'tipo.in' => 'El tipo de movimiento debe ser entrada o salida.',
            'desde.date_format' => 'La fecha inicial no tiene un formato válido.',
            'hasta.date_format' => 'La fecha final no tiene un formato válido.',
            'hasta.after_or_equal' => 'La fecha final no puede ser anterior a la fecha inicial.',
        ];
    }
}
