<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class RegistroHerramientasAsistente
{
    private const HERRAMIENTAS = [
        'stock_planta' => [
            'roles' => ['vendedor', 'administrador'],
            'reglas' => ['nombre' => ['required', 'string', 'min:2', 'max:100']],
            'definicion' => [
                'type' => 'function',
                'function' => [
                    'name' => 'stock_planta',
                    'description' => 'Consulta el stock actual y mínimo de una planta por su nombre exacto.',
                    'parameters' => [
                        'type' => 'object',
                        'required' => ['nombre'],
                        'properties' => [
                            'nombre' => ['type' => 'string', 'minLength' => 2, 'maxLength' => 100],
                        ],
                        'additionalProperties' => false,
                    ],
                ],
            ],
        ],
        'plantas_bajo_stock' => [
            'roles' => ['vendedor', 'administrador'],
            'reglas' => [],
            'definicion' => [
                'type' => 'function',
                'function' => [
                    'name' => 'plantas_bajo_stock',
                    'description' => 'Lista hasta 20 plantas con stock actual menor que su stock mínimo.',
                    'parameters' => ['type' => 'object', 'properties' => [], 'required' => [], 'additionalProperties' => false],
                ],
            ],
        ],
        'estado_productos_campana' => [
            'roles' => ['vendedor', 'administrador'],
            'reglas' => ['codigo_campana' => ['required', 'string', 'regex:/^CAM-[0-9]{4}-[0-9]{5,}$/', 'max:25']],
            'definicion' => [
                'type' => 'function',
                'function' => [
                    'name' => 'estado_productos_campana',
                    'description' => 'Consulta una campaña por código, incluyendo estado, productos, disponibilidad y avance frente a metas.',
                    'parameters' => [
                        'type' => 'object',
                        'required' => ['codigo_campana'],
                        'properties' => [
                            'codigo_campana' => ['type' => 'string', 'pattern' => '^CAM-[0-9]{4}-[0-9]{5,}$', 'maxLength' => 25],
                        ],
                        'additionalProperties' => false,
                    ],
                ],
            ],
        ],
        'campanas_por_estado' => [
            'roles' => ['vendedor', 'administrador'],
            'reglas' => ['estado' => ['required', 'string', 'in:objetivos_definidos,demanda_analizada,productos_identificados,oferta_disponible,gestion_disponibilidad,oferta_por_ajustar,oferta_planificada,promocion_preparada,promocion_realizada,resultados_evaluados,cerrada']],
            'definicion' => [
                'type' => 'function',
                'function' => [
                    'name' => 'campanas_por_estado',
                    'description' => 'Lista hasta 20 campañas en un estado válido del macroproceso.',
                    'parameters' => [
                        'type' => 'object',
                        'required' => ['estado'],
                        'properties' => [
                            'estado' => [
                                'type' => 'string',
                                'enum' => [
                                    'objetivos_definidos', 'demanda_analizada', 'productos_identificados',
                                    'oferta_disponible', 'gestion_disponibilidad', 'oferta_por_ajustar',
                                    'oferta_planificada', 'promocion_preparada', 'promocion_realizada',
                                    'resultados_evaluados', 'cerrada',
                                ],
                            ],
                        ],
                        'additionalProperties' => false,
                    ],
                ],
            ],
        ],
        'ranking_ventas_periodo' => [
            'roles' => ['vendedor', 'administrador'],
            'reglas' => [
                'periodo' => [
                    'sometimes',
                    'string',
                    'in:hoy,ayer,ultimos_7_dias,ultimos_30_dias,mes_actual,mes_anterior,este_anio,anio_anterior',
                ],
                'inicio' => ['sometimes', 'date_format:Y-m-d'],
                'fin' => ['sometimes', 'date_format:Y-m-d', 'after_or_equal:inicio'],
            ],
            'definicion' => [
                'type' => 'function',
                'function' => [
                    'name' => 'ranking_ventas_periodo',
                    'description' => 'Consulta el ranking de ventas en un periodo. Acepta un periodo relativo o fechas personalizadas; si no se indica periodo, usa mes_actual. ultimos_30_dias significa los 30 días hasta hoy; mes_anterior significa el mes calendario anterior.',
                    'parameters' => [
                        'type' => 'object',
                        'required' => [],
                        'properties' => [
                            'periodo' => [
                                'type' => 'string',
                                'enum' => [
                                    'hoy', 'ayer', 'ultimos_7_dias', 'ultimos_30_dias',
                                    'mes_actual', 'mes_anterior', 'este_anio', 'anio_anterior',
                                ],
                            ],
                            'inicio' => ['type' => 'string', 'format' => 'date'],
                            'fin' => ['type' => 'string', 'format' => 'date'],
                        ],
                        'additionalProperties' => false,
                    ],
                ],
            ],
        ],
        'solicitudes_disponibilidad_pendientes' => [
            'roles' => ['administrador'],
            'reglas' => [],
            'definicion' => [
                'type' => 'function',
                'function' => [
                    'name' => 'solicitudes_disponibilidad_pendientes',
                    'description' => 'Lista hasta 30 solicitudes de disponibilidad pendientes. Disponible solo para administradores.',
                    'parameters' => ['type' => 'object', 'properties' => [], 'required' => [], 'additionalProperties' => false],
                ],
            ],
        ],
        'casos_mantenimiento' => [
            'roles' => ['administrador'],
            'reglas' => [
                'estado' => ['sometimes', 'string', 'in:abierto,diagnosticado,planificado,preparado,en_ejecucion,cerrado'],
            ],
            'definicion' => [
                'type' => 'function',
                'function' => [
                    'name' => 'casos_mantenimiento',
                    'description' => 'Consulta hasta 20 casos de mantenimiento activos o de un estado indicado. Solo para administradores.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'estado' => [
                                'type' => 'string',
                                'enum' => ['abierto', 'diagnosticado', 'planificado', 'preparado', 'en_ejecucion', 'cerrado'],
                            ],
                        ],
                        'additionalProperties' => false,
                    ],
                ],
            ],
        ],
        'alertas_monitoreo' => [
            'roles' => ['administrador'],
            'reglas' => [],
            'definicion' => [
                'type' => 'function',
                'function' => [
                    'name' => 'alertas_monitoreo',
                    'description' => 'Consulta hasta 20 alertas IoT activas y lecturas recientes con variables medidas. Solo para administradores.',
                    'parameters' => ['type' => 'object', 'properties' => [], 'required' => [], 'additionalProperties' => false],
                ],
            ],
        ],
    ];

    public function __construct(
        private readonly DemandaComercialService $demanda,
        private readonly DisponibilidadCampanaService $disponibilidad,
        private readonly PlaneacionComercialService $planeacion,
        private readonly EvaluacionCampanaService $evaluacion,
        private readonly DashboardOperativoService $dashboard,
    ) {}

    public function herramientasDisponibles(User $usuario): array
    {
        $rol = $this->rolActual($usuario);

        return array_values(array_map(function (array $herramienta) {
            $definicion = $herramienta['definicion'];
            if ($definicion['function']['parameters']['properties'] === []) {
                $definicion['function']['parameters']['properties'] = (object) [];
            }

            return $definicion;
        }, array_filter(self::HERRAMIENTAS, fn (array $herramienta) => in_array($rol, $herramienta['roles'], true))));
    }

    public function ejecutar(string $nombre, mixed $argumentos, User $usuario): array
    {
        $herramienta = self::HERRAMIENTAS[$nombre] ?? null;
        if ($herramienta === null) {
            throw new InvalidArgumentException('La herramienta solicitada no está registrada.');
        }

        $rol = $this->rolActual($usuario);
        if (! in_array($rol, $herramienta['roles'], true)) {
            throw new AuthorizationException('El rol autenticado no puede usar esta consulta.');
        }

        $argumentosValidados = $this->validarArgumentos($argumentos, $herramienta['reglas']);

        return match ($nombre) {
            'stock_planta' => $this->demanda->consultarStockPorNombre($argumentosValidados['nombre']),
            'plantas_bajo_stock' => ['plantas' => $this->demanda->listarPlantasBajoStock()],
            'estado_productos_campana' => $this->consultarCampana($argumentosValidados['codigo_campana']),
            'campanas_por_estado' => ['campanas' => $this->planeacion->campanasPorEstado($argumentosValidados['estado'])],
            'ranking_ventas_periodo' => $this->consultarRanking($argumentosValidados),
            'solicitudes_disponibilidad_pendientes' => ['solicitudes' => $this->planeacion->solicitudesPendientes()],
            'casos_mantenimiento' => $this->dashboard->consultarCasosMantenimiento($argumentosValidados['estado'] ?? null),
            'alertas_monitoreo' => $this->dashboard->consultarAlertasMonitoreo(),
        };
    }

    public function herramientasSoloAdministrador(): array
    {
        return array_keys(array_filter(
            self::HERRAMIENTAS,
            fn (array $herramienta) => $herramienta['roles'] === ['administrador']
        ));
    }

    private function validarArgumentos(mixed $argumentos, array $reglas): array
    {
        if (! is_array($argumentos) || array_diff(array_keys($argumentos), array_keys($reglas)) !== []) {
            throw ValidationException::withMessages([
                'argumentos' => 'Los argumentos de la consulta no son válidos.',
            ]);
        }

        return Validator::make($argumentos, $reglas)->validate();
    }

    private function rolActual(User $usuario): ?string
    {
        return User::query()->whereKey($usuario->id)->value('rol');
    }

    private function consultarCampana(string $codigo): array
    {
        $campana = $this->planeacion->buscarPorCodigo($codigo);
        if (! $campana) {
            return ['encontrada' => false, 'codigo' => strtoupper(trim($codigo))];
        }

        return [
            ...$this->planeacion->consultarCampana($codigo),
            'disponibilidad' => $this->disponibilidad->verificar($campana),
            'avance' => $this->evaluacion->evaluar($campana),
        ];
    }

    private function consultarRanking(array $argumentos): array
    {
        $periodo = $argumentos['periodo'] ?? null;
        $tieneInicio = isset($argumentos['inicio']);
        $tieneFin = isset($argumentos['fin']);

        if (($tieneInicio xor $tieneFin) || ($periodo !== null && ($tieneInicio || $tieneFin))) {
            throw ValidationException::withMessages([
                'argumentos' => 'Indica un periodo o ambas fechas personalizadas, pero no los combines.',
            ]);
        }

        $rango = $this->resolverRangoFechas(
            $periodo ?? ($tieneInicio ? 'personalizado' : 'mes_actual'),
            $argumentos['inicio'] ?? null,
            $argumentos['fin'] ?? null,
        );
        $resultado = $this->demanda->consultar(
            $rango['inicio']->copy()->startOfDay(),
            $rango['fin']->copy()->endOfDay()
        );

        return [
            'periodo_consultado' => [
                'tipo' => $rango['tipo'],
                'inicio' => $rango['inicio']->toDateString(),
                'fin' => $rango['fin']->toDateString(),
            ],
            'ranking_ventas' => array_slice($resultado['ranking_ventas'], 0, 20),
        ];
    }

    /**
     * @return array{tipo: string, inicio: Carbon, fin: Carbon}
     */
    private function resolverRangoFechas(string $periodo, ?string $inicio, ?string $fin): array
    {
        $hoy = Carbon::now(config('app.timezone'))->startOfDay();

        [$desde, $hasta] = match ($periodo) {
            'hoy' => [$hoy->copy(), $hoy->copy()],
            'ayer' => [$hoy->copy()->subDay(), $hoy->copy()->subDay()],
            'ultimos_7_dias' => [$hoy->copy()->subDays(6), $hoy->copy()],
            'ultimos_30_dias' => [$hoy->copy()->subDays(29), $hoy->copy()],
            'mes_actual' => [$hoy->copy()->startOfMonth(), $hoy->copy()],
            'mes_anterior' => [
                $hoy->copy()->subMonthNoOverflow()->startOfMonth(),
                $hoy->copy()->subMonthNoOverflow()->endOfMonth(),
            ],
            'este_anio' => [$hoy->copy()->startOfYear(), $hoy->copy()],
            'anio_anterior' => [
                $hoy->copy()->subYear()->startOfYear(),
                $hoy->copy()->subYear()->endOfYear(),
            ],
            'personalizado' => [
                Carbon::createFromFormat('!Y-m-d', $inicio, config('app.timezone')),
                Carbon::createFromFormat('!Y-m-d', $fin, config('app.timezone')),
            ],
            default => throw new InvalidArgumentException('El periodo de consulta no es válido.'),
        };

        if (! $desde || ! $hasta || $desde->gt($hasta)) {
            throw ValidationException::withMessages([
                'argumentos' => 'El rango de fechas personalizado no es válido.',
            ]);
        }

        return [
            'tipo' => $periodo,
            'inicio' => $desde,
            'fin' => $hasta,
        ];
    }
}
