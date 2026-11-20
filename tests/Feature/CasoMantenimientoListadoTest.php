<?php

use App\Models\CasoMantenimiento;
use App\Models\User;
use App\Services\CasoMantenimientoService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('el listado del cliente solo muestra sus propias solicitudes y las ordena del mas reciente al mas antiguo', function () {
    $clienteUno = User::factory()->create(['rol' => 'cliente']);
    $clienteDos = User::factory()->create(['rol' => 'cliente']);

    $casoUno = CasoMantenimiento::factory()->create([
        'solicitante_id' => $clienteUno->id,
        'area_verde' => 'Jardín delantero',
        'estado' => 'abierto',
        'tipo_mantenimiento' => 'riego',
        'urgencia' => 'alta',
        'codigo' => 'MAN-2026-00001',
        'descripcion' => 'Necesito revisión general del jardín delantero.',
        'created_at' => now()->subDay(),
    ]);

    $casoDos = CasoMantenimiento::factory()->create([
        'solicitante_id' => $clienteUno->id,
        'area_verde' => 'Patio central',
        'estado' => 'diagnosticado',
        'tipo_mantenimiento' => 'poda',
        'urgencia' => 'media',
        'codigo' => 'MAN-2026-00002',
        'descripcion' => 'Necesito poda para ramas laterales del patio central.',
        'created_at' => now(),
    ]);

    CasoMantenimiento::factory()->create([
        'solicitante_id' => $clienteDos->id,
        'area_verde' => 'Invernadero',
        'estado' => 'abierto',
        'tipo_mantenimiento' => 'control_plagas',
        'urgencia' => 'alta',
        'codigo' => 'MAN-2026-00003',
        'descripcion' => 'Necesito controlar plagas en el invernadero.',
    ]);

    $response = $this->actingAs($clienteUno)
        ->get(route('mantenimiento.solicitudes.index'));

    $response->assertOk()
        ->assertViewHas('casos', function ($casos) use ($casoDos, $casoUno) {
            return $casos->pluck('id')->all() === [$casoDos->id, $casoUno->id];
        })
        ->assertDontSee('Invernadero');
});

test('el listado del cliente muestra estado vacio cuando no tiene solicitudes', function () {
    $cliente = User::factory()->create(['rol' => 'cliente']);

    $this->actingAs($cliente)
        ->get(route('mantenimiento.solicitudes.index'))
        ->assertOk()
        ->assertSee('No tienes solicitudes de mantenimiento')
        ->assertSee('Solicitar mantenimiento');
});

test('el listado del cliente paginara 10 elementos por pagina con 11 casos', function () {
    $cliente = User::factory()->create(['rol' => 'cliente']);

    for ($i = 1; $i <= 11; $i++) {
        CasoMantenimiento::factory()->create([
            'solicitante_id' => $cliente->id,
            'area_verde' => 'Área '.$i,
            'codigo' => sprintf('MAN-2026-%05d', $i),
            'descripcion' => 'Necesito revisión del área '.$i.'.',
            'estado' => 'abierto',
            'tipo_mantenimiento' => 'general',
            'urgencia' => 'media',
            'created_at' => now()->subMinutes($i),
        ]);
    }

    $response = $this->actingAs($cliente)
        ->get(route('mantenimiento.solicitudes.index'));

    $response->assertOk()
        ->assertViewHas('casos', function ($casos) {
            return $casos->count() === 10;
        });
});

test('listarPorSolicitante filtra, ordena y paginara los casos del usuario', function () {
    $clienteUno = User::factory()->create(['rol' => 'cliente']);
    $clienteDos = User::factory()->create(['rol' => 'cliente']);

    CasoMantenimiento::factory()->create([
        'solicitante_id' => $clienteUno->id,
        'codigo' => 'MAN-2026-00011',
        'descripcion' => 'Caso antiguo',
        'area_verde' => 'Área antigua',
        'created_at' => now()->subWeek(),
    ]);

    $casoReciente = CasoMantenimiento::factory()->create([
        'solicitante_id' => $clienteUno->id,
        'codigo' => 'MAN-2026-00012',
        'descripcion' => 'Caso reciente',
        'area_verde' => 'Área reciente',
        'created_at' => now(),
    ]);

    CasoMantenimiento::factory()->create([
        'solicitante_id' => $clienteDos->id,
        'codigo' => 'MAN-2026-00013',
        'descripcion' => 'Caso ajeno',
        'area_verde' => 'Área ajena',
        'created_at' => now(),
    ]);

    $resultado = (new CasoMantenimientoService)->listarPorSolicitante($clienteUno->id, 10);

    expect($resultado->count())->toBe(2)
        ->and($resultado->first()->id)->toBe($casoReciente->id)
        ->and($resultado->pluck('solicitante_id')->unique()->all())->toBe([$clienteUno->id]);
});

test('listarPorSolicitante desempata por id cuando dos casos comparten created_at', function () {
    $cliente = User::factory()->create(['rol' => 'cliente']);

    $casoViejo = CasoMantenimiento::factory()->create([
        'solicitante_id' => $cliente->id,
        'codigo' => 'MAN-2026-00021',
        'descripcion' => 'Caso menor',
        'area_verde' => 'Área menor',
        'created_at' => now(),
    ]);

    $casoNuevo = CasoMantenimiento::factory()->create([
        'solicitante_id' => $cliente->id,
        'codigo' => 'MAN-2026-00022',
        'descripcion' => 'Caso mayor',
        'area_verde' => 'Área mayor',
        'created_at' => now(),
    ]);

    $resultado = (new CasoMantenimientoService)->listarPorSolicitante($cliente->id, 10);

    expect($resultado->first()->id)->toBe($casoNuevo->id)
        ->and($resultado->last()->id)->toBe($casoViejo->id);
});
