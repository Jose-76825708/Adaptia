<?php

use App\Models\ProyectoPaisajismo;
use App\Models\User;
use App\Services\ProyectoPaisajismoService;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('abre proyecto en solicitado con codigo PAI y ignora claves inyectadas', function () {
    $cliente=User::factory()->create(['rol'=>'cliente']);
    $service=new ProyectoPaisajismoService();

    $proyecto=$service->abrir($cliente->id,[
        'nombre'=>'Jardín central','ubicacion'=>'Huancayo','descripcion'=>'Diseño integral del área verde','area_m2'=>'120,50','presupuesto_estimado'=>'5000','fecha_deseada'=>now()->addDays(10)->format('Y-m-d'),
        'estado'=>'entregado','solicitante_id'=>999,
    ],now()->startOfDay());

    expect($proyecto)->toBeInstanceOf(ProyectoPaisajismo::class)
        ->and($proyecto->estado)->toBe('solicitado')
        ->and($proyecto->solicitante_id)->toBe($cliente->id)
        ->and($proyecto->codigo)->toMatch('/^PAI-\\d{4}-\\d{5}$/');
});

it('rechaza abrir proyecto si los datos son invalidos', function () {
    $cliente=User::factory()->create(['rol'=>'cliente']);
    $service=new ProyectoPaisajismoService();

    expect(fn () => $service->abrir($cliente->id,['nombre'=>'x','ubicacion'=>'x','descripcion'=>'x','area_m2'=>'0'],now()->startOfDay()))
        ->toThrow(Illuminate\Validation\ValidationException::class);
});

it('puedeTransicionar cubre la matriz aprobada', function () {
    $service=new ProyectoPaisajismoService();

    expect($service->puedeTransicionar('solicitado','propuesto'))->toBeTrue()
        ->and($service->puedeTransicionar('propuesto','aprobado'))->toBeTrue()
        ->and($service->puedeTransicionar('propuesto','solicitado'))->toBeTrue()
        ->and($service->puedeTransicionar('aprobado','en_instalacion'))->toBeTrue()
        ->and($service->puedeTransicionar('en_instalacion','entregado'))->toBeTrue()
        ->and($service->puedeTransicionar('entregado','solicitado'))->toBeFalse();
});
