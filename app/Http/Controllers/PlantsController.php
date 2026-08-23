<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PlantsController extends Controller
{
    public function index(){
        return view('plants.index');
    }

    //Futuros filtros

    /*public function porLuz($nluz){
        return view('plants.index', ['nivel' => $nluz]);
    }

    public function porRiego($nriego){
        return view('plants.index', ['nivel' => $nriego]);
    }

    public function porEspacio($nespacio){
        return view('plants.index', ['nivel' => $nespacio]);
    }

    public function porExperiencia($nexperiencia){
        return view('plants.index', ['nivel' => $nexperiencia]);
    }
    
    */
}