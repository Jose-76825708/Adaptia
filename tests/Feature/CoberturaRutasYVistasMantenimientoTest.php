<?php

it('todas las rutas post de mantenimiento quedan registradas', function () {
    $uris = collect(app('router')->getRoutes()->getRoutes())->map(fn ($ruta) => $ruta->uri())->all();

    $esperadas = [
        'mantenimiento/casos/{caso}/diagnostico/guardar',
        'mantenimiento/casos/{caso}/diagnostico/fotos/{evidencia}/eliminar',
        'mantenimiento/casos/{caso}/plan/generar',
        'mantenimiento/casos/{caso}/requisicion/recibir',
        'mantenimiento/casos/{caso}/abastecimiento/preparar',
        'mantenimiento/casos/{caso}/ejecucion/evento',
        'mantenimiento/casos/{caso}/ejecucion/checklist/{item}',
        'mantenimiento/casos/{caso}/ejecucion/incidencias/{evento}/resolver',
        'mantenimiento/casos/{caso}/cierre/cerrar',
        'mantenimiento/casos/{caso}/cierre/reabrir',
        'mantenimiento/casos/{caso}/informe',
    ];

    foreach ($esperadas as $uri) {
        expect($uris)->toContain($uri);
    }
});

it('las vistas de los seis paneles tienen ids de prefijo unico', function () {
    $prefijos = [
        'diagnostico' => 'diag-',
        'plan' => 'plan-',
        'recursos' => 'rec-',
        'abastecimiento' => 'abas-',
        'ejecucion' => 'ejec-',
        'cierre' => 'cierre-',
    ];

    foreach ($prefijos as $panel => $prefijo) {
        $contenido = file_get_contents(base_path("resources/views/mantenimiento/casos/paneles/{$panel}.blade.php"));
        expect($contenido)->toContain("id=\"{$prefijo}");
    }
});

it('cada panel usa texto escapado sin html inseguro', function () {
    foreach (['diagnostico','plan','recursos','abastecimiento','ejecucion','cierre'] as $panel) {
        $contenido = file_get_contents(base_path("resources/views/mantenimiento/casos/paneles/{$panel}.blade.php"));
        expect($contenido)->not->toContain('{!!');
    }
});

it('el informe tiene ramas separadas para administrador y cliente', function () {
    $contenido = file_get_contents(base_path('resources/views/mantenimiento/casos/informe.blade.php'));

    expect($contenido)->toContain("auth()->user()->rol === 'administrador'")
        ->and($contenido)->toContain("@include('partials.sidebar')")
        ->and($contenido)->toContain("@extends('layouts.app')")
        ->and($contenido)->toContain('Informe final');
});
