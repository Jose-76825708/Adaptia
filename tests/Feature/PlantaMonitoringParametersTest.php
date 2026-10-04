<?php

use App\Models\Planta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function validPlantaPayload(array $overrides = []): array
{
    return array_merge([
        'tipo_planta_id' => \App\Models\TipoPlanta::factory()->create()->id,
        'nombre' => 'Planta de prueba',
        'precio' => 25,
        'luz_requerida' => 'media',
        'tamaño_adulto' => 'mediana',
        'tipo_ambiente' => 'interiores',
        'frecuencia_riego' => 'semanal',
        'estetica' => 'follaje',
        'nivel_cuidado' => 'principiante',
        'stock_actual' => 5,
        'stock_minimo' => 1,
        'imagen' => UploadedFile::fake()->create('planta.jpg', 10, 'image/jpeg'),
    ], $overrides);
}

test('formularios crear y editar muestran rangos de monitoreo IoT', function () {
    $administrador = User::factory()->create(['rol' => 'administrador']);
    $planta = Planta::factory()->create([
        'humedad_suelo_min' => 20,
        'humedad_suelo_max' => 60,
        'temperatura_min' => 15,
        'temperatura_max' => 30,
        'humedad_ambiental_min' => 35,
        'humedad_ambiental_max' => 75,
        'luz_min' => 200,
        'luz_max' => 1200,
    ]);

    $this->actingAs($administrador)
        ->get(route('plantas.create'))
        ->assertOk()
        ->assertSee('Parámetros de Monitoreo IoT')
        ->assertSee('name="humedad_suelo_min"', false)
        ->assertSee('name="temperatura_max"', false)
        ->assertSee('name="humedad_ambiental_min"', false)
        ->assertSee('name="luz_max"', false);

    $this->get(route('plantas.edit', $planta))
        ->assertOk()
        ->assertSee('name="humedad_suelo_min"', false)
        ->assertSee('name="temperatura_max"', false)
        ->assertSee('name="humedad_ambiental_max"', false)
        ->assertSee('name="luz_max"', false);
});

test('crea y actualiza rangos de monitoreo para una planta', function () {
    Storage::fake('public');
    $administrador = User::factory()->create(['rol' => 'administrador']);
    $payload = validPlantaPayload([
        'humedad_suelo_min' => 20,
        'humedad_suelo_max' => 60,
        'temperatura_min' => 15,
        'temperatura_max' => 30,
        'humedad_ambiental_min' => 35,
        'humedad_ambiental_max' => 75,
        'luz_min' => 200,
        'luz_max' => 1200,
    ]);

    $this->actingAs($administrador)
        ->post(route('plantas.store'), $payload)
        ->assertRedirect(route('plantas.index'));

    $planta = Planta::query()->where('nombre', 'Planta de prueba')->firstOrFail();
    expect((float) $planta->humedad_suelo_min)->toBe(20.0)
        ->and((float) $planta->humedad_suelo_max)->toBe(60.0)
        ->and((float) $planta->temperatura_min)->toBe(15.0)
        ->and((float) $planta->temperatura_max)->toBe(30.0)
        ->and((float) $planta->humedad_ambiental_min)->toBe(35.0)
        ->and((float) $planta->humedad_ambiental_max)->toBe(75.0)
        ->and((float) $planta->luz_min)->toBe(200.0)
        ->and((float) $planta->luz_max)->toBe(1200.0);

    $updatePayload = validPlantaPayload([
        'tipo_planta_id' => $planta->tipo_planta_id,
        'nombre' => $planta->nombre,
        'humedad_suelo_min' => 25,
        'humedad_suelo_max' => 65,
        'temperatura_min' => 16,
        'temperatura_max' => 29,
        'humedad_ambiental_min' => 40,
        'humedad_ambiental_max' => 70,
        'luz_min' => 250,
        'luz_max' => 1000,
    ]);
    unset($updatePayload['imagen']);

    $this->put(route('plantas.update', $planta), $updatePayload)
        ->assertRedirect(route('plantas.index'));

    expect((float) $planta->fresh()->humedad_suelo_min)->toBe(25.0)
        ->and((float) $planta->fresh()->luz_max)->toBe(1000.0);
});

test('los rangos son opcionales y sus límites deben ser coherentes', function () {
    Storage::fake('public');
    $administrador = User::factory()->create(['rol' => 'administrador']);

    $this->actingAs($administrador)
        ->post(route('plantas.store'), validPlantaPayload())
        ->assertRedirect(route('plantas.index'));

    expect(Planta::query()->where('nombre', 'Planta de prueba')->firstOrFail()->humedad_suelo_min)->toBeNull();

    $this->from(route('plantas.create'))
        ->post(route('plantas.store'), validPlantaPayload([
            'nombre' => 'Planta inválida',
            'humedad_suelo_min' => 70,
            'humedad_suelo_max' => 40,
        ]))
        ->assertRedirect(route('plantas.create'))
        ->assertSessionHasErrors(['humedad_suelo_min', 'humedad_suelo_max']);

    $this->from(route('plantas.create'))
        ->post(route('plantas.store'), validPlantaPayload([
            'nombre' => 'Planta incompleta',
            'luz_min' => 100,
        ]))
        ->assertRedirect(route('plantas.create'))
        ->assertSessionHasErrors('luz_max');

    expect(Planta::query()->whereIn('nombre', ['Planta inválida', 'Planta incompleta'])->count())->toBe(0);
});
