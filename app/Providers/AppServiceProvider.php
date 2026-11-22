<?php

namespace App\Providers;

use App\View\Composers\Paisajismo\DisenoProyectoComposer;
use App\View\Composers\Paisajismo\EntregaProyectoComposer;
use App\View\Composers\Paisajismo\InstalacionProyectoComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('paisajismo.proyectos.paneles.diseno', DisenoProyectoComposer::class);
        View::composer('paisajismo.proyectos.paneles.instalacion', InstalacionProyectoComposer::class);
        View::composer('paisajismo.proyectos.paneles.entrega', EntregaProyectoComposer::class);
    }
}
