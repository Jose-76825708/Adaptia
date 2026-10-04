<?php

namespace App\Services;

use App\Models\User;

class PersonalService
{
    public function crearCuenta(array $datos): User
    {
        return User::create([
            'name' => $datos['name'],
            'email' => $datos['email'],
            'password' => $datos['password'],
            'rol' => $datos['rol'],
        ]);
    }
}
