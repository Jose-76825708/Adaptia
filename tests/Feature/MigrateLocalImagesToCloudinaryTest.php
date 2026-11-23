<?php

use App\Models\CasoMantenimiento;
use App\Models\EvidenciaCaso;
use App\Models\Planta;
use App\Services\CloudinaryImageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('migra rutas locales a Cloudinary conservando las rutas y archivos originales y permite reejecucion', function () {
    Storage::fake('public');

    $planta = Planta::factory()->create(['imagen' => 'plantas/monstera.jpg']);
    Storage::disk('public')->put('plantas/monstera.jpg', 'planta');

    $caso = CasoMantenimiento::factory()->create(['estado' => 'abierto']);
    $evidencia = EvidenciaCaso::factory()->create([
        'caso_id' => $caso->id,
        'tipo' => 'foto',
        'contenido' => null,
        'ruta' => 'evidencias/'.$caso->id.'/riego.jpg',
    ]);
    Storage::disk('public')->put($evidencia->ruta, 'evidencia');

    $cloudinaryImages = Mockery::mock(CloudinaryImageService::class);
    $cloudinaryImages->shouldReceive('upload')->twice()->andReturn(
        ['secure_url' => 'https://res.cloudinary.com/demo/image/upload/monstera.jpg', 'public_id' => 'adaptia/plantas/monstera'],
        ['secure_url' => 'https://res.cloudinary.com/demo/image/upload/riego.jpg', 'public_id' => 'adaptia/evidencias/'.$caso->id.'/riego'],
    );
    $this->app->instance(CloudinaryImageService::class, $cloudinaryImages);

    $this->artisan('cloudinary:migrate-local-images --dry-run')
        ->assertSuccessful()
        ->expectsOutputToContain('se migraría');

    expect($planta->fresh()->imagen)->toBe('plantas/monstera.jpg')
        ->and($evidencia->fresh()->ruta)->toBe('evidencias/'.$caso->id.'/riego.jpg');

    $this->artisan('cloudinary:migrate-local-images --batch=1')
        ->assertSuccessful()
        ->expectsOutputToContain('Migradas: 2; omitidas: 0; errores: 0.');

    expect($planta->fresh()->imagen)->toBe('https://res.cloudinary.com/demo/image/upload/monstera.jpg')
        ->and($planta->fresh()->imagen_public_id)->toBe('adaptia/plantas/monstera')
        ->and($planta->fresh()->imagen_ruta_local)->toBe('plantas/monstera.jpg')
        ->and($evidencia->fresh()->ruta)->toBe('https://res.cloudinary.com/demo/image/upload/riego.jpg')
        ->and($evidencia->fresh()->public_id)->toBe('adaptia/evidencias/'.$caso->id.'/riego')
        ->and($evidencia->fresh()->ruta_local)->toBe('evidencias/'.$caso->id.'/riego.jpg');
    Storage::disk('public')->assertExists('plantas/monstera.jpg');
    Storage::disk('public')->assertExists('evidencias/'.$caso->id.'/riego.jpg');

    $this->artisan('cloudinary:migrate-local-images')
        ->assertSuccessful()
        ->expectsOutputToContain('Migradas: 0; omitidas: 0; errores: 0.');
});
