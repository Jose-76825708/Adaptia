<?php

namespace App\Http\Controllers;

use App\Services\ResumenAlmacenService;

class ResumenAlmacenController extends Controller
{
    public function __construct(private ResumenAlmacenService $service)
    {
    }

    public function index()
    {
        return view('almacen.resumen', ['resumen' => $this->service->obtenerResumen()]);
    }
}
