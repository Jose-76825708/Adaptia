<?php

namespace App\View\Composers\Paisajismo;

use App\Models\PropuestaDiseno;
use App\Services\DisenoProyectoService;
use Illuminate\View\View;

class DisenoProyectoComposer
{
    public function __construct(private readonly DisenoProyectoService $disenoService)
    {
    }

    public function compose(View $view): void
    {
        $proyecto=$view->getData()['proyecto'] ?? null;
        $historial=$proyecto ? PropuestaDiseno::where('proyecto_id',$proyecto->id)->with('plantas.planta')->orderByDesc('version')->orderByDesc('id')->get() : collect();
        $propuesta=$historial->first();

        $view->with([
            'historial_propuestas'=>$historial,
            'propuesta_vigente'=>$propuesta,
            'plantas_disponibles'=>$this->disenoService->plantasDisponibles(),
        ]);
    }
}
