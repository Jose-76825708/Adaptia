<?php

namespace App\Services;

use App\Models\CasoMantenimiento;
use InvalidArgumentException;

class AbastecimientoMantenimientoService
{
    public function __construct(
        private readonly CasoMantenimientoService $casoService,
        private readonly VerificadorDisponibilidadService $verificador,
    ) {
    }

    public function preparar(CasoMantenimiento $caso, int $planId): CasoMantenimiento
    {
        if ($caso->estado !== 'planificado') {
            throw new InvalidArgumentException('El caso debe estar planificado para marcarlo preparado.');
        }

        $veredicto = $this->verificador->verificarPlan($planId);
        if ($veredicto['estado'] !== 'ejecutable') {
            throw new InvalidArgumentException('El caso no puede prepararse porque el veredicto no es ejecutable.');
        }

        return $this->casoService->cambiarEstado($caso, 'preparado');
    }
}
