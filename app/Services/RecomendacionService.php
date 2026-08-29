<?php

namespace App\Services;

use App\Models\Planta;
use Illuminate\Http\Request;

class RecomendacionService
{
    
    public function limpiarPerfil(Request $request)
    {
        
        return $request->only([
            'luz_requerida',
            'frecuencia_riego',
            'tamaño_adulto',
            'nivel_cuidado',
            'tipo_ambiente',
            'toxicidad',
            'estetica',
        ]);

    } //no hace falta testear

    public function compatibilidadOrdinal (array $orden, string $valorPlanta, string $valorUsuario)
    {
        $posicion_usuario = array_search($valorUsuario,$orden);
        $posicion_planta = array_search($valorPlanta,$orden);

        $diferencia = abs($posicion_planta - $posicion_usuario);
        $distancia_maxima = count($orden) - 1;

        return  1-($diferencia/$distancia_maxima);
    } //testeado

    public function compatibilidadCategoria (string $atributo,array $categoria, string $valorPLanta, string $valorUsuario)
    {
        $posicion_usuario = array_search($valorUsuario,$categoria);
        $posicion_planta = array_search($valorPLanta,$categoria);

        if($atributo == 'tipo_ambiente' && $posicion_usuario == 2) {

            return 1;

        }

        if($posicion_usuario == $posicion_planta){

            return 1;

        }

        return 0;

        
    } //testeado

    private function ordenesDisponibles()
    {
        return [
            'luz_requerida' => ['baja','media','alta','siempre_en_el_sol'],
            'frecuencia_riego' => ['diario','cada_3_dias','semanal','quincenal','mensualmente'],
            'nivel_cuidado' => ['principiante','intermedio','experto'],
            'tamaño_adulto' => ['pequena','mediana','grande']
        ];
    } //no hace falta testear

    private function categoriasDisponibles()
    {
        return [
            'tipo_ambiente' => ['interiores','exteriores','ambos'],
            'estetica' => ['follaje','flor','colgantes','suculenta']

        ];
    } //no hace falta testear

    private function pesosCategoricos()
    {
        return [
            'luz_requerida' => 1/6,
            'frecuencia_riego' => 1/6,
            'nivel_cuidado' => 1/6,
            'tamaño_adulto' => 1/6,
            'tipo_ambiente' => 1/6,
            'estetica' => 1/6
        ];
    }

    public function calculaScore(Planta $planta, array $perfil_usuario) 
    {

        $ordenes = $this->ordenesDisponibles();
        $categorias = $this->categoriasDisponibles();
        $pesos = $this->pesosCategoricos(); 
        $score = 0;

        foreach ($ordenes as $atributo => $orden){

        $valorPlanta = $planta[$atributo];
        $valorUsuario = $perfil_usuario[$atributo];
        $peso = $pesos[$atributo];

        // funcion para atributos ordinales
        $compatibilidad_ordinal = $this->compatibilidadOrdinal($orden,$valorPlanta,$valorUsuario);

        $score += $compatibilidad_ordinal * $peso;


        }

        foreach ($categorias as $atributo => $categoria){

        $valorPlanta = $planta[$atributo];
        $valorUsuario = $perfil_usuario[$atributo];
        $peso = $pesos[$atributo];

        $compatibilidad_categorica = $this->compatibilidadCategoria($atributo, $categoria,$valorPlanta,$valorUsuario);

        $score += $compatibilidad_categorica * $peso;

        }

        return $score;

    
    } //testeado

    public function plantasNoToxicas () 
    {
        return Planta::where('toxicidad',false)->get();
    }

    public function generarRecomendaciones (array $perfil_usuario)
    {
        if($perfil_usuario['toxicidad'] == true) {

            $plantas = $this->plantasNoToxicas();

        }else{

            $plantas = Planta::all();

        }

        $plantas_ordenadas = $plantas->sortByDesc(function (Planta $planta) use ($perfil_usuario){
            return $this->calculaScore($planta,$perfil_usuario);
        })
        ->take(10)
        ->values();

        return $plantas_ordenadas;
    }
}
