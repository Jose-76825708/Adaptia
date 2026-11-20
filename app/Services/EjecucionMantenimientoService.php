<?php

namespace App\Services;

use App\Models\CasoMantenimiento;
use App\Models\EventoEjecucion;
use App\Models\TareaPlan;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class EjecucionMantenimientoService
{
    public const TIPOS = ['inicio', 'avance', 'incidencia', 'nota', 'reapertura'];

    public function __construct(private readonly CasoMantenimientoService $casoService)
    {
    }

    public function validarTipo(string $tipo): bool
    {
        $tipo = strtolower(trim($tipo));
        if (!in_array($tipo, self::TIPOS, true)) {
            throw new InvalidArgumentException('El tipo de evento no está permitido.');
        }

        return true;
    }

    public function registrar(CasoMantenimiento $caso, int $autorId, string $tipo, string $descripcion, ?int $tareaId = null, ?CarbonInterface $fecha = null): EventoEjecucion
    {
        $tipo = strtolower(trim($tipo));
        $this->validarTipo($tipo);
        $descripcion = trim($descripcion);

        if ($descripcion === '') {
            throw new InvalidArgumentException('La descripción del evento es obligatoria.');
        }

        $fecha = $fecha ?? now();

        if ($tipo === 'inicio') {
            if ($caso->estado !== 'preparado') {
                throw new InvalidArgumentException('El primer evento de ejecución debe iniciar desde preparado.');
            }
            if (EventoEjecucion::where('caso_id', $caso->id)->where('tipo', 'inicio')->exists()) {
                throw new InvalidArgumentException('El caso ya tiene un evento de inicio.');
            }
            if ($tareaId !== null) {
                throw new InvalidArgumentException('El evento de inicio no debe estar asociado a una tarea.');
            }
        } elseif (!in_array($caso->estado, ['en_ejecucion'], true)) {
            throw new InvalidArgumentException('Los eventos distintos de inicio solo se aceptan durante la ejecución.');
        }

        if ($tareaId !== null) {
            $tarea = TareaPlan::query()->findOrFail($tareaId);
            $plan = $caso->planes()->latest('version')->latest('id')->first();
            if (!$plan || (int) $tarea->plan_id !== (int) $plan->id) {
                throw new InvalidArgumentException('La tarea no pertenece al plan vigente del caso.');
            }
        }

        return DB::transaction(function () use ($caso, $autorId, $tipo, $descripcion, $tareaId, $fecha) {
            $evento = EventoEjecucion::create([
                'caso_id' => $caso->id,
                'tarea_id' => $tareaId,
                'tipo' => $tipo,
                'descripcion' => $descripcion,
                'autor_id' => $autorId,
                'fecha' => $fecha,
                'resuelta_en' => null,
            ]);

            if ($tipo === 'inicio') {
                $this->calcularFechasEstimadas($caso, $fecha);
                $this->casoService->cambiarEstado($caso, 'en_ejecucion');
            } elseif ($tareaId !== null) {
                TareaPlan::whereKey($tareaId)->update(['estado' => 'en_curso']);
            }

            return $evento->fresh();
        });
    }

    public function resolverIncidencia(CasoMantenimiento $caso, int $eventoId, int $autorId, ?CarbonInterface $fecha = null): EventoEjecucion
    {
        $evento = EventoEjecucion::where('caso_id', $caso->id)
            ->where('tipo', 'incidencia')
            ->findOrFail($eventoId);

        if ($evento->resuelta_en !== null) {
            throw new InvalidArgumentException('La incidencia ya está resuelta.');
        }

        $evento->resuelta_en = $fecha ?? now();
        $evento->save();

        return $evento->fresh();
    }

    private function calcularFechasEstimadas(CasoMantenimiento $caso, CarbonInterface $fechaInicio): void
    {
        $plan = $caso->planes()->latest('version')->latest('id')->first();
        if (!$plan) {
            throw new InvalidArgumentException('El caso no tiene un plan vigente para iniciar ejecución.');
        }

        $acumulado = 0;
        $tareas = $plan->tareas()->orderBy('id')->get();
        foreach ($tareas as $tarea) {
            $acumulado += (int) $tarea->duracion_dias;
            $tarea->fecha_estimada = $fechaInicio->copy()->addDays($acumulado)->startOfDay();
            $tarea->save();
        }
    }
}
