<?php

use App\Models\Planta;
use App\Services\RecomendacionService;
use Illuminate\Http\Request;

test('calcula compatibilidad ordinal para coincidencias, diferencias parciales y totales', function (
    string $valorPlanta,
    string $valorPerfil,
    float $compatibilidad
) {
    expect((new RecomendacionService)->compatibilidadOrdinal(
        ['baja', 'media', 'alta'],
        $valorPlanta,
        $valorPerfil
    ))->toEqual($compatibilidad);
})->with([
    'coincidencia total' => ['baja', 'baja', 1.0],
    'diferencia parcial' => ['media', 'baja', 0.5],
    'diferencia total' => ['alta', 'baja', 0.0],
]);

test('calcula compatibilidad categórica para coincidencias y excepciones de ambiente', function (
    string $valorPerfil,
    string $valorPlanta,
    float $compatibilidad
) {
    expect((new RecomendacionService)->compatibilidadCategoria(
        'tipo_ambiente',
        ['interiores', 'exteriores', 'ambos'],
        $valorPerfil,
        $valorPlanta
    ))->toEqual($compatibilidad);
})->with([
    'coincidencia exacta' => ['interiores', 'interiores', 1.0],
    'categorías distintas' => ['interiores', 'exteriores', 0.0],
    'ambos ambientes son compatibles' => ['interiores', 'ambos', 1.0],
]);

test('la estetica suculenta coincide si el perfil y la planta difieren en numero', function () {
    $service = new RecomendacionService;

    expect($service->compatibilidadCategoria(
        'estetica',
        ['follaje', 'flor', 'colgantes', 'suculenta'],
        'suculentas',
        'suculenta'
    ))->toBe(1)
        ->and($service->compatibilidadCategoria(
            'estetica',
            ['follaje', 'flor', 'colgantes', 'suculenta'],
            'suculenta',
            'suculentas'
        ))->toBe(1);
});

test('limpia el perfil conservando solo los campos usados para recomendar', function () {
    $service = new RecomendacionService;
    $request = Request::create('/', 'POST', [
        'luz_requerida' => 'media',
        'frecuencia_riego' => 'semanal',
        'tamaño_adulto' => 'mediana',
        'nivel_cuidado' => 'principiante',
        'tipo_ambiente' => 'interiores',
        'toxicidad' => '1',
        'estetica' => 'follaje',
        'rol' => 'administrador',
    ]);

    expect($service->limpiarPerfil($request))->toBe([
        'luz_requerida' => 'media',
        'frecuencia_riego' => 'semanal',
        'tamaño_adulto' => 'mediana',
        'nivel_cuidado' => 'principiante',
        'tipo_ambiente' => 'interiores',
        'toxicidad' => '1',
        'estetica' => 'follaje',
    ]);
});

// TEST DE LA FUNCIÓN PARA CALCULAR EL SCORE

// PRIMER TEST
test('una planta cumple TODO perfectamente debe arrojar el 100% de compatibilidad', function () {

    $service = new RecomendacionService;

    $planta = new Planta([
        'luz_requerida' => 'media',
        'frecuencia_riego' => 'semanal',
        'nivel_cuidado' => 'principiante',
        'tamaño_adulto' => 'pequena',
        'tipo_ambiente' => 'interiores',
        'estetica' => 'follaje',
    ]);

    $perfil_usuario = [
        'luz_requerida' => 'media',
        'frecuencia_riego' => 'semanal',
        'nivel_cuidado' => 'principiante',
        'tamaño_adulto' => 'pequena',
        'tipo_ambiente' => 'interiores',
        'estetica' => 'follaje',
    ];

    $resultado = $service->calculaScore($planta, $perfil_usuario);

    expect(round($resultado, 2))->toEqual(1);

});

// SEGUNDO TEST
test('una planta no cumple absolutamente nada debe arrojar el 0% de compatibilidad', function () {

    $service = new RecomendacionService;

    $planta = new Planta([
        'luz_requerida' => 'baja',
        'frecuencia_riego' => 'diario',
        'nivel_cuidado' => 'principiante',
        'tamaño_adulto' => 'pequena',
        'tipo_ambiente' => 'interiores',
        'estetica' => 'follaje',
    ]);

    $perfil_usuario = [
        'luz_requerida' => 'siempre_en_el_sol',
        'frecuencia_riego' => 'mensualmente',
        'nivel_cuidado' => 'experto',
        'tamaño_adulto' => 'grande',
        'tipo_ambiente' => 'exteriores',
        'estetica' => 'flor',
    ];

    $resultado = $service->calculaScore($planta, $perfil_usuario);

    expect(round($resultado, 2))->toEqual(0);

});

// TERCER TEST

test('cuando una planta cumple la mitad del perfil del usuario', function () {

    $service = new RecomendacionService;

    $planta = new Planta([
        'luz_requerida' => 'baja',
        'frecuencia_riego' => 'diario',
        'nivel_cuidado' => 'principiante',
        'tamaño_adulto' => 'pequena',
        'tipo_ambiente' => 'interiores',
        'estetica' => 'follaje',
    ]);

    $perfil_usuario = [
        'luz_requerida' => 'baja',
        'frecuencia_riego' => 'diario',
        'nivel_cuidado' => 'principiante',
        'tamaño_adulto' => 'grande',
        'tipo_ambiente' => 'exteriores',
        'estetica' => 'flor',
    ];

    $resultado = $service->calculaScore($planta, $perfil_usuario);

    expect(round($resultado, 2))->toEqual(0.5);

});
