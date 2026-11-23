<?php

namespace App\Services;

use App\Models\Planta;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class RecomendacionService
{
    public function limpiarPerfil(Request $request)
    {
        return $request->only([
            'luz_requerida',
            'frecuencia_riego',
            'tamaño_adulto',
            'nivel_cuidado',
            'tipo_ambiente',
            'toxicidad',
            'estetica',
        ]);
    }

    public function compatibilidadOrdinal(array $orden, string $valorPlanta, string $valorUsuario)
    {
        $posicion_usuario = array_search($valorUsuario, $orden);
        $posicion_planta = array_search($valorPlanta, $orden);

        $diferencia = abs($posicion_planta - $posicion_usuario);
        $distancia_maxima = count($orden) - 1;

        return 1 - ($diferencia / $distancia_maxima);
    }

    public function compatibilidadCategoria(string $atributo, array $categoria, string $valorPLanta, string $valorUsuario)
    {
        if (
            $atributo === 'estetica' &&
            in_array($valorPLanta, ['suculenta', 'suculentas'], true) &&
            in_array($valorUsuario, ['suculenta', 'suculentas'], true)
        ) {
            return 1;
        }

        $posicion_usuario = array_search($valorUsuario, $categoria);
        $posicion_planta = array_search($valorPLanta, $categoria);

        if (
            ($atributo == 'tipo_ambiente' && $posicion_usuario == 2) ||
            $posicion_usuario == $posicion_planta
        ) {
            return 1;
        }

        return 0;
    }

    private function ordenesDisponibles()
    {
        return [
            'luz_requerida' => ['baja', 'media', 'alta', 'siempre_en_el_sol'],
            'frecuencia_riego' => ['diario', 'cada_3_dias', 'semanal', 'quincenal', 'mensualmente'],
            'nivel_cuidado' => ['principiante', 'intermedio', 'experto'],
            'tamaño_adulto' => ['pequena', 'mediana', 'grande'],
        ];
    }

    private function categoriasDisponibles()
    {
        return [
            'tipo_ambiente' => ['interiores', 'exteriores', 'ambos'],
            'estetica' => ['follaje', 'flor', 'colgantes', 'suculenta'],
        ];
    }

    private function pesosCategoricos()
    {
        return [
            'luz_requerida' => 1 / 6,
            'frecuencia_riego' => 1 / 6,
            'nivel_cuidado' => 1 / 6,
            'tamaño_adulto' => 1 / 6,
            'tipo_ambiente' => 1 / 6,
            'estetica' => 1 / 6,
        ];
    }

    public function calculaScore(Planta $planta, array $perfil_usuario)
    {
        $perfilUsuario = $this->normalizarPerfil($perfil_usuario);

        if (! $this->perfilCriteriosCompleto($perfilUsuario)) {
            throw new InvalidArgumentException('El perfil no contiene todos los criterios requeridos para calcular recomendaciones.');
        }

        $ordenes = $this->ordenesDisponibles();
        $categorias = $this->categoriasDisponibles();
        $pesos = $this->pesosCategoricos();
        $score = 0;

        foreach ($ordenes as $atributo => $orden) {
            $valorPlanta = $planta[$atributo];
            $valorUsuario = $perfilUsuario[$atributo];
            $peso = $pesos[$atributo];

            $compatibilidadOrdinal = $this->compatibilidadOrdinal($orden, $valorPlanta, $valorUsuario);
            $score += $compatibilidadOrdinal * $peso;
        }

        foreach ($categorias as $atributo => $categoria) {
            $valorPlanta = $planta[$atributo];
            $valorUsuario = $perfilUsuario[$atributo];
            $peso = $pesos[$atributo];

            $compatibilidadCategorica = $this->compatibilidadCategoria($atributo, $categoria, $valorPlanta, $valorUsuario);
            $score += $compatibilidadCategorica * $peso;
        }

        return $score;
    }

    private function normalizarPerfil(array $perfil): array
    {
        $aliasesTamanoAdulto = [
            'tamaÃ±o_adulto',
            'tama�o_adulto',
            'tamano_adulto',
        ];

        if (! array_key_exists('tamaño_adulto', $perfil)) {
            foreach ($aliasesTamanoAdulto as $alias) {
                if (array_key_exists($alias, $perfil)) {
                    $perfil['tamaño_adulto'] = $perfil[$alias];
                    break;
                }
            }
        }

        return $perfil;
    }

    private function perfilCriteriosCompleto(array $perfil): bool
    {
        foreach (array_keys($this->ordenesDisponibles()) as $atributo) {
            if (! array_key_exists($atributo, $perfil) || ! is_string($perfil[$atributo]) || trim($perfil[$atributo]) === '') {
                return false;
            }
        }

        foreach (array_keys($this->categoriasDisponibles()) as $atributo) {
            if (! array_key_exists($atributo, $perfil) || ! is_string($perfil[$atributo]) || trim($perfil[$atributo]) === '') {
                return false;
            }
        }

        return true;
    }

    private function perfilCompleto(array $perfil): bool
    {
        return $this->perfilCriteriosCompleto($perfil)
            && array_key_exists('toxicidad', $perfil)
            && $perfil['toxicidad'] !== null;
    }

    public function plantasNoToxicas()
    {
        return Planta::where('toxicidad', false)->get();
    }

    public function generarRecomendaciones(array $perfil_usuario): Collection
    {
        $perfilUsuario = $this->normalizarPerfil($perfil_usuario);

        if (! $this->perfilCompleto($perfilUsuario)) {
            return collect();
        }

        if ((bool) $perfilUsuario['toxicidad']) {
            $plantas = $this->plantasNoToxicas();
        } else {
            $plantas = Planta::all();
        }

        return $plantas->map(function (Planta $planta) use ($perfilUsuario) {
            $planta->setAttribute(
                'compatibilidad_porcentaje',
                $this->calculaScore($planta, $perfilUsuario) * 100
            );

            return $planta;
        })
            ->sortByDesc('compatibilidad_porcentaje')
            ->take(10)
            ->values();
    }
}
