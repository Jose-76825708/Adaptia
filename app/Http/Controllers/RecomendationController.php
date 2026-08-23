<?php

//Todo lo relacionado al cuestionario de recomendacion de plantas

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RecomendationController extends Controller
{
    public function index(){
        return view('recomendation.index');
    }
}