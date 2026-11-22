<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('administrador ve el dashboard operativo sin cargar el panel del cliente', function () {
    $administrador = User::factory()->create(['rol' => 'administrador']);

    $this->actingAs($administrador)
        ->get(route('home'))
        ->assertOk()
        ->assertViewIs('dashboard.administrador');
});
