<?php

use App\Models\ProyectoPaisajismo;
use App\Models\AvanceInstalacion;
use App\Models\User;
use App\Services\InstalacionProyectoService;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('avance requiere descripcion y porcentaje valido creciente', function () {
    $cliente=User::factory()->create(['rol'=>'cliente']);
    $proyecto=ProyectoPaisajismo::factory()->create(['solicitante_id'=>$cliente->id,'estado'=>'en_instalacion']);

    $autor=User::factory()->create(['rol'=>'administrador']);
    $avance=(new InstalacionProyectoService())->registrarAvance($proyecto,$autor->id,'Avance inicial de jardinería',50);

    expect($avance)->toBeInstanceOf(AvanceInstalacion::class)
        ->and($avance->porcentaje)->toBe(50)
        ->and(AvanceInstalacion::count())->toBe(1);
});
