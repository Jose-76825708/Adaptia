<?php

namespace App\View\Composers\Paisajismo;

use App\Models\AvanceInstalacion;
use App\Models\PropuestaDiseno;
use App\Services\InstalacionProyectoService;
use Illuminate\View\View;

class InstalacionProyectoComposer
{
    public function __construct(private readonly InstalacionProyectoService $instalacionService)
    {
    }

    public function compose(View $view): void
    {
        $proyecto=$view->getData()['proyecto'] ?? null;
        $disponibilidad=['estado'=>'suficiente','lineas'=>[]];
        $avances=[];

        if ($proyecto) {
            $propuesta=PropuestaDiseno::where('proyecto_id',$proyecto->id)->orderByDesc('version')->orderByDesc('id')->first();
            if ($propuesta) {
                $lineas=[];
                foreach ($propuesta->plantas()->with('planta')->get() as $linea) {
                    $lineas[]=['planta_id'=>$linea->planta_id,'nombre'=>$linea->planta->nombre,'requerido'=>$linea->cantidad,'disponible'=>$linea->planta->stock_actual];
                }
                $disponibilidad=$this->instalacionService->verificarDisponibilidad($lineas);
            }

            $avances=AvanceInstalacion::where('proyecto_id',$proyecto->id)->orderByDesc('porcentaje')->orderByDesc('id')->get();
        }

        $view->with(['disponibilidad_instalacion'=>$disponibilidad,'avances_instalacion'=>$avances]);
    }
}
