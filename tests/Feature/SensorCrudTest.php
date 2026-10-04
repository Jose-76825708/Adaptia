<?php

use App\Models\Planta;
use App\Models\PlantaVendida;
use App\Models\Sensor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

function crearSensorAsignado(): array
{
    $cliente = User::factory()->create(['rol' => 'cliente']);
    $vendedor = User::factory()->create(['rol' => 'vendedor']);
    $planta = Planta::factory()->create();
    $venta = \App\Models\Venta::create([
        'user_id' => $cliente->id,
        'vendedor_id' => $vendedor->id,
        'planta_id' => $planta->id,
        'cantidad' => 1,
    ]);
    $sensor = Sensor::create([
        'identificador_fisico' => 'SENSOR-ASIGNADO-CRUD-001',
        'estado' => 'activo',
    ]);
    $unidad = PlantaVendida::create([
        'venta_id' => $venta->id,
        'user_id' => $cliente->id,
        'sensor_id' => $sensor->id,
    ]);

    return [$sensor, $unidad];
}

test('no registra la ruta show de sensores porque el controller no la implementa', function () {
    expect(Route::has('sensores.show'))->toBeFalse();
});

test('listado de sensores muestra estado vacío y mensaje de éxito', function () {
    $administrador = User::factory()->create(['rol' => 'administrador']);

    $this->actingAs($administrador)
        ->get(route('sensores.index'))
        ->assertOk()
        ->assertSee('No hay sensores registrados todavía.');

    Sensor::create([
        'identificador_fisico' => 'SENSOR-MENSAJE-001',
        'estado' => 'activo',
    ]);

    $this->withSession(['success' => 'Sensor creado correctamente.'])
        ->get(route('sensores.index'))
        ->assertOk()
        ->assertSee('Sensor creado correctamente.')
        ->assertSee('SENSOR-MENSAJE-001');
});

test('listado de sensores muestra error al intentar eliminar un sensor asignado', function () {
    $administrador = User::factory()->create(['rol' => 'administrador']);

    $this->actingAs($administrador)
        ->withSession(['errors' => new \Illuminate\Support\MessageBag([
            'sensor' => 'No se puede eliminar un sensor asignado a una planta vendida.',
        ])])
        ->get(route('sensores.index'))
        ->assertOk()
        ->assertSee('No se pudo completar la operación:')
        ->assertSee('No se puede eliminar un sensor asignado a una planta vendida.');
});

test('botón de eliminación solicita confirmación', function () {
    $administrador = User::factory()->create(['rol' => 'administrador']);
    Sensor::create([
        'identificador_fisico' => 'SENSOR-CONFIRMAR-001',
        'estado' => 'activo',
    ]);

    $this->actingAs($administrador)
        ->get(route('sensores.index'))
        ->assertOk()
        ->assertSee("¿Estás seguro de eliminar este sensor?", false);
});

test('administrador y vendedor pueden usar el CRUD de sensores pero cliente no', function () {
    foreach (['administrador', 'vendedor'] as $rol) {
        $usuario = User::factory()->create(['rol' => $rol]);

        $this->actingAs($usuario)
            ->get(route('sensores.index'))
            ->assertOk();

        $this->get(route('sensores.create'))
            ->assertOk();
    }

    $cliente = User::factory()->create(['rol' => 'cliente']);
    $this->actingAs($cliente)
        ->get(route('sensores.index'))
        ->assertForbidden();
});

test('crea sensor con identificador único y muestra confirmación', function () {
    $vendedor = User::factory()->create(['rol' => 'vendedor']);

    $this->actingAs($vendedor)
        ->post(route('sensores.store'), [
            'identificador_fisico' => 'ESP32-CRUD-001',
            'estado' => 'activo',
        ])
        ->assertRedirect(route('sensores.index'))
        ->assertSessionHas('success', 'Sensor creado correctamente.');

    $this->assertDatabaseHas('sensores', [
        'identificador_fisico' => 'ESP32-CRUD-001',
        'estado' => 'activo',
    ]);

    $this->from(route('sensores.create'))
        ->post(route('sensores.store'), [
            'identificador_fisico' => 'ESP32-CRUD-001',
            'estado' => 'activo',
        ])
        ->assertRedirect(route('sensores.create'))
        ->assertSessionHasErrors('identificador_fisico');
});

test('actualiza estado y permite mantener el identificador actual', function () {
    $administrador = User::factory()->create(['rol' => 'administrador']);
    $sensor = Sensor::create([
        'identificador_fisico' => 'ESP32-CRUD-002',
        'estado' => 'activo',
    ]);

    $this->actingAs($administrador)
        ->put(route('sensores.update', $sensor->id), [
            'identificador_fisico' => 'ESP32-CRUD-002',
            'estado' => 'inactivo',
        ])
        ->assertRedirect(route('sensores.index'))
        ->assertSessionHas('success', 'Sensor actualizado correctamente.');

    $this->assertDatabaseHas('sensores', [
        'id' => $sensor->id,
        'identificador_fisico' => 'ESP32-CRUD-002',
        'estado' => 'inactivo',
    ]);
});

test('rechaza actualizar un sensor con el identificador de otro sensor', function () {
    $administrador = User::factory()->create(['rol' => 'administrador']);
    $sensorExistente = Sensor::create([
        'identificador_fisico' => 'ESP32-CRUD-003',
        'estado' => 'activo',
    ]);
    $sensorAEditar = Sensor::create([
        'identificador_fisico' => 'ESP32-CRUD-004',
        'estado' => 'activo',
    ]);

    $this->from(route('sensores.edit', $sensorAEditar->id))
        ->actingAs($administrador)
        ->put(route('sensores.update', $sensorAEditar->id), [
            'identificador_fisico' => $sensorExistente->identificador_fisico,
            'estado' => 'inactivo',
        ])
        ->assertRedirect(route('sensores.edit', $sensorAEditar->id))
        ->assertSessionHasErrors('identificador_fisico');

    $this->assertDatabaseHas('sensores', [
        'id' => $sensorAEditar->id,
        'identificador_fisico' => 'ESP32-CRUD-004',
        'estado' => 'activo',
    ]);
});

test('elimina sensor libre y conserva el asignado con un error visible', function () {
    $vendedor = User::factory()->create(['rol' => 'vendedor']);
    $sensorLibre = Sensor::create([
        'identificador_fisico' => 'SENSOR-LIBRE-CRUD-001',
        'estado' => 'activo',
    ]);

    $this->actingAs($vendedor)
        ->delete(route('sensores.destroy', $sensorLibre->id))
        ->assertRedirect(route('sensores.index'))
        ->assertSessionHas('success', 'Sensor eliminado correctamente.');

    $this->assertDatabaseMissing('sensores', ['id' => $sensorLibre->id]);

    [$sensorAsignado, $unidad] = crearSensorAsignado();

    $this->from(route('sensores.index'))
        ->delete(route('sensores.destroy', $sensorAsignado->id))
        ->assertRedirect(route('sensores.index'))
        ->assertSessionHasErrors('sensor');

    $this->assertDatabaseHas('sensores', ['id' => $sensorAsignado->id]);
    $this->assertDatabaseHas('plantas_vendidas', [
        'id' => $unidad->id,
        'sensor_id' => $sensorAsignado->id,
    ]);

    $this->get(route('sensores.index'))
        ->assertOk()
        ->assertSee('No se puede eliminar un sensor asignado a una planta vendida.');
});
