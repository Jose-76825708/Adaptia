<?php

use App\Http\Controllers\CasoMantenimientoController;
use App\Http\Controllers\PlanMantenimientoController;
use App\Models\CasoMantenimiento;
use App\Models\User;
use App\Services\CasoMantenimientoService;
use App\Services\GeneradorPlanMantenimientoService;
use App\Services\OrdenadorTareasPlanService;
use App\Services\PlanMantenimientoService;
use Illuminate\Http\Request;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('CasoController rechaza etapa desconocida con 404', function () {
    $admin = User::factory()->create(['rol'=>'administrador']);
    $caso = CasoMantenimiento::factory()->create(['solicitante_id'=>$admin->id]);

    $this->actingAs($admin)
        ->get(route('mantenimiento.casos.show.etapa', ['caso'=>$caso,'etapa'=>'desconocida']))
        ->assertNotFound();
});

it('CasoController carga cada etapa permitida', function () {
    $admin = User::factory()->create(['rol'=>'administrador']);
    $caso = CasoMantenimiento::factory()->create(['solicitante_id'=>$admin->id]);

    foreach (['diagnostico','plan','recursos','abastecimiento','ejecucion','cierre'] as $etapa) {
        $this->actingAs($admin)
            ->get(route('mantenimiento.casos.show.etapa', ['caso'=>$caso,'etapa'=>$etapa]))
            ->assertOk()
            ->assertViewIs('mantenimiento.casos.etapa');
    }
});

it('PlanController redirige con error al estado incorrecto', function () {
    $admin = User::factory()->create(['rol'=>'administrador']);
    $caso = CasoMantenimiento::factory()->create([
        'solicitante_id'=>$admin->id,
        'estado'=>'abierto',
    ]);

    $service = new PlanMantenimientoService(new CasoMantenimientoService(), new GeneradorPlanMantenimientoService(), new OrdenadorTareasPlanService());
    $controller = new PlanMantenimientoController();
    $request = Request::create('/mantenimiento/casos/'.$caso->id.'/plan/generar','POST');
    $request->setUserResolver(fn () => $admin);

    $response = $controller->generar($request,$caso,$service);

    expect($response->getTargetUrl())->toContain('/mantenimiento/casos/'.$caso->id.'/etapa/plan');
});
