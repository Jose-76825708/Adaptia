<?php

use App\Models\Planta;
use App\Models\User;
use App\Services\CloudinaryImageService;
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
        'tamano_adulto' => 'mediana',
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
    $cloudinaryImages = Mockery::mock(CloudinaryImageService::class);
    $cloudinaryImages->shouldReceive('upload')->twice()->andReturn(
        ['secure_url' => 'https://res.cloudinary.com/demo/image/upload/planta-1.jpg', 'public_id' => 'adaptia/plantas/planta-1'],
        ['secure_url' => 'https://res.cloudinary.com/demo/image/upload/planta-2.jpg', 'public_id' => 'adaptia/plantas/planta-2'],
    );
    $cloudinaryImages->shouldReceive('delete')->once()->with('adaptia/plantas/planta-1');
    $cloudinaryImages->shouldReceive('delete')->once()->with('adaptia/plantas/planta-2');
    $this->app->instance(CloudinaryImageService::class, $cloudinaryImages);

    $administrador = User::factory()->create(['rol' => 'administrador']);
    $planta = Planta::factory()->create(['imagen' => 'plantas/antigua.jpg']);
    Storage::disk('public')->put($planta->imagen, 'imagen antigua');

    $this->actingAs($administrador)
        ->get(route('plantas.show', $planta))
        ->assertOk()
        ->assertViewIs('plantas.show')
        ->assertViewHas('find', fn ($value) => $value->is($planta));
    $this->get(route('plantas.show', $planta))->assertSee($planta->imagen_url);
    $this->get(route('plantas.edit', $planta))
        ->assertOk()
        ->assertSee('enctype="multipart/form-data"', false)
        ->assertSee('name="imagen"', false);

    $this->put(route('plantas.update', $planta), coberturaPayloadPlanta(
        $planta,
        UploadedFile::fake()->create('nueva.jpg', 10, 'image/jpeg'),
    ))->assertRedirect(route('plantas.index'));

    $planta->refresh();
    expect($planta->nombre)->toBe('Planta editada')
        ->and($planta->imagen)->toBe('https://res.cloudinary.com/demo/image/upload/planta-1.jpg')
        ->and($planta->imagen_public_id)->toBe('adaptia/plantas/planta-1')
        ->and($planta->imagen_ruta_local)->toBe('plantas/antigua.jpg')
        ->and($planta->imagen_url)->toBe('https://res.cloudinary.com/demo/image/upload/planta-1.jpg');
    Storage::disk('public')->assertExists('plantas/antigua.jpg');

    $this->put(route('plantas.update', $planta), coberturaPayloadPlanta(
        $planta,
        UploadedFile::fake()->create('sin-anterior.jpg', 10, 'image/jpeg'),
    ))->assertRedirect(route('plantas.index'));
    expect($planta->fresh()->imagen_public_id)->toBe('adaptia/plantas/planta-2');

    $this->delete(route('plantas.destroy', $planta))
        ->assertRedirect(route('plantas.index'));
    expect(Planta::find($planta->id))->toBeNull();
});

test('reporta cuando la planta se elimina pero Cloudinary no puede borrar su imagen', function () {
    $cloudinaryImages = Mockery::mock(CloudinaryImageService::class);
    $cloudinaryImages->shouldReceive('delete')->once()->with('adaptia/plantas/huella')
        ->andThrow(new \App\Exceptions\CloudinaryImageException('No se pudo eliminar la imagen de Cloudinary.'));
    $this->app->instance(CloudinaryImageService::class, $cloudinaryImages);

    $administrador = User::factory()->create(['rol' => 'administrador']);
    $planta = Planta::factory()->create([
        'imagen' => 'https://res.cloudinary.com/demo/image/upload/huella.jpg',
        'imagen_public_id' => 'adaptia/plantas/huella',
    ]);

    $this->actingAs($administrador)
        ->delete(route('plantas.destroy', $planta))
        ->assertRedirect(route('plantas.index'))
        ->assertSessionHas('warning');

    expect(Planta::find($planta->id))->toBeNull();
});
