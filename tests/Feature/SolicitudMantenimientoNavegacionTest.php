<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->cliente = User::factory()->create(['rol' => 'cliente']);
});

test('las pantallas de mantenimiento tienen un boton volver y sus enlaces internos responden sin errores', function () {
    $paginas = [
        ['route' => route('mantenimiento.solicitudes.index'), 'volver' => route('home')],
        ['route' => route('mantenimiento.solicitudes.create'), 'volver' => route('mantenimiento.solicitudes.index')],
    ];

    foreach ($paginas as $pagina) {
        $response = $this->actingAs($this->cliente)->get($pagina['route']);

        $response->assertOk();
        $response->assertSee('Volver');
        $response->assertSee($pagina['volver']);

        preg_match_all('/href=["\']([^"\']+)["\']/i', $response->getContent(), $matches);
        $hrefs = $matches[1] ?? [];

        foreach ($hrefs as $href) {
            $href = trim($href);
            if ($href === '' || $href === '#' || str_starts_with($href, '#')) {
                continue;
            }

            if (preg_match('/^(mailto:|tel:|javascript:)/i', $href) === 1) {
                continue;
            }

            if (preg_match('/\.(png|jpg|jpeg|gif|svg|webp|ico|css|js)(\?.*)?$/i', $href) === 1) {
                continue;
            }

            if (str_contains($href, 'logout') || str_contains($href, 'destroy')) {
                continue;
            }

            $url = preg_match('/^https?:\/\//i', $href) ? $href : url($href);
            $subResponse = $this->actingAs($this->cliente)->get($url);

            expect($subResponse->status())->not->toBe(404)
                ->and($subResponse->status())->not->toBe(500);
        }
    }
});
