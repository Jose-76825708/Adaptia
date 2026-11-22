<?php

namespace App\Services;

use InvalidArgumentException;

/**
 * Cálculos puros (sin base de datos) del macroproceso de gestión de inventario:
 * suficiencia de stock, comparación de una recepción contra lo solicitado y
 * clasificación de incidencias.
 */
class SuficienciaInventarioService
{
    public const TIPOS_INCIDENCIA = ['faltante', 'exceso', 'danado', 'incorrecto'];

    /**
     * Decide si el stock cubre lo requerido. Se considera disponible lo que está
     * en buen estado y además figura en el sistema.
     */
    public function evaluar(float $requerida, float $cantidadFisica, float $cantidadBuenEstado, float $stockSistema): array
    {
        if ($requerida <= 0) {
            throw new InvalidArgumentException('La cantidad requerida debe ser mayor que cero.');
        }
        if ($cantidadFisica < 0 || $cantidadBuenEstado < 0 || $stockSistema < 0) {
            throw new InvalidArgumentException('Las cantidades no pueden ser negativas.');
        }
        if ($this->redondear($cantidadBuenEstado) > $this->redondear($cantidadFisica)) {
            throw new InvalidArgumentException('La cantidad en buen estado no puede superar la cantidad física contada.');
        }

        $disponible = $this->redondear(min($cantidadBuenEstado, $stockSistema));
        $faltante = $this->redondear(max(0.0, $requerida - $disponible));

        return [
            'disponible' => $disponible,
            'faltante' => $faltante,
            'suficiente' => $faltante <= 0.0,
            'diferencia_fisica_sistema' => $this->redondear($cantidadFisica - $stockSistema),
        ];
    }

    /**
     * Compara lo aceptado hasta ahora más lo recibido contra lo solicitado en la reposición.
     */
    public function evaluarRecepcion(float $solicitada, float $aceptadaPrevia, float $recibida): array
    {
        if ($solicitada <= 0) {
            throw new InvalidArgumentException('La cantidad solicitada en la reposición debe ser mayor que cero.');
        }
        if ($recibida <= 0) {
            throw new InvalidArgumentException('La cantidad recibida debe ser mayor que cero.');
        }
        if ($aceptadaPrevia < 0) {
            throw new InvalidArgumentException('La cantidad aceptada previamente no puede ser negativa.');
        }

        $total = $this->redondear($aceptadaPrevia + $recibida);
        $diferencia = $this->redondear($total - $solicitada);

        return [
            'total' => $total,
            'diferencia' => $diferencia,
            'coincide' => abs($diferencia) < 0.005,
            'tipo_sugerido' => $diferencia < -0.005 ? 'faltante' : ($diferencia > 0.005 ? 'exceso' : null),
        ];
    }

    /**
     * Calcula cuántas unidades quedan afectadas por la incidencia y cuántas se aceptan de la recepción.
     *
     * @param array{diferencia: float} $evaluacion Resultado de evaluarRecepcion().
     * @return array{cantidad_afectada: float, cantidad_aceptada: float}
     */
    public function clasificarIncidencia(string $tipo, array $evaluacion, float $recibida, ?float $cantidadAfectada = null): array
    {
        if (!in_array($tipo, self::TIPOS_INCIDENCIA, true)) {
            throw new InvalidArgumentException('El tipo de incidencia no es válido.');
        }

        $diferencia = (float) $evaluacion['diferencia'];

        if ($tipo === 'faltante') {
            if ($diferencia >= -0.005) {
                throw new InvalidArgumentException('No hay faltante: lo recibido completa la cantidad solicitada.');
            }

            return ['cantidad_afectada' => $this->redondear(abs($diferencia)), 'cantidad_aceptada' => $this->redondear($recibida)];
        }

        if ($tipo === 'exceso') {
            if ($diferencia <= 0.005) {
                throw new InvalidArgumentException('No hay exceso: lo recibido no supera la cantidad solicitada.');
            }

            return [
                'cantidad_afectada' => $this->redondear($diferencia),
                'cantidad_aceptada' => $this->redondear($recibida - $diferencia),
            ];
        }

        if ($cantidadAfectada === null || $cantidadAfectada <= 0) {
            throw new InvalidArgumentException('Indica cuántas unidades están dañadas o son incorrectas.');
        }
        if ($this->redondear($cantidadAfectada) > $this->redondear($recibida)) {
            throw new InvalidArgumentException('Las unidades afectadas no pueden superar lo recibido.');
        }

        return [
            'cantidad_afectada' => $this->redondear($cantidadAfectada),
            'cantidad_aceptada' => $this->redondear($recibida - $cantidadAfectada),
        ];
    }

    public function esEntera(float $cantidad): bool
    {
        return abs($cantidad - round($cantidad)) < 0.00001;
    }

    private function redondear(float $valor): float
    {
        return round($valor, 2);
    }
}
