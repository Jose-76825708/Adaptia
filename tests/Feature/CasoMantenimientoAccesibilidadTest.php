<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['rol' => 'administrador']);
});

test('la bandeja administrativa mantiene labels y controles accesibles y sin ids duplicados', function () {
    $response = $this->actingAs($this->admin)->get(route('mantenimiento.casos.index'));
    $response->assertOk();

    $html = $response->getContent();
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML('<?xml encoding="UTF-8">' . $html);
    libxml_clear_errors();

    $labels = [];
    foreach ($dom->getElementsByTagName('label') as $label) {
        $for = $label->getAttribute('for');
        if ($for !== '') {
            $labels[$for] = trim($label->textContent);
        }
    }

    $ids = [];
    foreach ($dom->getElementsByTagName('select') as $control) {
        if ($control->hasAttribute('id')) {
            $ids[] = $control->getAttribute('id');
        }
    }
    foreach ($dom->getElementsByTagName('input') as $control) {
        if ($control->hasAttribute('id') && $control->getAttribute('type') !== 'hidden') {
            $ids[] = $control->getAttribute('id');
        }
    }

    expect($labels)->toHaveKeys(['filtro-estado', 'filtro-urgencia', 'filtro-tipo', 'filtro-q']);
    foreach (['filtro-estado', 'filtro-urgencia', 'filtro-tipo', 'filtro-q'] as $id) {
        $labelText = $labels[$id] ?? '';
        expect($labelText)->not->toBeEmpty();
        $control = $dom->getElementById($id);
        expect($control)->not->toBeNull();
        expect(strtoupper($control->tagName))->toBeIn(['SELECT', 'INPUT']);
    }

    $duplicados = array_filter(array_count_values($ids), fn ($count) => $count > 1);
    expect($duplicados)->toBe([]);
});
