<?php

use App\Models\Planta;
use App\Models\PerfilCliente;
use App\Models\TipoPlanta;
use App\Models\User;
use App\Services\RecomendacionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// TEST DE NUESTRA FUNCIONALIDAD DE PLANTAS NO TOXICAS

//  1ER TEST

test('la funcion plantasNoToxicas debe devolvernos netamente plantas no toxicas', function () {

    $service = new RecomendacionService();

    Planta::factory()->create(['toxicidad' => true]);
    Planta::factory()->create(['toxicidad' => false]);
    Planta::factory()->create(['toxicidad' => true]);

    $resultado = $service->plantasNoToxicas();

    expect($resultado->count())->toBe(1);

});

// 2DO TEST
test('la funcion de generar recomendación nos dara una lista en base a 3 plantas de muestra, ninguna es tóxica', function () {

    $service = new RecomendacionService();

    // creacion de las plantas de prueba
    $plantaBuena = Planta::factory()->create([
        'luz_requerida' => 'media',
        'frecuencia_riego' => 'semanal',
        'nivel_cuidado' => 'principiante',
        'tamaño_adulto' => 'pequena',
        'tipo_ambiente' => 'interiores',
        'estetica' => 'follaje',
        'toxicidad' => false,
    ]);
    $plantamedia = Planta::factory()->create([
        'luz_requerida' => 'baja',
        'frecuencia_riego' => 'semanal',
        'nivel_cuidado' => 'principiante',
        'tamaño_adulto' => 'mediana',
        'tipo_ambiente' => 'interiores',
        'estetica' => 'follaje',
        'toxicidad' => false,
    ]);
    $plantabaja = Planta::factory()->create([
        'luz_requerida' => 'siempre_en_el_sol',
        'frecuencia_riego' => 'mensualmente',
        'nivel_cuidado' => 'experto',
        'tamaño_adulto' => 'grande',
        'tipo_ambiente' => 'exteriores',
        'estetica' => 'suculentas',
        'toxicidad' => false,
    ]);

    // definicion del perfil del usuario
    $usuariPerfil = [
        'luz_requerida' => 'media',
        'frecuencia_riego' => 'semanal',
        'nivel_cuidado' => 'principiante',
        'tamaño_adulto' => 'pequena',
        'tipo_ambiente' => 'interiores',
        'estetica' => 'follaje',
        'toxicidad' => false,
    ];

    $resultado = $service->generarRecomendaciones($usuariPerfil);

    $ordenEsperado = [$plantaBuena->id, $plantamedia->id, $plantabaja->id];
    $ordenObtenido = $resultado->pluck('id')->toArray();

    expect($ordenObtenido)->toBe($ordenEsperado);

});

test('generar recomendaciones incluye el porcentaje de compatibilidad para mostrarlo en la vista', function () {
    $planta = Planta::factory()->create([
        'luz_requerida' => 'alta',
        'frecuencia_riego' => 'quincenal',
        'nivel_cuidado' => 'principiante',
        'tamaño_adulto' => 'mediana',
        'tipo_ambiente' => 'ambos',
        'estetica' => 'suculentas',
        'toxicidad' => true,
    ]);

    $perfil = [
        'luz_requerida' => 'alta',
        'frecuencia_riego' => 'quincenal',
        'nivel_cuidado' => 'principiante',
        'tamaño_adulto' => 'mediana',
        'tipo_ambiente' => 'ambos',
        'estetica' => 'suculenta',
        'toxicidad' => false,
    ];

    $recomendacion = (new RecomendacionService())
        ->generarRecomendaciones($perfil)
        ->firstWhere('id', $planta->id);

    expect($recomendacion)->not->toBeNull()
        ->and(round($recomendacion->compatibilidad_porcentaje, 1))->toBe(100.0);
});

test('el panel del cliente muestra el porcentaje calculado de compatibilidad', function () {
    $cliente = User::factory()->create(['rol' => 'cliente']);

    PerfilCliente::create([
        'user_id' => $cliente->id,
        'luz_requerida' => 'alta',
        'frecuencia_riego' => 'quincenal',
        'nivel_cuidado' => 'principiante',
        'tamaño_adulto' => 'mediana',
        'tipo_ambiente' => 'ambos',
        'estetica' => 'suculenta',
        'toxicidad' => false,
    ]);

    Planta::factory()->create([
        'nombre' => 'Aloe Vera',
        'luz_requerida' => 'alta',
        'frecuencia_riego' => 'quincenal',
        'nivel_cuidado' => 'principiante',
        'tamaño_adulto' => 'mediana',
        'tipo_ambiente' => 'ambos',
        'estetica' => 'suculentas',
        'toxicidad' => true,
    ]);

    $this->actingAs($cliente)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('Aloe Vera')
        ->assertSee('Compatibilidad 100.0%');
});