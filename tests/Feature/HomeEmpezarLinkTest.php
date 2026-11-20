<?php

it('el boton empezar del layout publica apunta a login sin sesion', function () {
    $contenido = file_get_contents(resource_path('views/layouts/app.blade.php'));

    expect($contenido)->not->toContain('href="{{ Auth::check() ? route(\'perfil.edit\') : route(\'login\') }}">Empezar');
});

it('el boton empezar ahora del home publico apunta a login', function () {
    $contenido = file_get_contents(resource_path('views/home.blade.php'));

    expect($contenido)->not->toContain('href="{{ Auth::check() ? route(\'perfil.edit\') : route(\'login\') }}"');
});
