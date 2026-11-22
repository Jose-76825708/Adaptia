<?php

namespace App\View\Composers\Paisajismo;

use App\Models\EntregaProyecto;
use App\Models\AvanceInstalacion;
use Illuminate\View\View;

class EntregaProyectoComposer
{
    public function compose(View $view): void
    {
        $proyecto=$view->getData()['proyecto'] ?? null;
        $entrega=$proyecto ? EntregaProyecto::where('proyecto_id',$proyecto->id)->first() : null;
        $ultimo=$proyecto ? AvanceInstalacion::where('proyecto_id',$proyecto->id)->orderByDesc('porcentaje')->orderByDesc('id')->first() : null;

        $view->with(['entrega_vigente'=>$entrega,'ultimo_avance_entrega'=>$ultimo]);
    }
}
