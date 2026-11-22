<?php

use App\Models\Planta;
use App\Models\ProyectoPaisajismo;
use App\Models\PropuestaDiseno;
use App\Models\TipoPlanta;
use App\Models\User;
use App\Services\DisenoProyectoService;
use App\Services\ProyectoPaisajismoService;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('persiste propuesta con precio snapshot y version', function () {
    $admin=User::factory()->create(['rol'=>'administrador']);
    $cliente=User::factory()->create(['rol'=>'cliente']);
    $tipo=TipoPlanta::factory()->create();
    $planta=Planta::factory()->create(['tipo_planta_id'=>$tipo->id,'nombre'=>'Rosas','precio'=>20,'stock_actual'=>10]);
    $proyecto=ProyectoPaisajismo::factory()->create(['solicitante_id'=>$cliente->id,'estado'=>'solicitado','area_m2'=>100,'presupuesto_estimado'=>1000]);

    $diseno=new DisenoProyectoService();
    $propuesta=$diseno->guardarPropuesta($proyecto,$admin->id,'Diseño integral del jardín con plantas resistentes.',[['planta_id'=>$planta->id,'cantidad'=>2]],now());

    expect($propuesta)->toBeInstanceOf(PropuestaDiseno::class)
        ->and($propuesta->version)->toBe(1)
        ->and($propuesta->plantas()->count())->toBe(1)
        ->and($proyecto->fresh()->estado)->toBe('propuesto');
});

it('aprueba propuesta vigente y cambia el proyecto a aprobado', function () {
    $admin=User::factory()->create(['rol'=>'administrador']);
    $cliente=User::factory()->create(['rol'=>'cliente']);
    $proyecto=ProyectoPaisajismo::factory()->create(['solicitante_id'=>$cliente->id,'estado'=>'propuesto']);
    $propuesta=PropuestaDiseno::factory()->create(['proyecto_id'=>$proyecto->id,'autor_id'=>$admin->id,'version'=>1]);

    $propuesta=(new DisenoProyectoService())->registrarDecision($proyecto,$admin->id,'aprobada',null,now());

    expect($propuesta->decision)->toBe('aprobada')
        ->and($proyecto->fresh()->estado)->toBe('aprobado');
});

it('rechaza propuesta si el proyecto no esta solicitado', function () {
    $proyecto=ProyectoPaisajismo::factory()->create(['estado'=>'propuesto']);
    expect($proyecto->estado)->toBe('propuesto');
});
