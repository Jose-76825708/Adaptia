<?php

namespace App\Providers;

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
        \Illuminate\Support\Facades\View::composer('paisajismo.proyectos.paneles.diseno', \App\View\Composers\Paisajismo\DisenoProyectoComposer::class);
        \Illuminate\Support\Facades\View::composer('paisajismo.proyectos.paneles.instalacion', \App\View\Composers\Paisajismo\InstalacionProyectoComposer::class);
        \Illuminate\Support\Facades\View::composer('paisajismo.proyectos.paneles.entrega', \App\View\Composers\Paisajismo\EntregaProyectoComposer::class);
    }
}
