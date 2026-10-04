<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('vendedor inicia sesión y llega al CRUD compartido con el administrador', function () {
    $user = User::factory()->create([
        'email' => 'vendedor@example.com',
        'password' => Hash::make('password'),
        'rol' => 'vendedor',
    ]);

    $response = $this->withSession(['url.intended' => route('home')])
        ->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('sensores.index'));
    $this->assertAuthenticatedAs($user);

    $this->get(route('sensores.index'))
        ->assertOk()
        ->assertViewIs('sensores.index');
});

test('vendedor recién registrado llega al CRUD compartido', function () {
    $response = $this->withSession(['url.intended' => route('home')])
        ->post(route('register'), [
            'name' => 'Vendedor',
            'email' => 'nuevo-vendedor@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'rol' => 'vendedor',
        ]);

    $response->assertRedirect(route('sensores.index'));
    $this->assertAuthenticated();
});

test('administrador conserva su redirección al CRUD después del login', function () {
    $user = User::factory()->create([
        'email' => 'administrador@example.com',
        'password' => Hash::make('password'),
        'rol' => 'administrador',
    ]);

    $response = $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('sensores.index'));
    $this->assertAuthenticatedAs($user);
});

test('cliente conserva su redirección al home después del login', function () {
    $user = User::factory()->create([
        'email' => 'cliente@example.com',
        'password' => Hash::make('password'),
        'rol' => 'cliente',
    ]);

    $response = $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('home'));
    $this->assertAuthenticatedAs($user);
});
