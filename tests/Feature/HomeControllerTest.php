<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('usuario no cliente ve la página principal sin cargar el panel del cliente', function () {
    $administrador = User::factory()->create(['rol' => 'administrador']);

    $this->actingAs($administrador)
        ->get(route('home'))
        ->assertOk()
        ->assertViewIs('home');
});
