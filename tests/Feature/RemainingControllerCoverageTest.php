<?php

use App\Http\Controllers\LecturaSensorController;
use App\Http\Controllers\PlantaVendidaController;
use App\Http\Controllers\VentaController;
use App\Http\Requests\StoreLecturaSensorRequest;
use App\Models\Planta;
use App\Models\PlantaVendida;
use App\Models\Sensor;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

uses(RefreshDatabase::class);

test('muestra el formulario de login y rechaza credenciales incorrectas', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertViewIs('auth.login');

    $this->from(route('login'))
        ->post(route('login'), [
            'email' => 'desconocido@example.com',
            'password' => 'incorrecta',
        ])
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors([
            'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
        ])
        ->assertSessionHas('_old_input.email', 'desconocido@example.com');
});

test('los controladores rechazan una llamada interna sin usuario autenticado', function () {
    $sensor = Sensor::create([
        'identificador_fisico' => 'ESP32-CONTROLLER-CHECK',
        'estado' => 'activo',
    ]);
    $cliente = User::factory()->create(['rol' => 'cliente']);
    $vendedor = User::factory()->create(['rol' => 'vendedor']);
    $venta = Venta::create([
        'user_id' => $cliente->id,
        'vendedor_id' => $vendedor->id,
        'planta_id' => Planta::factory()->create()->id,
        'cantidad' => 1,
    ]);
    $unidad = PlantaVendida::create([
        'venta_id' => $venta->id,
        'user_id' => $cliente->id,
    ]);
    $requestAsignacion = Request::create('/plantas-vendidas/1/sensor', 'POST', [
        'sensor_id' => $sensor->id,
    ]);
    $requestVenta = Request::create('/ventas', 'POST', [
        'user_id' => $cliente->id,
        'planta_id' => Planta::factory()->create()->id,
        'cantidad' => 1,
    ]);

    expect(fn () => app(PlantaVendidaController::class)->asignarSensor(
        $requestAsignacion,
        $unidad,
    ))->toThrow(HttpException::class)
        ->and(fn () => app(VentaController::class)->store($requestVenta))
        ->toThrow(HttpException::class);
});

test('el controlador de lecturas detecta la ausencia del sensor autenticado', function () {
    expect(fn () => app(LecturaSensorController::class)->store(new StoreLecturaSensorRequest))
        ->toThrow(LogicException::class, 'El middleware de credenciales no proporcionó un sensor autenticado.');
});
