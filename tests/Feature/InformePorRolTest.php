<?php

it('el informe separa la vista administrativa y la vista del cliente', function () {
    $contenido = file_get_contents(base_path('resources/views/mantenimiento/casos/informe.blade.php'));

    expect($contenido)->toContain("auth()->user()->rol === 'administrador'")
        ->and($contenido)->toContain("@include('partials.sidebar')")
        ->and($contenido)->toContain("@extends('layouts.app')")
        ->and($contenido)->toContain('Informe final');
});

it('la rama cliente no muestra el sidebar administrativo', function () {
    $contenido = file_get_contents(base_path('resources/views/mantenimiento/casos/informe.blade.php'));

    expect($contenido)->toContain("@else")
        ->and($contenido)->toContain("mantenimiento.solicitudes.index");
});
