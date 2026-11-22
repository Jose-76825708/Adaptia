<?php

use App\Models\Planta;
use App\Models\ProyectoPaisajismo;
use App\Models\PropuestaDiseno;
use App\Models\PropuestaPlanta;
use App\Models\TipoPlanta;
use App\Models\User;
use App\Services\InstalacionProyectoService;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('inicia instalacion y descuenta stock en transaccion', function () {
    $tipo=TipoPlanta::factory()->create();
    $planta=Planta::factory()->create(['tipo_planta_id'=>$tipo->id,'nombre'=>'Rosas','precio'=>20,'stock_actual'=>10,'stock_minimo'=>1]);
    $autor=User::factory()->create(['rol'=>'administrador']);
    $cliente=User::factory()->create(['rol'=>'cliente']);
    $proyecto=ProyectoPaisajismo::factory()->create(['solicitante_id'=>$cliente->id,'estado'=>'aprobado','codigo'=>'PAI-2026-00001']);
    $propuesta=PropuestaDiseno::factory()->create(['proyecto_id'=>$proyecto->id,'autor_id'=>$autor->id,'version'=>1,'decision'=>'aprobada']);
    PropuestaPlanta::create(['propuesta_id'=>$propuesta->id,'planta_id'=>$planta->id,'cantidad'=>3,'precio_unitario'=>20]);

    $actual=(new InstalacionProyectoService())->iniciarInstalacion($proyecto,$autor->id);

    expect($actual->estado)->toBe('en_instalacion')
        ->and($planta->fresh()->stock_actual)->toBe(7);
});

it('rechaza inicio sin propuesta aprobada', function () {
    $admin=User::factory()->create(['rol'=>'administrador']);
    $cliente=User::factory()->create(['rol'=>'cliente']);
    $proyecto=ProyectoPaisajismo::factory()->create(['solicitante_id'=>$cliente->id,'estado'=>'aprobado']);

    expect(fn () => (new InstalacionProyectoService())->iniciarInstalacion($proyecto,$admin->id))->toThrow(InvalidArgumentException::class,'aprobada');
});
