<?php

it('redirige la ruta raíz a la landing pública de home', function () {
    $this->get('/')
        ->assertRedirect('/home');

    $this->get('/home')
        ->assertOk()
        ->assertSee('Encuentra la planta perfecta para tu');
});

it('el boton empezar del layout publica apunta a login sin sesion', function () {
    $contenido = file_get_contents(resource_path('views/layouts/app.blade.php'));

    expect($contenido)->not->toContain('href="{{ Auth::check() ? route(\'perfil.edit\') : route(\'login\') }}">Empezar');
});

it('el boton empezar ahora del home publico apunta a login', function () {
    $contenido = file_get_contents(resource_path('views/home.blade.php'));

    expect($contenido)->not->toContain('href="{{ Auth::check() ? route(\'perfil.edit\') : route(\'login\') }}"');
});
