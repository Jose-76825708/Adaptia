<?php

use App\Models\Planta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function coberturaPayloadPlanta(Planta $planta, ?UploadedFile $imagen = null): array
{
    return [
        'tipo_planta_id' => $planta->tipo_planta_id,
        'nombre' => 'Planta editada',
        'precio' => 29,
        'luz_requerida' => 'media',
        'tamaño_adulto' => 'mediana',
        'tipo_ambiente' => 'interiores',
        'frecuencia_riego' => 'semanal',
        'estetica' => 'follaje',
        'nivel_cuidado' => 'principiante',
        'stock_actual' => 6,
        'stock_minimo' => 1,
        'imagen' => $imagen,
    ];
}

test('consulta, actualiza imágenes y elimina una planta conservando el inventario', function () {
    Storage::fake('public');
    $administrador = User::factory()->create(['rol' => 'administrador']);
    $planta = Planta::factory()->create(['imagen' => 'plantas/antigua.jpg']);
    Storage::disk('public')->put($planta->imagen, 'imagen antigua');

    $this->actingAs($administrador)
        ->get(route('plantas.show', $planta))
        ->assertOk()
        ->assertViewIs('plantas.show')
        ->assertViewHas('find', fn ($value) => $value->is($planta));

    $this->put(route('plantas.update', $planta), coberturaPayloadPlanta(
        $planta,
        UploadedFile::fake()->create('nueva.jpg', 10, 'image/jpeg'),
    ))->assertRedirect(route('plantas.index'));

    $planta->refresh();
    expect($planta->nombre)->toBe('Planta editada')
        ->and($planta->imagen)->not->toBe('plantas/antigua.jpg');
    Storage::disk('public')->assertMissing('plantas/antigua.jpg');
    Storage::disk('public')->assertExists($planta->imagen);

    $planta->update(['imagen' => '']);
    $this->put(route('plantas.update', $planta), coberturaPayloadPlanta(
        $planta,
        UploadedFile::fake()->create('sin-anterior.jpg', 10, 'image/jpeg'),
    ))->assertRedirect(route('plantas.index'));
    Storage::disk('public')->assertExists($planta->fresh()->imagen);

    $this->delete(route('plantas.destroy', $planta))
        ->assertRedirect(route('plantas.index'));
    expect(Planta::find($planta->id))->toBeNull();
});
