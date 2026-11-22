<?php

use App\Models\AvanceInstalacion;
use App\Models\EntregaProyecto;
use App\Models\ProyectoPaisajismo;
use App\Models\User;
use App\Services\EntregaProyectoService;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('rechaza entrega sin avance 100', function () {
    $autor=User::factory()->create(['rol'=>'administrador']);
    $cliente=User::factory()->create(['rol'=>'cliente']);
    $proyecto=ProyectoPaisajismo::factory()->create(['solicitante_id'=>$cliente->id,'estado'=>'en_instalacion']);
    AvanceInstalacion::create(['proyecto_id'=>$proyecto->id,'autor_id'=>$autor->id,'descripcion'=>'Avance intermedio','porcentaje'=>80]);

    expect(fn () => (new EntregaProyectoService())->registrarEntrega($proyecto,$autor->id,'Entrega final y revisión completa','Mantener riego y podar según temporada'))
        ->toThrow(InvalidArgumentException::class,'100');
});

it('registra entrega en 100 y satisface una sola vez', function () {
    $autor=User::factory()->create(['rol'=>'administrador']);
    $cliente=User::factory()->create(['rol'=>'cliente']);
    $proyecto=ProyectoPaisajismo::factory()->create(['solicitante_id'=>$cliente->id,'estado'=>'en_instalacion']);
    AvanceInstalacion::create(['proyecto_id'=>$proyecto->id,'autor_id'=>$autor->id,'descripcion'=>'Instalación finalizada','porcentaje'=>100]);

    $service=new EntregaProyectoService();
    $entrega=$service->registrarEntrega($proyecto,$autor->id,'Entrega final y revisión completa','Mantener riego y podar según temporada');

    expect($entrega)->toBeInstanceOf(EntregaProyecto::class)
        ->and($proyecto->fresh()->estado)->toBe('entregado');

    $entrega=$service->registrarSatisfaccion($proyecto,'Muy satisfecho',5);
    expect($entrega->calificacion)->toBe(5)
        ->and(fn () => $service->registrarSatisfaccion($proyecto,'Segunda vez',4))->toThrow(InvalidArgumentException::class,'ya fue calificado');
});
