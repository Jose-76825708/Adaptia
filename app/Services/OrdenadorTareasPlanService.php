<?php

namespace App\Services;

class OrdenadorTareasPlanService
{
    private const PRIORIDAD = ['critica' => 0, 'alta' => 1, 'media' => 2, 'baja' => 3];
    private const CRITICIDAD = ['alta' => 0, 'media' => 1, 'baja' => 2];

    public function ordenar(array $tareas): array
    {
        $porCodigo = [];
        foreach ($tareas as $indice => $tarea) {
            $codigo = (string) ($tarea['codigo'] ?? 'T' . ($indice + 1));
            $porCodigo[$codigo] = $tarea;
        }

        $resultado = [];
        $visitadas = [];

        $visitar = function (string $codigo) use (&$visitar, &$resultado, &$visitadas, $porCodigo): void {
            if (isset($visitadas[$codigo]) || !isset($porCodigo[$codigo])) {
                return;
            }

            $tarea = $porCodigo[$codigo];
            $dependencia = $tarea['depende_de_codigo'] ?? null;
            if (is_string($dependencia) && $dependencia !== '') {
                $visitar($dependencia);
            }

            $visitadas[$codigo] = true;
            $resultado[] = $tarea;
        };

        $pendientes = array_values($porCodigo);
        usort($pendientes, fn (array $a, array $b) => $this->comparar($a, $b));

        foreach ($pendientes as $tarea) {
            $visitar((string) $tarea['codigo']);
        }

        return $resultado;
    }

    private function comparar(array $a, array $b): int
    {
        $prioridad = (self::PRIORIDAD[$a['prioridad'] ?? 'baja'] ?? 3)
            <=> (self::PRIORIDAD[$b['prioridad'] ?? 'baja'] ?? 3);
        if ($prioridad !== 0) {
            return $prioridad;
        }

        $criticidad = (self::CRITICIDAD[$a['criticidad'] ?? 'baja'] ?? 2)
            <=> (self::CRITICIDAD[$b['criticidad'] ?? 'baja'] ?? 2);
        if ($criticidad !== 0) {
            return $criticidad;
        }

        $codigo = strcmp((string) ($a['codigo'] ?? ''), (string) ($b['codigo'] ?? ''));
        if ($codigo !== 0) {
            return $codigo;
        }

        return ((int) ($a['id'] ?? 0)) <=> ((int) ($b['id'] ?? 0));
    }
}
