<?php

namespace App\Services;

class RecomendacionCompraService
{
    private const PRIORIDAD = ['critica' => 0, 'alta' => 1, 'media' => 2, 'baja' => 3];

    public function generar(array $items): array
    {
        usort($items, function (array $a, array $b) {
            $prioridadA = self::PRIORIDAD[$a['prioridad'] ?? 'baja'] ?? 3;
            $prioridadB = self::PRIORIDAD[$b['prioridad'] ?? 'baja'] ?? 3;

            if ($prioridadA !== $prioridadB) {
                return $prioridadA <=> $prioridadB;
            }

            return strcmp((string) ($a['codigo'] ?? ''), (string) ($b['codigo'] ?? ''));
        });

        return array_values($items);
    }
}
