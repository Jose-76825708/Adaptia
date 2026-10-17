<?php

test('configuración de base de datos contiene las conexiones y opciones esperadas', function () {
    $database = require base_path('config/database.php');

    expect($database['connections']['sqlite']['driver'])->toBe('sqlite')
        ->and($database['connections']['mysql']['driver'])->toBe('mysql')
        ->and($database['connections']['mysql']['options'])->toBeArray();
});

test('configuración de logs dirige el canal single al archivo de Laravel', function () {
    $logging = require base_path('config/logging.php');

    expect($logging['channels']['single']['path'])->toBe(storage_path('logs/laravel.log'))
        ->and($logging['channels']['stack']['ignore_exceptions'])->toBeFalse();
});
