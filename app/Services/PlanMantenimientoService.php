<?php

namespace App\Services;

use App\Models\CasoMantenimiento;
use App\Models\DiagnosticoCaso;
use App\Models\ItemChecklist;
use App\Models\PlanMantenimiento;
use App\Models\RecursoCatalogo;
use App\Models\TareaPlan;
use App\Models\TareaRecurso;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PlanMantenimientoService
{
    public function __construct(
        private readonly CasoMantenimientoService $casoService,
        private readonly GeneradorPlanMantenimientoService $generador,
        private readonly OrdenadorTareasPlanService $ordenador,
    ) {
    }

    public function generarYPersistir(CasoMantenimiento $caso, int $autorId, ?CarbonInterface $hoy = null): PlanMantenimiento
    {
        $hoy = $hoy ?? Carbon::now();

        if (!in_array($caso->estado, ['diagnosticado', 'planificado'], true)) {
            throw new InvalidArgumentException('El caso debe estar diagnosticado o planificado para generar el plan.');
        }

        $diagnostico = DiagnosticoCaso::query()
            ->where('caso_id', $caso->id)
            ->orderByDesc('version')
            ->orderByDesc('id')
            ->first();

        if (!$diagnostico) {
            throw new InvalidArgumentException('El caso no tiene un diagnóstico vigente.');
        }

        $tareas = $this->generador->derivarTareas(
            $caso->tipo_mantenimiento,
            $diagnostico->severidad,
            []
        );
        $tareas = $this->ordenador->ordenar($tareas);

        foreach ($tareas as $tarea) {
            foreach ($tarea['recursos'] as $recurso) {
                if (!RecursoCatalogo::query()->where('codigo', $recurso['codigo'])->exists()) {
                    throw new InvalidArgumentException("El recurso {$recurso['codigo']} no existe en el catálogo.");
                }
            }
        }

        return DB::transaction(function () use ($caso, $autorId, $hoy, $diagnostico, $tareas) {
            $version = (int) PlanMantenimiento::query()
                ->where('caso_id', $caso->id)
                ->max('version') + 1;

            $plan = PlanMantenimiento::create([
                'caso_id' => $caso->id,
                'autor_id' => $autorId,
                'version' => $version,
                'generado_en' => $hoy,
                'descripcion' => 'Plan generado desde el diagnóstico ' . $diagnostico->version . '.',
            ]);

            $idsPorCodigo = [];

            foreach ($tareas as $tarea) {
                $dependeId = null;
                $codigoDependencia = $tarea['depende_de_codigo'] ?? null;
                if (is_string($codigoDependencia) && isset($idsPorCodigo[$codigoDependencia])) {
                    $dependeId = $idsPorCodigo[$codigoDependencia];
                }

                $nuevaTarea = TareaPlan::create([
                    'plan_id' => $plan->id,
                    'codigo' => $tarea['codigo'],
                    'titulo' => $tarea['titulo'],
                    'descripcion' => $tarea['descripcion'],
                    'prioridad' => $tarea['prioridad'],
                    'criticidad' => $tarea['criticidad'],
                    'depende_de_tarea_id' => $dependeId,
                    'fecha_estimada' => null,
                    'duracion_dias' => $tarea['duracion_dias'],
                    'estado' => 'pendiente',
                ]);

                $idsPorCodigo[$tarea['codigo']] = $nuevaTarea->id;

                foreach ($tarea['recursos'] as $recurso) {
                    $catalogo = RecursoCatalogo::query()->where('codigo', $recurso['codigo'])->firstOrFail();
                    TareaRecurso::create([
                        'tarea_id' => $nuevaTarea->id,
                        'recurso_id' => $catalogo->id,
                        'cantidad' => $recurso['cantidad'],
                    ]);
                }

                foreach ($tarea['checklist'] as $texto) {
                    ItemChecklist::create([
                        'tarea_id' => $nuevaTarea->id,
                        'descripcion' => $texto,
                        'cumplido' => false,
                    ]);
                }
            }

            if ($caso->estado === 'diagnosticado') {
                $this->casoService->cambiarEstado($caso, 'planificado');
            }

            return $plan->fresh(['tareas.recursos', 'tareas.checklist']);
        });
    }
}
