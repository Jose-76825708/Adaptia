<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class AlmacenController extends Controller
{
    public function index(): View
    {
        return view('almacen.index');
    }

    public function recepcion(): View
    {
        return view('almacen.recepcion');
    }
}
