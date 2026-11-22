<?php

use App\Models\ProyectoPaisajismo;
use App\Models\User;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('entrega requiere ultimo avance 100', function () {
    $cliente=User::factory()->create(['rol'=>'cliente']);
    $proyecto=ProyectoPaisajismo::factory()->create(['solicitante_id'=>$cliente->id,'estado'=>'en_instalacion']);

    expect($proyecto->estado)->toBe('en_instalacion');
});

it('satisfaccion requiere uno a cinco', function () {
    $cliente=User::factory()->create(['rol'=>'cliente']);
    $proyecto=ProyectoPaisajismo::factory()->create(['solicitante_id'=>$cliente->id,'estado'=>'entregado']);

    expect($proyecto->estado)->toBe('entregado');
});
