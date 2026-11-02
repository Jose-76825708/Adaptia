<?php

namespace App\Services;

class SolicitudMantenimientoService
{
    /**
     * Normaliza una solicitud de mantenimiento a un contrato estándar.
     *
     * @param  array<string, mixed>  $datos
     * @return array<string, mixed>
     */
    public function normalizar(array $datos): array
    {
        $salida = [
            'area_verde' => null,
            'descripcion' => null,
            'fecha_deseada' => null,
            'observaciones' => [],
        ];

        $entrada = array_intersect_key($datos, array_flip(array_keys($salida)));

        if (array_key_exists('area_verde', $entrada)) {
            $salida['area_verde'] = $this->normalizarTexto($entrada['area_verde']);
        }

        if (array_key_exists('descripcion', $entrada)) {
            $salida['descripcion'] = $this->normalizarTexto($entrada['descripcion']);
        }

        if (array_key_exists('fecha_deseada', $entrada)) {
            $salida['fecha_deseada'] = $this->normalizarFecha($entrada['fecha_deseada']);
        }

        if (array_key_exists('observaciones', $entrada)) {
            $salida['observaciones'] = $this->normalizarObservaciones($entrada['observaciones']);
        }

        return $salida;
    }

    private function normalizarTexto(mixed $valor): ?string
    {
        if (!is_scalar($valor) && !is_null($valor)) {
            return null;
        }

        if (is_bool($valor)) {
            return $valor ? 'true' : 'false';
        }

        $texto = trim((string) $valor);
        $texto = preg_replace('/\s+/', ' ', $texto) ?? $texto;

        if ($texto === '') {
            return null;
        }

        return $texto;
    }

    private function normalizarFecha(mixed $valor): ?string
    {
        if (!is_scalar($valor) && !is_null($valor)) {
            return null;
        }

        $texto = $this->normalizarTexto($valor);

        if ($texto === null) {
            return null;
        }

        $input = $texto;

        $candidatos = [
            'Y-m-d' => $this->parseFechaConFormato($input, 'Y-m-d'),
            'd/m/Y' => $this->parseFechaConFormato($input, 'd/m/Y'),
        ];

        foreach ($candidatos as $formato => $fecha) {
            if ($fecha !== null) {
                return $fecha;
            }
        }

        return $input;
    }

    private function parseFechaConFormato(string $valor, string $formato): ?string
    {
        $fecha = \DateTime::createFromFormat($formato, $valor);

        if ($fecha === false) {
            return null;
        }

        if ($fecha->format($formato) !== $valor) {
            return null;
        }

        return $fecha->format('Y-m-d');
    }

    private function normalizarObservaciones(mixed $valor): array
    {
        if (is_null($valor)) {
            return [];
        }

        if (is_array($valor)) {
            $items = $valor;
        } else {
            $texto = $this->normalizarTexto($valor);
            if ($texto === null) {
                return [];
            }

            $items = preg_split('/[\r\n;]+/', $texto) ?: [$texto];
        }

        $normalizadas = [];

        foreach ($items as $item) {
            $valorNormalizado = $this->normalizarTexto($item);
            if ($valorNormalizado !== null) {
                $normalizadas[] = $valorNormalizado;
            }
        }

        $normalizadas = array_slice($normalizadas, 0, 6);

        return array_values($normalizadas);
    }
}
