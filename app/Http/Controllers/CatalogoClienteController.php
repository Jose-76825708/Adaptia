<?php

namespace App\Http\Controllers;

use App\Services\PlantaService;
use Illuminate\Contracts\View\View;

class CatalogoClienteController extends Controller
{
    public function __construct(private PlantaService $plantaService)
    {
    }

    public function index(): View
    {
        $plantas = $this->plantaService->getAll();

        return view('home.catalogo-plantas', compact('plantas'));
    }
}
