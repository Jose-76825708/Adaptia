<?php

namespace App\Http\Controllers;

use App\Services\PersonalService;
use Illuminate\Http\Request;

class PersonalController extends Controller
{
    public function __construct(private PersonalService $service)
    {
    }

    public function create()
    {
        return view('personal.create');
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'rol' => ['required', 'string', 'in:vendedor,administrador'],
        ]);

        $this->service->crearCuenta($datos);

        return redirect()->route('personal.create')
            ->with('success', 'Cuenta de personal creada correctamente.');
    }
}
