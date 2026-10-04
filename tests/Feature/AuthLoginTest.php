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

test('registro público asigna rol cliente e ignora intentos de solicitar roles privilegiados', function () {
    foreach (['vendedor', 'administrador'] as $rolSolicitado) {
        $email = "registro-{$rolSolicitado}@example.com";

        $response = $this->post(route('register'), [
            'name' => 'Cuenta de prueba',
            'email' => $email,
            'password' => 'password',
            'password_confirmation' => 'password',
            'rol' => $rolSolicitado,
        ]);

        $response->assertRedirect(route('home'));
        $user = User::where('email', $email)->firstOrFail();
        expect($user->rol)->toBe('cliente')
            ->and($user->perfilCliente)->not->toBeNull();
        $this->assertAuthenticatedAs($user);
        $this->post(route('logout'));
    }
});

test('formulario público de registro no ofrece selector de rol', function () {
    $this->get(route('register'))
        ->assertOk()
        ->assertSee('Crear cuenta de cliente')
        ->assertDontSee('name="rol"', false)
        ->assertDontSee('Administrador');
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
