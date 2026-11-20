<?php

namespace App\Services;

use App\Models\CasoMantenimiento;
use App\Models\EventoEjecucion;
use App\Models\InformeCierre;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CierreMantenimientoService
{
    public function __construct(
        private readonly CasoMantenimientoService $casoService,
        private readonly DiagnosticoCasoService $diagnosticoService,
        private readonly ComparacionEjecucionService $comparacionService,
        private readonly DecisionCierreService $decisionService,
    ) {
    }

    public function reevaluar(CasoMantenimiento $caso, ?CarbonInterface $hoy = null): array
    {
        $hoy = $hoy ?? now();
        $inicio = EventoEjecucion::query()
            ->where('caso_id', $caso->id)
            ->where('tipo', 'inicio')
            ->orderBy('fecha')
            ->orderBy('id')
            ->first();

        if (!$inicio) {
            throw new InvalidArgumentException('El caso no tiene un evento de inicio para reevaluar el cierre.');
        }

        $diagnostico = $this->diagnosticoService->evaluarYPersistir($caso, (int) $caso->solicitante_id, $hoy);

        $plan = $caso->planes()->latest('version')->latest('id')->first();
        $tareas = $plan ? $plan->tareas()->get()->map(fn ($tarea) => [
            'id' => $tarea->id,
            'estado' => $tarea->estado,
            'fecha_estimada' => $tarea->fecha_estimada,
        ])->all() : [];

        $eventos = EventoEjecucion::query()->where('caso_id', $caso->id)->get()->map(fn ($evento) => [
            'id' => $evento->id,
            'tipo' => $evento->tipo,
            'resuelta_en' => $evento->resuelta_en?->toDateTimeString(),
        ])->all();

        $comparacion = $this->comparacionService->comparar($tareas, $eventos, $hoy);
        $tareasSinCompletar = count(array_filter(
            $tareas,
            fn (array $tarea) => ($tarea['estado'] ?? 'pendiente') !== 'completada',
        ));
        $decision = $this->decisionService->decidir(
            $diagnostico->severidad,
            $tareasSinCompletar,
            count($comparacion['incidencias_abiertas']),
        );

        return array_merge($decision, [
            'severidad_final' => $diagnostico->severidad,
            'comparacion' => $comparacion,
            'inicio_en' => $inicio->fecha,
        ]);
    }

    public function cerrar(CasoMantenimiento $caso, int $autorId, ?CarbonInterface $hoy = null): InformeCierre
    {
        if ($caso->estado !== 'en_ejecucion') {
            throw new InvalidArgumentException('Solo se puede cerrar un caso en ejecución.');
        }

        $resultado = $this->reevaluar($caso, $hoy);
        if (!$resultado['puede_cerrar']) {
            throw new InvalidArgumentException(implode(' ', $resultado['correcciones']));
        }

        return DB::transaction(function () use ($caso, $autorId, $hoy, $resultado) {
            $version = (int) InformeCierre::where('caso_id', $caso->id)->max('version') + 1;
            $informe = InformeCierre::create([
                'caso_id' => $caso->id,
                'autor_id' => $autorId,
                'destinatario_id' => $caso->solicitante_id,
                'version' => $version,
                'resumen' => 'Cierre aprobado con severidad final ' . $resultado['severidad_final'] . '.',
                'contenido' => $this->contenidoPlano($resultado),
                'notificacion_pendiente' => true,
                'cerrado_en' => $hoy ?? now(),
            ]);

            $this->casoService->cambiarEstado($caso, 'cerrado');

            return $informe->fresh();
        });
    }

    public function validarMotivoReapertura(string $motivo): string
    {
        $motivo = trim($motivo);
        if ($motivo === '') {
            throw new InvalidArgumentException('El motivo de reapertura es obligatorio.');
        }

        return $motivo;
    }

    public function reabrir(CasoMantenimiento $caso, int $autorId, string $motivo, ?CarbonInterface $hoy = null): void
    {
        $motivo = $this->validarMotivoReapertura($motivo);

        if ($caso->estado !== 'cerrado') {
            throw new InvalidArgumentException('Solo se puede reabrir un caso cerrado.');
        }

        DB::transaction(function () use ($caso, $autorId, $motivo, $hoy) {
            EventoEjecucion::create([
                'caso_id' => $caso->id,
                'tarea_id' => null,
                'tipo' => 'reapertura',
                'descripcion' => $motivo,
                'autor_id' => $autorId,
                'fecha' => $hoy ?? now(),
                'resuelta_en' => null,
            ]);

            $this->casoService->cambiarEstado($caso, 'en_ejecucion');
        });
    }

    private function contenidoPlano(array $resultado): string
    {
        return implode("\n", [
            'Resultado: cerrado',
            'Severidad final: ' . $resultado['severidad_final'],
            'Tareas pendientes: ' . count($resultado['comparacion']['pendientes']),
            'Tareas retrasadas: ' . count($resultado['comparacion']['retrasadas']),
            'Incidencias abiertas: ' . count($resultado['comparacion']['incidencias_abiertas']),
        ]);
    }
}
