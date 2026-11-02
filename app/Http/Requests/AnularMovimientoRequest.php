<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class AnularMovimientoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()?->rol === 'administrador';
    }

    public function rules(): array
    {
        return [
            'motivo' => ['required', 'string', 'max:255'],
        ];
    }
}
