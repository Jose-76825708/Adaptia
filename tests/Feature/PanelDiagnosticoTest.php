<?php

use App\Models\CasoMantenimiento;
use App\Models\User;
use App\Services\CasoMantenimientoService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::dropIfExists('casos_mantenimiento');
    Schema::dropIfExists('users');

    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->timestamp('email_verified_at')->nullable();
        $table->string('password');
        $table->string('remember_token', 100)->nullable();
        $table->string('rol')->nullable();
        $table->timestamps();
    });

    Schema::create('casos_mantenimiento', function (Blueprint $table) {
        $table->id();
        $table->string('codigo')->unique();
        $table->foreignId('solicitante_id')->constrained('users')->onDelete('restrict');
        $table->string('area_verde');
        $table->text('descripcion');
        $table->date('fecha_deseada')->nullable();
        $table->text('observaciones')->nullable();
        $table->string('tipo_mantenimiento');
        $table->string('urgencia');
        $table->string('estado')->default('abierto');
        $table->timestamps();
    });
});

test('el panel contiene formularios con ids y labels accesibles', function () {
    $contenido = file_get_contents(base_path('resources/views/mantenimiento/casos/paneles/diagnostico.blade.php'));

    expect($contenido)->toContain('id="diag-nota-contenido"')
        ->and($contenido)->toContain('for="diag-nota-contenido"')
        ->and($contenido)->toContain('id="diag-foto-archivo"')
        ->and($contenido)->toContain('for="diag-foto-archivo"')
        ->and($contenido)->not->toContain('{!!');
});

test('el panel contiene el boton para eliminar fotos', function () {
    $contenido = file_get_contents(base_path('resources/views/mantenimiento/casos/paneles/diagnostico.blade.php'));

    expect($contenido)->toContain('Eliminar foto')
        ->and($contenido)->toContain('mantenimiento.casos.diagnostico.fotos.eliminar');
});

test('las rutas finales de diagnostico existen', function () {
    $rutas = app('router')->getRoutes()->getRoutes();
    $uris = collect($rutas)->map(fn ($ruta) => $ruta->uri())->all();

    expect($uris)->toContain('mantenimiento/casos/{caso}/diagnostico/notas')
        ->and($uris)->toContain('mantenimiento/casos/{caso}/diagnostico/fotos')
        ->and($uris)->toContain('mantenimiento/casos/{caso}/diagnostico/evaluar')
        ->and($uris)->toContain('mantenimiento/casos/{caso}/diagnostico/fotos/{evidencia}/eliminar');
});
