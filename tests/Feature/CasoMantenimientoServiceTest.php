<?php

use App\Models\CasoMantenimiento;
use App\Models\User;
use App\Services\CasoMantenimientoService;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::dropIfExists('casos_mantenimiento');
    Schema::dropIfExists('users');

    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->timestamp('email_verified_at')->nullable();
        $table->string('password');
        $table->string('remember_token', 100)->nullable();
        $table->string('rol')->nullable();
        $table->timestamps();
    });

    Schema::create('casos_mantenimiento', function (Blueprint $table) {
        $table->id();
        $table->string('codigo')->unique();
        $table->foreignId('solicitante_id')->constrained('users');
        $table->string('area_verde', 120);
        $table->text('descripcion');
        $table->date('fecha_deseada')->nullable();
        $table->text('observaciones')->nullable();
        $table->string('tipo_mantenimiento');
        $table->string('urgencia');
        $table->string('estado')->default('abierto');
        $table->timestamps();
    });
});

test('abrir crea un caso con codigo unico y estado abierto', function () {
    $user = User::factory()->create();
    $service = new CasoMantenimientoService;

    $caso = $service->abrir($user->id, [
        'area_verde' => 'Jardín principal',
        'descripcion' => 'Necesito riego urgente para las plantas del jardín.',
        'fecha_deseada' => '2026-11-10',
        'observaciones' => 'Falta humedad en algunas zonas.',
    ], Carbon::parse('2026-11-03'));

    expect($caso)->toBeInstanceOf(CasoMantenimiento::class)
        ->and($caso->codigo)->toMatch('/^MAN-\d{4}-\d{5}$/')
        ->and($caso->estado)->toBe('abierto')
        ->and($caso->solicitante_id)->toBe($user->id)
        ->and(CasoMantenimiento::count())->toBe(1);
});

test('abrir ignora user_id y estado provenientes del arreglo de entrada', function () {
    $user = User::factory()->create();
    $service = new CasoMantenimientoService;

    $caso = $service->abrir($user->id, [
        'user_id' => 999,
        'estado' => 'cerrado',
        'area_verde' => 'Patio central',
        'descripcion' => 'Necesito poda en las ramas laterales del patio.',
        'fecha_deseada' => null,
        'observaciones' => 'Hay ramas largas.',
    ], Carbon::parse('2026-11-03'));

    expect($caso->solicitante_id)->toBe($user->id)
        ->and($caso->estado)->toBe('abierto')
        ->and($caso->area_verde)->toBe('Patio central');
});

test('abrir no persiste cuando la solicitud falla en validacion', function () {
    $user = User::factory()->create();
    $service = new CasoMantenimientoService;

    expect(fn () => $service->abrir($user->id, [
        'area_verde' => 'Ja',
        'descripcion' => 'Corto',
        'fecha_deseada' => '2026-10-30',
        'observaciones' => null,
    ], Carbon::parse('2026-11-03')))->toThrow(\Illuminate\Validation\ValidationException::class);

    expect(CasoMantenimiento::count())->toBe(0);
});

test('abrir usa el solicitante_id recibido como parametro', function () {
    $userUno = User::factory()->create();
    $userDos = User::factory()->create();
    $service = new CasoMantenimientoService;

    $caso = $service->abrir($userDos->id, [
        'area_verde' => 'Jardín lateral',
        'descripcion' => 'Necesito limpiar exceso de hojas secas del jardín lateral.',
        'fecha_deseada' => '2026-11-12',
        'observaciones' => 'Se acumulan hojas en la zona.',
    ], Carbon::parse('2026-11-03'));

    expect($caso->solicitante_id)->toBe($userDos->id)
        ->and($caso->solicitante_id)->not->toBe($userUno->id);
});

test('cambiarEstado actualiza el estado cuando la transicion es valida', function () {
    $user = User::factory()->create();
    $service = new CasoMantenimientoService;

    $caso = $service->abrir($user->id, [
        'area_verde' => 'Invernadero',
        'descripcion' => 'Necesito revisión para controlar plagas en el invernadero.',
        'fecha_deseada' => '2026-11-05',
        'observaciones' => 'Hay insectos en varias plantas.',
    ], Carbon::parse('2026-11-03'));

    $casoActualizado = $service->cambiarEstado($caso, 'diagnosticado');

    expect($casoActualizado->estado)->toBe('diagnosticado')
        ->and($caso->fresh()->estado)->toBe('diagnosticado');
});

test('cambiarEstado lanza excepcion cuando la transicion no es valida', function () {
    $user = User::factory()->create();
    $service = new CasoMantenimientoService;

    $caso = $service->abrir($user->id, [
        'area_verde' => 'Jerárquico',
        'descripcion' => 'Necesito revisión general del área de cultivo.',
        'fecha_deseada' => '2026-11-08',
        'observaciones' => 'Se observa un cambio en el crecimiento.',
    ], Carbon::parse('2026-11-03'));

    expect(fn () => $service->cambiarEstado($caso, 'cerrado'))->toThrow(\InvalidArgumentException::class, 'La transición de');
    expect($caso->fresh()->estado)->toBe('abierto');
});

test('abrir guarda observaciones como null cuando no se envian', function () {
    $user = User::factory()->create();
    $service = new CasoMantenimientoService;

    $caso = $service->abrir($user->id, [
        'area_verde' => 'Jardín posterior',
        'descripcion' => 'Necesito revisión general del jardín posterior.',
        'fecha_deseada' => '2026-11-12',
    ], Carbon::parse('2026-11-03'));

    expect($caso->fresh()->observaciones)->toBeNull();
});

test('abrir conserva el contenido de observaciones cuando se recibe texto', function () {
    $user = User::factory()->create();
    $service = new CasoMantenimientoService;

    $caso = $service->abrir($user->id, [
        'area_verde' => 'Jardín posterior',
        'descripcion' => 'Necesito revisión general del jardín posterior.',
        'fecha_deseada' => '2026-11-12',
        'observaciones' => 'Falta humedad en la zona sur.',
    ], Carbon::parse('2026-11-03'));

    expect($caso->fresh()->observaciones)->toBe('Falta humedad en la zona sur.');
});
