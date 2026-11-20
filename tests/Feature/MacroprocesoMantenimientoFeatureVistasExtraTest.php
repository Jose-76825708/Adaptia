<?php

it('cada panel tiene botones y contenedores con formato del programa', function () {
    foreach (['diagnostico','plan','recursos','abastecimiento','ejecucion','cierre'] as $panel) {
        $contenido=file_get_contents(base_path("resources/views/mantenimiento/casos/paneles/{$panel}.blade.php"));
        expect($contenido)->toContain('rounded-[10px]')
            ->and($contenido)->toContain('border-[#e4ebdf]')
            ->and($contenido)->not->toContain('{!!');
    }
});

it('diagnostico tiene guardado unificado eliminacion y zoom', function () {
    $contenido=file_get_contents(base_path('resources/views/mantenimiento/casos/paneles/diagnostico.blade.php'));
    expect($contenido)->toContain('Guardar diagnóstico')
        ->and($contenido)->toContain('Eliminar foto')
        ->and($contenido)->toContain('target="_blank"')
        ->and($contenido)->toContain('h-48');
});

it('abastecimiento usa select de item y no campo numero libre', function () {
    $contenido=file_get_contents(base_path('resources/views/mantenimiento/casos/paneles/abastecimiento.blade.php'));
    expect($contenido)->toContain('<select')
        ->and($contenido)->toContain('name="item_id"')
        ->and($contenido)->not->toContain('name="item_id" type="number"');
});

it('ejecucion usa tarea textual y opciones segun estado', function () {
    $contenido=file_get_contents(base_path('resources/views/mantenimiento/casos/paneles/ejecucion.blade.php'));
    expect($contenido)->toContain('name="tarea"')
        ->and($contenido)->toContain('type="text"')
        ->and($contenido)->toContain("estado === 'preparado'");
});

it('cierre usa nombre y reapertura con motivo', function () {
    $contenido=file_get_contents(base_path('resources/views/mantenimiento/casos/paneles/cierre.blade.php'));
    expect($contenido)->toContain('Cierre / Reapertura')
        ->and($contenido)->toContain('cierre-motivo')
        ->and($contenido)->toContain('Cerrar caso')
        ->and($contenido)->toContain('Reabrir caso');
});

it('tablas usan encabezado verde y celdas centradas', function () {
    foreach (['diagnostico','plan','recursos','abastecimiento','ejecucion','cierre'] as $panel) {
        $contenido=file_get_contents(base_path("resources/views/mantenimiento/casos/paneles/{$panel}.blade.php"));
        if (str_contains($contenido,'<table')) {
            expect($contenido)->toContain('bg-[#edf8e7]')
                ->and($contenido)->toContain('text-center')
                ->and($contenido)->toContain('text-[#3b5d2d]');
        }
    }
});

it('informe separa sidebar administrador y layout cliente', function () {
    $contenido=file_get_contents(base_path('resources/views/mantenimiento/casos/informe.blade.php'));
    expect($contenido)->toContain("auth()->user()->rol === 'administrador'")
        ->and($contenido)->toContain("@include('partials.sidebar')")
        ->and($contenido)->toContain("@extends('layouts.app')");
});
