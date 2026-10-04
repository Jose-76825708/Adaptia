<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('administrador puede crear cuentas de vendedor y administrador sin perder su sesión', function () {
    $administrador = User::factory()->create(['rol' => 'administrador']);

    foreach (['vendedor', 'administrador'] as $rol) {
        $email = "personal-{$rol}@example.com";

        $this->actingAs($administrador)
            ->post(route('personal.store'), [
                'name' => "Personal {$rol}",
                'email' => $email,
                'password' => 'password',
                'password_confirmation' => 'password',
                'rol' => $rol,
            ])
            ->assertRedirect(route('personal.create'))
            ->assertSessionHas('success');

        $usuarioCreado = User::where('email', $email)->firstOrFail();
        expect($usuarioCreado->rol)->toBe($rol)
            ->and(Hash::check('password', $usuarioCreado->password))->toBeTrue();
        $this->assertAuthenticatedAs($administrador);
    }
});

test('solo administradores pueden abrir el formulario y crear personal', function () {
    foreach (['cliente', 'vendedor'] as $rol) {
        $usuario = User::factory()->create(['rol' => $rol]);

        $this->actingAs($usuario)
            ->get(route('personal.create'))
            ->assertForbidden();

        $this->post(route('personal.store'), [
            'name' => 'Intento no autorizado',
            'email' => "no-autorizado-{$rol}@example.com",
            'password' => 'password',
            'password_confirmation' => 'password',
            'rol' => 'administrador',
        ])->assertForbidden();
    }
});

test('rechaza roles no permitidos y correos duplicados', function () {
    $administrador = User::factory()->create(['rol' => 'administrador']);
    $existente = User::factory()->create([
        'email' => 'existente@example.com',
        'rol' => 'cliente',
    ]);

    $this->from(route('personal.create'))
        ->actingAs($administrador)
        ->post(route('personal.store'), [
            'name' => 'Cuenta manipulada',
            'email' => 'rol-invalido@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'rol' => 'cliente',
        ])
        ->assertRedirect(route('personal.create'))
        ->assertSessionHasErrors('rol');

    $this->from(route('personal.create'))
        ->post(route('personal.store'), [
            'name' => 'Correo duplicado',
            'email' => $existente->email,
            'password' => 'password',
            'password_confirmation' => 'password',
            'rol' => 'vendedor',
        ])
        ->assertRedirect(route('personal.create'))
        ->assertSessionHasErrors('email');

    expect(User::where('email', 'rol-invalido@example.com')->exists())->toBeFalse()
        ->and(User::where('email', 'existente@example.com')->count())->toBe(1);
});

test('personal solo ve el enlace de gestión de cuentas el administrador', function () {
    $administrador = User::factory()->create(['rol' => 'administrador']);
    $vendedor = User::factory()->create(['rol' => 'vendedor']);

    $this->actingAs($administrador)
        ->get(route('personal.create'))
        ->assertOk()
        ->assertSee(route('personal.create'));

    $this->actingAs($vendedor)
        ->get(route('sensores.index'))
        ->assertOk()
        ->assertDontSee(route('personal.create'));
});
