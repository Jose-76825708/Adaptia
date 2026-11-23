<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('vendedor inicia sesión y llega a su dashboard comercial', function () {
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

    $response->assertRedirect(route('home'));
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

test('el proxy HTTPS de Render genera urls seguras para vite y rutas', function () {
    $this->withHeader('X-Forwarded-Proto', 'https')
        ->get('/login')
        ->assertOk()
        ->assertSee('href="https://localhost/build/assets/', false)
        ->assertSee('action="https://localhost/login"', false);

    $this->withHeader('X-Forwarded-Proto', 'https')
        ->get('/home')
        ->assertOk()
        ->assertSee('href="https://localhost/build/assets/', false)
        ->assertSee('src="https://localhost/build/assets/', false)
        ->assertSee('https://localhost/login', false);
});

test('sin cabecera de proxy las urls locales conservan el esquema de la petición', function () {
    $this->get('/login')
        ->assertOk()
        ->assertSee('action="http://localhost/login"', false);
});

test('administrador inicia sesión y llega a su dashboard operativo', function () {
    $user = User::factory()->create([
        'email' => 'administrador@example.com',
        'password' => Hash::make('password'),
        'rol' => 'administrador',
    ]);

    $response = $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('home'));
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
