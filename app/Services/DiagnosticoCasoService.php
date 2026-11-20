<?php

namespace App\Services;

use App\Models\Alerta;
use App\Models\CasoMantenimiento;
use App\Models\DiagnosticoCaso;
use App\Models\HallazgoDiagnostico;
use App\Models\Planta;
use App\Models\PlantaVendida;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DiagnosticoCasoService
{
    public function __construct(
        private readonly CasoMantenimientoService $casoService,
        private readonly EvidenciaCasoService $evidenciaService,
        private readonly EvaluadorEstadoAreaService $evaluador,
    ) {
    }

    public function evaluarYPersistir(CasoMantenimiento $caso, int $autorId, ?CarbonInterface $hoy = null): DiagnosticoCaso
    {
        $hoy = $hoy ?? Carbon::now();

        if ($caso->estado === 'cerrado') {
            throw new InvalidArgumentException('No se puede evaluar un caso cerrado.');
        }

        if (!in_array($caso->estado, ['abierto', 'diagnosticado', 'en_ejecucion'], true)) {
            throw new InvalidArgumentException('El caso no permite generar o reevaluar el diagnóstico en su estado actual.');
        }

        $paquete = $this->evidenciaService->compilar($caso, $hoy);
        $paquete['rangos'] = $this->rangosPorPlantaVendida($caso);
        $paquete['alertas'] = $this->alertasPorVariable($caso, $hoy);
        $paquete['texto'] = $this->textoEvaluable($paquete);

        $evaluacion = $this->evaluador->evaluar($paquete, EvaluadorEstadoAreaService::ESTANDARES);

        return DB::transaction(function () use ($caso, $autorId, $hoy, $evaluacion) {
            $version = (int) DiagnosticoCaso::query()
                ->where('caso_id', $caso->id)
                ->max('version') + 1;

            $diagnostico = DiagnosticoCaso::create([
                'caso_id' => $caso->id,
                'autor_id' => $autorId,
                'version' => $version,
                'severidad' => $evaluacion['severidad_global'],
                'resumen' => $evaluacion['justificacion'],
                'justificacion' => $evaluacion['justificacion'],
                'evaluado_en' => $hoy,
            ]);

            foreach ($evaluacion['hallazgos'] as $hallazgo) {
                HallazgoDiagnostico::create([
                    'diagnostico_id' => $diagnostico->id,
                    'tipo' => $hallazgo['tipo'],
                    'variable' => $hallazgo['variable'],
                    'severidad' => $hallazgo['severidad'],
                    'descripcion' => $hallazgo['descripcion'],
                ]);
            }

            if ($caso->estado === 'abierto') {
                $this->casoService->cambiarEstado($caso, 'diagnosticado');
            }

            return $diagnostico->fresh('hallazgos');
        });
    }

    private function rangosPorPlantaVendida(CasoMantenimiento $caso): array
    {
        $rangos = [];
        $campos = [
            'humedad_suelo' => ['humedad_suelo_min', 'humedad_suelo_max'],
            'temperatura' => ['temperatura_min', 'temperatura_max'],
            'humedad_ambiental' => ['humedad_ambiental_min', 'humedad_ambiental_max'],
            'luz' => ['luz_min', 'luz_max'],
        ];

        $plantasVendidas = PlantaVendida::query()
            ->where('user_id', $caso->solicitante_id)
            ->with(['venta.planta'])
            ->get();

        foreach ($plantasVendidas as $plantaVendida) {
            $planta = $plantaVendida->venta?->planta;
            if (!$planta instanceof Planta) {
                continue;
            }

            foreach ($campos as $variable => $columnas) {
                $rangos[$plantaVendida->id][$variable] = [
                    'minimo' => $planta->{$columnas[0]},
                    'maximo' => $planta->{$columnas[1]},
                ];
            }
        }

        return $rangos;
    }

    private function alertasPorVariable(CasoMantenimiento $caso, CarbonInterface $hoy): array
    {
        $inicio = $hoy->copy()->subDays(EvidenciaCasoService::VENTANA_LECTURAS_DIAS)->startOfDay();
        $fin = $hoy->copy()->endOfDay();

        $conteo = Alerta::query()
            ->whereHas('plantaVendida', function ($query) use ($caso) {
                $query->where('user_id', $caso->solicitante_id);
            })
            ->whereBetween('created_at', [$inicio, $fin])
            ->whereNotNull('variable')
            ->get()
            ->countBy('variable')
            ->all();

        return $conteo;
    }

    private function textoEvaluable(array $paquete): array
    {
        $texto = [];
        foreach ($paquete['evidencias']['notas'] ?? [] as $nota) {
            $texto[] = (string) ($nota['contenido'] ?? '');
        }

        $texto[] = (string) ($paquete['caso']['descripcion'] ?? '');
        $texto[] = (string) ($paquete['caso']['observaciones'] ?? '');

        return $texto;
    }
}
