<?php

use App\Models\AvanceInstalacion;
use App\Models\ProyectoPaisajismo;
use App\Models\User;
use App\Services\InstalacionProyectoService;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('rechaza avance fuera de en instalacion', function () {
    $autor=User::factory()->create(['rol'=>'administrador']);
    $proyecto=ProyectoPaisajismo::factory()->create(['estado'=>'aprobado']);

    expect(fn () => (new InstalacionProyectoService())->registrarAvance($proyecto,$autor->id,'Avance válido',50))->toThrow(InvalidArgumentException::class,'instalación');
});

it('rechaza descripcion corta porcentaje fuera y decreciente', function () {
    $autor=User::factory()->create(['rol'=>'administrador']);
    $proyecto=ProyectoPaisajismo::factory()->create(['estado'=>'en_instalacion']);
    AvanceInstalacion::create(['proyecto_id'=>$proyecto->id,'autor_id'=>$autor->id,'descripcion'=>'Avance inicial válido','porcentaje'=>70]);

    $service=new InstalacionProyectoService();
    expect(fn () => $service->registrarAvance($proyecto,$autor->id,'x',50))->toThrow(InvalidArgumentException::class,'descripción')
        ->and(fn () => $service->registrarAvance($proyecto,$autor->id,'Avance válido',101))->toThrow(InvalidArgumentException::class,'porcentaje')
        ->and(fn () => $service->registrarAvance($proyecto,$autor->id,'Avance válido',60))->toThrow(InvalidArgumentException::class,'disminuir');
});
