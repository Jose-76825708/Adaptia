<?php

namespace App\Services;

class EvaluadorEstadoAreaService
{
    public const ESTANDARES = [
        'riesgo' => ['misma_planta_alta' => 2],
        'reincidencia' => ['media' => 2, 'alta' => 4],
        'texto' => [
            'criticas' => ['urgente', 'peligro', 'riesgo', 'se muere', 'caído', 'desplomado'],
        ],
    ];

    public function evaluar(array $paquete, array $estandares): array
    {
        $hallazgos = [];
        $variablesAfectadas = [];

        foreach ($paquete['sensores'] ?? [] as $sensor) {
            $plantaVendidaId = (int) ($sensor['planta_vendida_id'] ?? 0);
            $altasEnPlanta = 0;

            foreach ($sensor['variables'] ?? [] as $variable => $resumen) {
                $rango = $paquete['rangos'][$plantaVendidaId][$variable] ?? null;
                if (!$this->rangoCompleto($rango)) {
                    continue;
                }

                $promedio = $this->numero($resumen['promedio'] ?? null);
                $ultimo = $this->numero($resumen['ultima'] ?? null);
                $minimo = $this->numero($resumen['minimo'] ?? null);
                $maximo = $this->numero($resumen['maximo'] ?? null);

                if ($promedio !== null && ($promedio < (float) $rango['minimo'] || $promedio > (float) $rango['maximo'])) {
                    $hallazgos[] = $this->hallazgo('rango', 'alta', $variable, 'El promedio está fuera del rango permitido.');
                    $variablesAfectadas[$variable] = $variable;
                    $altasEnPlanta++;
                } elseif ($minimo !== null && $maximo !== null
                    && ($minimo < (float) $rango['minimo'] || $maximo > (float) $rango['maximo'])) {
                    $hallazgos[] = $this->hallazgo('rango', 'media', $variable, 'El mínimo o máximo supera el rango permitido.');
                    $variablesAfectadas[$variable] = $variable;
                } elseif ($ultimo !== null && $promedio !== null
                    && ($ultimo < (float) $rango['minimo'] || $ultimo > (float) $rango['maximo'])
                    && $promedio >= (float) $rango['minimo'] && $promedio <= (float) $rango['maximo']) {
                    $hallazgos[] = $this->hallazgo('tendencia', 'media', $variable, 'La última lectura está fuera de rango mientras el promedio permanece dentro.');
                    $variablesAfectadas[$variable] = $variable;
                }
            }

            if ($altasEnPlanta >= (int) ($estandares['riesgo']['misma_planta_alta'] ?? 2)) {
                $hallazgos[] = $this->hallazgo('riesgo', 'alta', null, 'La misma planta presenta al menos dos variables con severidad alta.');
            }
        }

        foreach ($paquete['alertas'] ?? [] as $variable => $cantidad) {
            $cantidad = (int) $cantidad;
            $media = (int) ($estandares['reincidencia']['media'] ?? 2);
            $alta = (int) ($estandares['reincidencia']['alta'] ?? 4);

            if ($cantidad >= $alta) {
                $hallazgos[] = $this->hallazgo('reincidencia', 'alta', (string) $variable, "Se registraron {$cantidad} alertas de la variable.");
            } elseif ($cantidad >= $media) {
                $hallazgos[] = $this->hallazgo('reincidencia', 'media', (string) $variable, "Se registraron {$cantidad} alertas de la variable.");
            }
        }

        $texto = implode(' ', $paquete['texto'] ?? []);
        $texto = mb_strtolower($texto);
        $ocurrencias = 0;
        foreach ($estandares['texto']['criticas'] ?? [] as $palabra) {
            $palabra = mb_strtolower((string) $palabra);
            $ocurrencias += mb_substr_count($texto, $palabra);
        }

        if ($ocurrencias >= 3) {
            $hallazgos[] = $this->hallazgo('texto', 'alta', null, 'Las notas o la solicitud contienen tres o más palabras críticas.');
        } elseif ($ocurrencias >= 1) {
            $hallazgos[] = $this->hallazgo('texto', 'media', null, 'Las notas o la solicitud contienen palabras críticas.');
        }

        if ($hallazgos === []) {
            $hallazgos[] = $this->hallazgo('texto', 'baja', null, 'Sin hallazgos con la evidencia disponible.');
        }

        $severidadGlobal = $this->severidadGlobal($hallazgos);

        return [
            'severidad_global' => $severidadGlobal,
            'hallazgos' => $hallazgos,
            'variables_afectadas' => array_values($variablesAfectadas),
            'justificacion' => $this->justificacion($severidadGlobal, $hallazgos),
        ];
    }

    private function rangoCompleto(?array $rango): bool
    {
        return is_array($rango)
            && $rango['minimo'] !== null
            && $rango['maximo'] !== null;
    }

    private function numero(mixed $valor): ?float
    {
        return is_numeric($valor) ? (float) $valor : null;
    }

    private function hallazgo(string $tipo, string $severidad, ?string $variable, string $descripcion): array
    {
        return [
            'tipo' => $tipo,
            'severidad' => $severidad,
            'variable' => $variable,
            'descripcion' => $descripcion,
        ];
    }

    private function severidadGlobal(array $hallazgos): string
    {
        $puntaje = 0;
        foreach ($hallazgos as $hallazgo) {
            $puntaje += match ($hallazgo['severidad']) {
                'alta' => 3,
                'media' => 2,
                default => 1,
            };
        }

        if ($puntaje >= 6) {
            return 'alta';
        }

        if ($puntaje >= 3) {
            return 'media';
        }

        return 'baja';
    }

    private function justificacion(string $severidad, array $hallazgos): string
    {
        return sprintf('Severidad %s con %d hallazgo(s).', $severidad, count($hallazgos));
    }
}
