<?php

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('las rutas post administrativas estan protegidas', function () {
    $rutas = [
        '/mantenimiento/casos/1/diagnostico/guardar',
        '/mantenimiento/casos/1/diagnostico/fotos/1/eliminar',
        '/mantenimiento/casos/1/plan/generar',
        '/mantenimiento/casos/1/requisicion/recibir',
        '/mantenimiento/casos/1/abastecimiento/preparar',
        '/mantenimiento/casos/1/ejecucion/evento',
        '/mantenimiento/casos/1/cierre/cerrar',
        '/mantenimiento/casos/1/cierre/reabrir',
    ];

    foreach ($rutas as $uri) {
        $this->post($uri)->assertRedirect(route('login'));
    }
});

it('el informe requiere autenticacion', function () {
    $this->get('/mantenimiento/casos/1/informe')->assertRedirect(route('login'));
});

it('las rutas eliminadas no existen', function () {
    $uris = collect(app('router')->getRoutes()->getRoutes())->map(fn ($ruta) => $ruta->uri())->all();

    expect($uris)->not->toContain('mantenimiento/casos/{caso}/plan/guardar')
        ->and($uris)->not->toContain('mantenimiento/casos/{caso}/recursos/verificar')
        ->and($uris)->not->toContain('mantenimiento/casos/{caso}/cierre/evaluar')
        ->and($uris)->not->toContain('mantenimiento/casos/{caso}/cierre/guardar');
});

it('el detalle administrativo existe con auth role', function () {
    $admin = App\Models\User::factory()->create(['rol'=>'administrador']);
    $caso = App\Models\CasoMantenimiento::factory()->create(['solicitante_id'=>$admin->id]);

    $this->actingAs($admin)
        ->get(route('mantenimiento.casos.show',$caso))
        ->assertOk()
        ->assertSee('Flujo de mantenimiento');
});
