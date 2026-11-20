<?php

use App\Models\CasoMantenimiento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['rol' => 'administrador']);
});

test('la bandeja administrativa lista casos de todos los clientes con filtros, orden y paginacion', function () {
    $clienteUno = User::factory()->create(['rol' => 'cliente']);
    $clienteDos = User::factory()->create(['rol' => 'cliente']);

    foreach (range(1, 16) as $index) {
        CasoMantenimiento::factory()->create([
            'solicitante_id' => $index % 2 === 0 ? $clienteUno->id : $clienteDos->id,
            'codigo' => 'MAN-2026-000' . str_pad((string) ($index + 20), 2, '0', STR_PAD_LEFT),
            'area_verde' => $index % 3 === 0 ? 'Patio central' : 'Jardín principal',
            'tipo_mantenimiento' => $index % 2 === 0 ? 'riego' : 'general',
            'urgencia' => $index % 3 === 0 ? 'alta' : 'media',
            'estado' => $index % 4 === 0 ? 'cerrado' : 'abierto',
            'created_at' => now()->subMinutes($index),
        ]);
    }

    $casoA = CasoMantenimiento::factory()->create([
        'solicitante_id' => $clienteUno->id,
        'codigo' => 'MAN-2026-00090',
        'area_verde' => 'Zona de prueba',
        'tipo_mantenimiento' => 'riego',
        'urgencia' => 'alta',
        'estado' => 'abierto',
        'created_at' => now(),
    ]);

    $casoB = CasoMantenimiento::factory()->create([
        'solicitante_id' => $clienteUno->id,
        'codigo' => 'MAN-2026-00091',
        'area_verde' => 'Zona de prueba',
        'tipo_mantenimiento' => 'general',
        'urgencia' => 'media',
        'estado' => 'abierto',
        'created_at' => now(),
    ]);

    $response = $this->actingAs($this->admin)->get(route('mantenimiento.casos.index'));
    $response->assertOk();

    $this->actingAs($this->admin)
        ->get(route('mantenimiento.casos.index', ['estado' => 'abierto']))
        ->assertOk();

    $this->actingAs($this->admin)
        ->get(route('mantenimiento.casos.index', ['urgencia' => 'alta']))
        ->assertOk();

    $this->actingAs($this->admin)
        ->get(route('mantenimiento.casos.index', ['tipo' => 'riego']))
        ->assertOk();

    $this->actingAs($this->admin)
        ->get(route('mantenimiento.casos.index', ['q' => '00090']))
        ->assertOk();

    $this->actingAs($this->admin)
        ->get(route('mantenimiento.casos.index', ['estado' => 'abierto', 'urgencia' => 'alta', 'tipo' => 'riego', 'q' => 'Zona']))
        ->assertOk();

    $this->actingAs($this->admin)
        ->get(route('mantenimiento.casos.index', ['estado' => 'valor_invalido']))
        ->assertOk();

    $resultado = $this->actingAs($this->admin)->get(route('mantenimiento.casos.index', ['q' => 'Zona de prueba']))->viewData('casos');
    expect($resultado->pluck('id')->all())->toBe([$casoB->id, $casoA->id]);

    $listado = $this->actingAs($this->admin)->get(route('mantenimiento.casos.index', ['estado' => 'abierto']))->viewData('casos');
    expect($listado->count())->toBeGreaterThan(0);

    $paginated = $this->actingAs($this->admin)->get(route('mantenimiento.casos.index', ['page' => 2]));
    $paginated->assertOk();
});

test('la bandeja ignora q como arreglo y conserva solo filtros validos en la paginacion', function () {
    $cliente = User::factory()->create(['rol' => 'cliente']);

    foreach (range(1, 20) as $index) {
        CasoMantenimiento::factory()->create([
            'codigo' => 'MAN-2026-' . str_pad((string) ($index + 100), 5, '0', STR_PAD_LEFT),
            'solicitante_id' => $cliente->id,
            'estado' => 'abierto',
            'urgencia' => 'alta',
            'tipo_mantenimiento' => 'riego',
            'area_verde' => 'Jardín principal',
            'created_at' => now()->subMinutes($index),
        ]);
    }

    $response = $this->actingAs($this->admin)
        ->get(route('mantenimiento.casos.index', ['q' => ['x']]))
        ->assertOk();

    $paginated = $this->actingAs($this->admin)
        ->get(route('mantenimiento.casos.index', ['estado' => 'valor_invalido', 'urgencia' => 'alta', 'page' => 2]));

    $paginated->assertOk();
    $paginated->assertSee('urgencia=alta');
    $paginated->assertDontSee('estado=valor_invalido');
});
