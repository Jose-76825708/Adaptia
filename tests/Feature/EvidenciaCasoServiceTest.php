<?php

use App\Models\CasoMantenimiento;
use App\Models\LecturaSensor;
use App\Models\Planta;
use App\Models\PlantaVendida;
use App\Models\Sensor;
use App\Models\TipoPlanta;
use App\Models\User;
use App\Models\Venta;
use App\Services\EvidenciaCasoService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new EvidenciaCasoService();
    $this->cliente = User::factory()->create(['rol' => 'cliente']);
    $this->autor = User::factory()->create(['rol' => 'administrador']);
    $this->caso = CasoMantenimiento::factory()->create([
        'solicitante_id' => $this->cliente->id,
        'estado' => 'abierto',
        'area_verde' => 'Jardín central',
    ]);
});

test('registra una nota valida para un caso abierto', function () {
    $evidencia = $this->service->registrarNota($this->caso, $this->autor->id, 'Necesito revisión del sistema de riego.');

    expect($evidencia->tipo)->toBe('nota')
        ->and($evidencia->contenido)->toBe('Necesito revisión del sistema de riego.')
        ->and($evidencia->caso_id)->toBe($this->caso->id)
        ->and($evidencia->autor_id)->toBe($this->autor->id);
});

test('rechaza una nota si el caso esta cerrado', function () {
    $this->caso->update(['estado' => 'cerrado']);

    expect(fn () => $this->service->registrarNota($this->caso, $this->autor->id, 'Nota de prueba'))->toThrow(\Illuminate\Validation\ValidationException::class);
});

test('guarda una foto valida en storage publico y guarda la descripcion como texto', function () {
    Storage::fake('public');
    $archivo = UploadedFile::fake()->create('inspeccion.jpg', 100, 'image/jpeg');

    $evidencia = $this->service->registrarFoto($this->caso, $this->autor->id, $archivo, 'Vista frontal del área');

    expect($evidencia->tipo)->toBe('foto')
        ->and($evidencia->descripcion)->toBe('Vista frontal del área')
        ->and($evidencia->ruta)->not->toBeNull()
        ->and(Storage::disk('public')->exists($evidencia->ruta))->toBeTrue();
});

test('rechaza una foto con mime no permitido', function () {
    Storage::fake('public');
    $archivo = UploadedFile::fake()->create('archivo.svg', 100, 'image/svg+xml');

    expect(fn () => $this->service->registrarFoto($this->caso, $this->autor->id, $archivo, 'Texto'))->toThrow(\Illuminate\Validation\ValidationException::class);
});

test('compila el paquete de evidencia con caso, evidencias y lecturas del solicitante', function () {
    $tipo = TipoPlanta::factory()->create();
    $planta = Planta::factory()->create(['tipo_planta_id' => $tipo->id]);
    $vendedor = User::factory()->create(['rol' => 'vendedor']);
    $venta = Venta::create([
        'user_id' => $this->cliente->id,
        'vendedor_id' => $vendedor->id,
        'planta_id' => $planta->id,
        'cantidad' => 1,
        'fecha' => now(),
    ]);
    $sensor = Sensor::create(['identificador_fisico' => 'SEN-'.now()->timestamp, 'estado' => 'activo']);
    $plantaVendida = PlantaVendida::create([
        'venta_id' => $venta->id,
        'user_id' => $this->cliente->id,
        'sensor_id' => $sensor->id,
    ]);
    LecturaSensor::create([
        'planta_vendida_id' => $plantaVendida->id,
        'humedad_suelo' => 40,
        'temperatura' => 22,
        'humedad_ambiental' => 60,
        'luz' => 750,
        'fecha_hora' => now()->subDay(),
    ]);
    LecturaSensor::create([
        'planta_vendida_id' => $plantaVendida->id,
        'humedad_suelo' => 45,
        'temperatura' => 25,
        'humedad_ambiental' => 65,
        'luz' => 800,
        'fecha_hora' => now(),
    ]);

    $this->service->registrarNota($this->caso, $this->autor->id, 'Se requiere revisión del riego.');

    $paquete = $this->service->compilar($this->caso, Carbon::now());

    expect($paquete)->toHaveKeys(['caso', 'evidencias', 'sensores'])
        ->and($paquete['caso']['id'])->toBe($this->caso->id)
        ->and($paquete['caso']['codigo'])->toBe($this->caso->codigo)
        ->and($paquete['evidencias']['notas'])->toHaveCount(1)
        ->and($paquete['sensores'])->toHaveCount(1)
        ->and($paquete['sensores'][0]['variables']['humedad_suelo']['ultima'])->toBe(45.0);
});
