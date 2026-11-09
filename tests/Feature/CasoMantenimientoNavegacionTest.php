<?php

use App\Models\CasoMantenimiento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['rol' => 'administrador']);
    $this->cliente = User::factory()->create(['rol' => 'cliente']);
});

test('la bandeja administrativa no muestra botón volver y el detalle sigue con enlace válido', function () {
    $caso = CasoMantenimiento::factory()->create([
        'solicitante_id' => $this->cliente->id,
        'codigo' => 'MAN-2026-00050',
        'estado' => 'abierto',
        'area_verde' => 'Jardín de pruebas',
    ]);

    $indexResponse = $this->actingAs($this->admin)->get(route('mantenimiento.casos.index'));
    $indexResponse->assertOk();
    $indexResponse->assertDontSee('Volver');
    $indexResponse->assertSee(route('plantas.index'));

    preg_match_all('/href=["\']([^"\']+)["\']/i', $indexResponse->getContent(), $matches);
    $hrefs = $matches[1] ?? [];
    foreach ($hrefs as $href) {
        $href = trim($href);
        if ($href === '' || str_starts_with($href, '#') || preg_match('/^(mailto:|tel:|javascript:)/i', $href) === 1) {
            continue;
        }
        if (preg_match('/\.(png|jpg|jpeg|gif|svg|webp|ico|css|js)(\?.*)?$/i', $href) === 1) {
            continue;
        }

        $url = preg_match('/^https?:\/\//i', $href) ? $href : url($href);
        $subResponse = $this->actingAs($this->admin)->get($url);
        expect($subResponse->status())->not->toBe(404)
            ->and($subResponse->status())->not->toBe(500);
    }

    $showResponse = $this->actingAs($this->admin)->get(route('mantenimiento.casos.show', $caso));
    $showResponse->assertOk();
    $showResponse->assertSee('Volver');
    $showResponse->assertSee(route('mantenimiento.casos.index'));

    preg_match_all('/href=["\']([^"\']+)["\']/i', $showResponse->getContent(), $matches);
    $hrefs = $matches[1] ?? [];
    foreach ($hrefs as $href) {
        $href = trim($href);
        if ($href === '' || str_starts_with($href, '#') || preg_match('/^(mailto:|tel:|javascript:)/i', $href) === 1) {
            continue;
        }
        if (preg_match('/\.(png|jpg|jpeg|gif|svg|webp|ico|css|js)(\?.*)?$/i', $href) === 1) {
            continue;
        }

        $url = preg_match('/^https?:\/\//i', $href) ? $href : url($href);
        $subResponse = $this->actingAs($this->admin)->get($url);
        expect($subResponse->status())->not->toBe(404)
            ->and($subResponse->status())->not->toBe(500);
    }
});
