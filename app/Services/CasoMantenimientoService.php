<?php

namespace App\Services;

use App\Models\CasoMantenimiento;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CasoMantenimientoService
{
    public const ESTADOS = [
        'abierto',
        'diagnosticado',
        'planificado',
        'preparado',
        'en_ejecucion',
        'cerrado',
    ];

    public const TRANSICIONES = [
        'abierto' => ['diagnosticado'],
        'diagnosticado' => ['planificado'],
        'planificado' => ['preparado'],
        'preparado' => ['en_ejecucion'],
        'en_ejecucion' => ['cerrado'],
        'cerrado' => ['en_ejecucion'],
    ];

    public function abrir(int $solicitanteId, array $datos, ?CarbonInterface $hoy = null): CasoMantenimiento
    {
        $hoy = $hoy ?? Carbon::now();
        $solicitudService = new SolicitudMantenimientoService();

        $solicitudNormalizada = $solicitudService->normalizar($datos);
        $solicitudValidada = $solicitudService->validar($solicitudNormalizada, $hoy);
        $clasificacion = $solicitudService->clasificar($solicitudValidada, $hoy);

        return DB::transaction(function () use ($solicitanteId, $solicitudValidada, $clasificacion, $hoy) {
            $caso = CasoMantenimiento::create([
                'codigo' => 'PENDIENTE',
                'solicitante_id' => $solicitanteId,
                'area_verde' => $solicitudValidada['area_verde'],
                'descripcion' => $solicitudValidada['descripcion'],
                'fecha_deseada' => $solicitudValidada['fecha_deseada'] ?? null,
                'observaciones' => $solicitudValidada['observaciones'] ?? null,
                'tipo_mantenimiento' => $clasificacion['tipo_mantenimiento'],
                'urgencia' => $clasificacion['urgencia'],
                'estado' => 'abierto',
            ]);

            $codigo = sprintf('MAN-%s-%05d', $hoy->format('Y'), $caso->id);
            $caso->update(['codigo' => $codigo]);

            return $caso->fresh();
        });
    }

    public function listarPorSolicitante(int $solicitanteId, int $porPagina = 10)
    {
        return CasoMantenimiento::where('solicitante_id', $solicitanteId)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($porPagina);
    }

    public function listar(array $filtros = [], int $porPagina = 15)
    {
        $query = CasoMantenimiento::query()->with('solicitante');
        $filtrosAplicados = [];

        $estado = $filtros['estado'] ?? null;
        if (is_string($estado) && in_array($estado, self::ESTADOS, true)) {
            $query->where('estado', $estado);
            $filtrosAplicados['estado'] = $estado;
        }

        $urgencia = $filtros['urgencia'] ?? null;
        if (is_string($urgencia) && in_array($urgencia, \App\Services\SolicitudMantenimientoService::URGENCIAS, true)) {
            $query->where('urgencia', $urgencia);
            $filtrosAplicados['urgencia'] = $urgencia;
        }

        $tipo = $filtros['tipo'] ?? null;
        if (is_string($tipo) && in_array($tipo, \App\Services\SolicitudMantenimientoService::TIPOS, true)) {
            $query->where('tipo_mantenimiento', $tipo);
            $filtrosAplicados['tipo'] = $tipo;
        }

        $busqueda = $filtros['q'] ?? null;
        if (is_string($busqueda)) {
            $busqueda = trim($busqueda);
            if ($busqueda !== '') {
                $query->where(function ($subQuery) use ($busqueda) {
                    $subQuery->where('codigo', 'like', "%{$busqueda}%")
                        ->orWhere('area_verde', 'like', "%{$busqueda}%");
                });
                $filtrosAplicados['q'] = $busqueda;
            }
        }

        return $query->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($porPagina)
            ->appends($filtrosAplicados);
    }

    public function puedeTransicionar(string $desde, string $hacia): bool
    {
        $desde = strtolower(trim($desde));
        $hacia = strtolower(trim($hacia));

        if (!in_array($desde, self::ESTADOS, true) || !in_array($hacia, self::ESTADOS, true)) {
            return false;
        }

        return in_array($hacia, self::TRANSICIONES[$desde] ?? [], true);
    }

    public function cambiarEstado(CasoMantenimiento $caso, string $nuevoEstado): CasoMantenimiento
    {
        $nuevoEstado = strtolower(trim($nuevoEstado));

        if (!$this->puedeTransicionar($caso->estado, $nuevoEstado)) {
            throw new InvalidArgumentException(
                "La transición de '{$caso->estado}' a '{$nuevoEstado}' no está permitida."
            );
        }

        $caso->estado = $nuevoEstado;
        $caso->save();

        return $caso->fresh();
    }
}
