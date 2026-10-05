<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLecturaSensorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'planta_vendida_id' => ['required', 'integer', 'exists:plantas_vendidas,id'],
            'humedad_suelo' => ['required', 'numeric', 'between:0,100'],
            'temperatura' => ['required', 'numeric', 'between:-40,80'],
            'humedad_ambiental' => ['required', 'numeric', 'between:0,100'],
            'luz' => ['required', 'numeric', 'between:0,65535'],
            'fecha_hora' => [
                'required',
                'date',
                'regex:/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d+)?(?:Z|[+-]\d{2}:\d{2})?$/',
            ],
        ];
    }
}
