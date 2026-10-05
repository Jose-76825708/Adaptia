<?php

namespace App\Services;

use App\Models\Planta;

class EvaluadorUmbralesService
{
    private const RANGOS = [
        'humedad_suelo' => ['humedad_suelo_min', 'humedad_suelo_max'],
        'temperatura' => ['temperatura_min', 'temperatura_max'],
        'humedad_ambiental' => ['humedad_ambiental_min', 'humedad_ambiental_max'],
        'luz' => ['luz_min', 'luz_max'],
    ];

    private const VARIABLES = [
        'humedad_suelo' => ['nombre' => 'La humedad del suelo', 'unidad' => '%'],
        'temperatura' => ['nombre' => 'La temperatura', 'unidad' => '°C'],
        'humedad_ambiental' => ['nombre' => 'La humedad ambiental', 'unidad' => '%'],
        'luz' => ['nombre' => 'La iluminación', 'unidad' => 'lux'],
    ];

    /**
     * @param  array<string, int|float|string>  $mediciones
     * @return array<int, array{
     *     tipo: 'riego'|'cuidado_planta',
     *     variable: string,
     *     valor_medido: float,
     *     limite: float,
     *     direccion: 'bajo'|'alto',
     *     rango_esperado: array{minimo: float, maximo: float},
     *     mensaje: string
     * }>
     */
    public function evaluar(Planta $planta, array $mediciones): array
    {
        $desviaciones = [];

        foreach (self::RANGOS as $variable => [$campoMinimo, $campoMaximo]) {
            $minimo = $planta->{$campoMinimo};
            $maximo = $planta->{$campoMaximo};

            if ($minimo === null || $maximo === null) {
                continue;
            }

            $valor = (float) $mediciones[$variable];
            $direccion = null;
            $limite = null;

            if ($valor < (float) $minimo) {
                $direccion = 'bajo';
                $limite = (float) $minimo;
            } elseif ($valor > (float) $maximo) {
                $direccion = 'alto';
                $limite = (float) $maximo;
            }

            if ($direccion !== null) {
                $desviaciones[] = $this->crearAlerta(
                    $planta,
                    $variable,
                    $valor,
                    $limite,
                    $direccion,
                    (float) $minimo,
                    (float) $maximo,
                );
            }
        }

        return $desviaciones;
    }

    /**
     * @return array<int, string>
     */
    public function variablesEvaluables(Planta $planta): array
    {
        $variables = [];

        foreach (self::RANGOS as $variable => [$campoMinimo, $campoMaximo]) {
            if ($planta->{$campoMinimo} !== null && $planta->{$campoMaximo} !== null) {
                $variables[] = $variable;
            }
        }

        return $variables;
    }

    /**
     * @return array{
     *     tipo: 'riego'|'cuidado_planta',
     *     variable: string,
     *     valor_medido: float,
     *     limite: float,
     *     direccion: 'bajo'|'alto',
     *     rango_esperado: array{minimo: float, maximo: float},
     *     mensaje: string
     * }
     */
    private function crearAlerta(
        Planta $planta,
        string $variable,
        float $valor,
        float $limite,
        string $direccion,
        float $minimo,
        float $maximo,
    ): array {
        $tipo = $variable === 'humedad_suelo' && $direccion === 'bajo'
            ? 'riego'
            : 'cuidado_planta';
        $definicion = self::VARIABLES[$variable];
        $fraseDireccion = $direccion === 'bajo'
            ? 'por debajo del rango recomendado'
            : 'por encima del rango recomendado';
        $nombrePlanta = $planta->nombre;
        $mensaje = sprintf(
            '%s está %s para tu %s. Medición: %s %s; rango esperado: %s–%s %s.',
            $definicion['nombre'],
            $fraseDireccion,
            $nombrePlanta,
            $this->formatearNumero($valor),
            $definicion['unidad'],
            $this->formatearNumero($minimo),
            $this->formatearNumero($maximo),
            $definicion['unidad'],
        );

        return [
            'tipo' => $tipo,
            'variable' => $variable,
            'valor_medido' => $valor,
            'limite' => $limite,
            'direccion' => $direccion,
            'rango_esperado' => [
                'minimo' => $minimo,
                'maximo' => $maximo,
            ],
            'mensaje' => $mensaje,
        ];
    }

    private function formatearNumero(float $valor): string
    {
        return rtrim(rtrim(number_format($valor, 2, '.', ''), '0'), '.');
    }
}
