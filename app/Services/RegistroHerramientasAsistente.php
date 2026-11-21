<?php

namespace App\Services;

use App\Models\User;
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
        'verificar_disponibilidad_campana' => [
            'roles' => ['vendedor', 'administrador'],
            'reglas' => ['codigo_campana' => ['required', 'string', 'regex:/^CAM-[0-9]{4}-[0-9]{5,}$/', 'max:25']],
            'definicion' => [
                'type' => 'function',
                'function' => [
                    'name' => 'verificar_disponibilidad_campana',
                    'description' => 'Compara la cantidad meta de cada planta de una campaña con su stock actual.',
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
        'estado_productos_campana' => [
            'roles' => ['vendedor', 'administrador'],
            'reglas' => ['codigo_campana' => ['required', 'string', 'regex:/^CAM-[0-9]{4}-[0-9]{5,}$/', 'max:25']],
            'definicion' => [
                'type' => 'function',
                'function' => [
                    'name' => 'estado_productos_campana',
                    'description' => 'Consulta estado, objetivos, fechas y productos activos de una campaña.',
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
                'inicio' => ['required', 'date_format:Y-m-d'],
                'fin' => ['required', 'date_format:Y-m-d', 'after_or_equal:inicio'],
            ],
            'definicion' => [
                'type' => 'function',
                'function' => [
                    'name' => 'ranking_ventas_periodo',
                    'description' => 'Consulta el ranking de unidades vendidas por planta entre dos fechas, inclusive.',
                    'parameters' => [
                        'type' => 'object',
                        'required' => ['inicio', 'fin'],
                        'properties' => [
                            'inicio' => ['type' => 'string', 'format' => 'date'],
                            'fin' => ['type' => 'string', 'format' => 'date'],
                        ],
                        'additionalProperties' => false,
                    ],
                ],
            ],
        ],
        'avance_campana' => [
            'roles' => ['vendedor', 'administrador'],
            'reglas' => ['codigo_campana' => ['required', 'string', 'regex:/^CAM-[0-9]{4}-[0-9]{5,}$/', 'max:25']],
            'definicion' => [
                'type' => 'function',
                'function' => [
                    'name' => 'avance_campana',
                    'description' => 'Calcula unidades vendidas y porcentaje de cumplimiento por planta frente a la meta.',
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
    ];

    public function __construct(
        private readonly DemandaComercialService $demanda,
        private readonly DisponibilidadCampanaService $disponibilidad,
        private readonly PlaneacionComercialService $planeacion,
        private readonly EvaluacionCampanaService $evaluacion,
    ) {}

    public function herramientasDisponibles(User $usuario): array
    {
        $rol = $this->rolActual($usuario);

        return array_values(array_map(
            fn (array $herramienta) => $herramienta['definicion'],
            array_filter(self::HERRAMIENTAS, fn (array $herramienta) => in_array($rol, $herramienta['roles'], true))
        ));
    }

    public function ejecutar(string $nombre, mixed $argumentos, User $usuario): array
    {
        $herramienta = self::HERRAMIENTAS[$nombre] ?? null;
        if ($herramienta === null) {
            throw new InvalidArgumentException('La herramienta solicitada no está registrada.');
        }

        $rol = $this->rolActual($usuario);
        if (!in_array($rol, $herramienta['roles'], true)) {
            throw new AuthorizationException('El rol autenticado no puede usar esta consulta.');
        }

        $argumentosValidados = $this->validarArgumentos($argumentos, $herramienta['reglas']);

        return match ($nombre) {
            'stock_planta' => $this->demanda->consultarStockPorNombre($argumentosValidados['nombre']),
            'plantas_bajo_stock' => ['plantas' => $this->demanda->listarPlantasBajoStock()],
            'verificar_disponibilidad_campana' => $this->verificarCampana($argumentosValidados['codigo_campana']),
            'estado_productos_campana' => $this->planeacion->consultarCampana($argumentosValidados['codigo_campana']),
            'campanas_por_estado' => ['campanas' => $this->planeacion->campanasPorEstado($argumentosValidados['estado'])],
            'ranking_ventas_periodo' => $this->consultarRanking($argumentosValidados['inicio'], $argumentosValidados['fin']),
            'avance_campana' => $this->consultarAvance($argumentosValidados['codigo_campana']),
            'solicitudes_disponibilidad_pendientes' => ['solicitudes' => $this->planeacion->solicitudesPendientes()],
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
        if (!is_array($argumentos) || array_diff(array_keys($argumentos), array_keys($reglas)) !== []) {
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

    private function verificarCampana(string $codigo): array
    {
        $campana = $this->planeacion->buscarPorCodigo($codigo);
        if (!$campana) {
            return ['encontrada' => false, 'codigo' => strtoupper(trim($codigo))];
        }

        return [
            'encontrada' => true,
            'codigo' => $campana->codigo,
            ...$this->disponibilidad->verificar($campana),
        ];
    }

    private function consultarRanking(string $inicio, string $fin): array
    {
        $resultado = $this->demanda->consultar(
            \Carbon\Carbon::parse($inicio)->startOfDay(),
            \Carbon\Carbon::parse($fin)->endOfDay()
        );

        return ['ranking_ventas' => array_slice($resultado['ranking_ventas'], 0, 20)];
    }

    private function consultarAvance(string $codigo): array
    {
        $campana = $this->planeacion->buscarPorCodigo($codigo);
        if (!$campana) {
            return ['encontrada' => false, 'codigo' => strtoupper(trim($codigo))];
        }

        return [
            'encontrada' => true,
            'codigo' => $campana->codigo,
            'estado' => $campana->estado,
            'avance' => $this->evaluacion->evaluar($campana),
        ];
    }
}
