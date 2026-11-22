<?php

namespace App\Services;

use App\Exceptions\StockInsuficienteException;
use App\Models\GestionInventario;
use App\Models\IncidenciaInventario;
use App\Models\Planta;
use App\Models\RecepcionInventario;
use App\Models\RecursoCatalogo;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class GestionInventarioService
{
    public const ESTADOS = [
        'existencias_revisadas',
        'stock_suficiente',
        'stock_insuficiente',
        'reposicion_gestionada',
        'compra_realizada',
        'material_recibido',
        'en_incidencia',
        'incidencia_gestionada',
        'recepcion_conforme',
        'ingreso_registrado',
        'almacenado',
        'cerrada',
    ];

    public const TRANSICIONES = [
        'existencias_revisadas' => ['stock_suficiente', 'stock_insuficiente'],
        'stock_suficiente' => ['cerrada'],
        'stock_insuficiente' => ['reposicion_gestionada'],
        'reposicion_gestionada' => ['compra_realizada'],
        'compra_realizada' => ['material_recibido'],
        'material_recibido' => ['recepcion_conforme', 'en_incidencia'],
        'en_incidencia' => ['incidencia_gestionada'],
        'incidencia_gestionada' => ['material_recibido'],
        'recepcion_conforme' => ['ingreso_registrado'],
        'ingreso_registrado' => ['almacenado'],
        'almacenado' => ['cerrada'],
        'cerrada' => [],
    ];

    /** Carril BPMN → roles de Adaptia que pueden actuar (no se crean roles nuevos). */
    public const ROLES_POR_CARRIL = [
        'almacen' => ['vendedor', 'administrador'],
        'compras' => ['administrador'],
    ];

    /** Carril responsable de la actividad que se ejecuta desde cada estado. */
    public const CARRIL_POR_ESTADO = [
        'existencias_revisadas' => 'almacen',
        'stock_suficiente' => 'almacen',
        'stock_insuficiente' => 'compras',
        'reposicion_gestionada' => 'compras',
        'compra_realizada' => 'almacen',
        'material_recibido' => 'almacen',
        'en_incidencia' => 'compras',
        'incidencia_gestionada' => 'almacen',
        'recepcion_conforme' => 'almacen',
        'ingreso_registrado' => 'almacen',
        'almacenado' => 'almacen',
    ];

    /** Mensaje simple que se muestra en el detalle según el estado de la gestión. */
    public const MENSAJES_ESTADO = [
        'existencias_revisadas' => 'Se revisaron las existencias. Falta verificar el estado y las cantidades.',
        'stock_suficiente' => 'El stock cubre lo requerido. Falta registrar el movimiento de salida.',
        'stock_insuficiente' => 'El stock no alcanza. Falta gestionar la reposición.',
        'reposicion_gestionada' => 'La reposición está definida. Falta registrar la compra o abastecimiento.',
        'compra_realizada' => 'La compra está hecha. Falta recibir el material.',
        'material_recibido' => 'Se recibió material. Falta verificar la recepción.',
        'en_incidencia' => 'La recepción tuvo una diferencia. Falta gestionarla con el proveedor.',
        'incidencia_gestionada' => 'La diferencia fue gestionada. Falta volver a recibir el material.',
        'recepcion_conforme' => 'La recepción es conforme. Falta registrar el ingreso.',
        'ingreso_registrado' => 'El ingreso está registrado. Falta almacenar el material.',
        'almacenado' => 'El material está almacenado. Falta registrar el movimiento y actualizar las existencias.',
        'cerrada' => 'El proceso terminó y las existencias quedaron actualizadas.',
    ];

    public const TIPOS_ITEM = ['planta', 'insumo'];

    private const RELACIONES = ['planta', 'recurso', 'creador', 'recepciones', 'incidencias', 'movimientos'];

    public function __construct(
        private readonly SuficienciaInventarioService $suficiencia,
        private readonly MovimientoGestionInventarioService $movimientos,
    ) {
    }

    // ------------------------------------------------------------------
    // Consultas
    // ------------------------------------------------------------------

    public function listar(array $filtros = [], int $porPagina = 15)
    {
        $consulta = GestionInventario::query()->with(['planta', 'recurso', 'creador']);
        $aplicados = [];

        $estado = $filtros['estado'] ?? null;
        if (is_string($estado) && in_array($estado, self::ESTADOS, true)) {
            $consulta->where('estado', $estado);
            $aplicados['estado'] = $estado;
        }

        $tipo = $filtros['tipo_item'] ?? null;
        if (is_string($tipo) && in_array($tipo, self::TIPOS_ITEM, true)) {
            $consulta->where('tipo_item', $tipo);
            $aplicados['tipo_item'] = $tipo;
        }

        $busqueda = $filtros['q'] ?? null;
        if (is_string($busqueda)) {
            $busqueda = trim(mb_substr($busqueda, 0, 100));
            if ($busqueda !== '') {
                $consulta->where(function ($sub) use ($busqueda) {
                    $sub->where('codigo', 'like', "%{$busqueda}%")
                        ->orWhere('motivo', 'like', "%{$busqueda}%")
                        ->orWhereHas('planta', fn ($q) => $q->where('nombre', 'like', "%{$busqueda}%"))
                        ->orWhereHas('recurso', fn ($q) => $q->where('nombre', 'like', "%{$busqueda}%"));
                });
                $aplicados['q'] = $busqueda;
            }
        }

        return $consulta->orderByDesc('created_at')->orderByDesc('id')->paginate($porPagina)->appends($aplicados);
    }

    public function obtener(int $id): GestionInventario
    {
        return GestionInventario::query()->with(self::RELACIONES)->findOrFail($id);
    }

    public function plantasDisponibles()
    {
        return Planta::query()->orderBy('nombre')->get(['id', 'nombre', 'stock_actual', 'stock_minimo']);
    }

    public function insumosDisponibles()
    {
        return RecursoCatalogo::query()->where('tipo', 'material')->orderBy('nombre')
            ->get(['id', 'nombre', 'unidad', 'stock_actual', 'stock_minimo']);
    }

    public function puedeTransicionar(string $desde, string $hacia): bool
    {
        return in_array($hacia, self::TRANSICIONES[$desde] ?? [], true);
    }

    public function carrilDeEstado(string $estado): ?string
    {
        return self::CARRIL_POR_ESTADO[$estado] ?? null;
    }

    public function puedeActuar(User $usuario, GestionInventario $gestion): bool
    {
        $carril = $this->carrilDeEstado($gestion->estado);

        return $carril !== null && in_array($usuario->rol, self::ROLES_POR_CARRIL[$carril], true);
    }

    // ------------------------------------------------------------------
    // Actividades del macroproceso
    // ------------------------------------------------------------------

    /** Actividad 1: revisar existencias de plantas o insumos. */
    public function crear(User $usuario, array $datos): GestionInventario
    {
        $this->autorizarCarril($usuario, 'almacen');

        $validados = $this->validar($datos, [
            'tipo_item' => ['required', Rule::in(self::TIPOS_ITEM)],
            'planta_id' => ['required_if:tipo_item,planta', 'nullable', 'integer', 'exists:plantas,id'],
            'recurso_id' => [
                'required_if:tipo_item,insumo', 'nullable', 'integer',
                Rule::exists('recursos_catalogo', 'id')->where('tipo', 'material'),
            ],
            'cantidad_requerida' => ['required', 'numeric', 'gt:0', 'max:99999999'],
            'motivo' => ['nullable', 'string', 'max:2000'],
        ]);

        $cantidad = (float) $validados['cantidad_requerida'];
        if ($validados['tipo_item'] === 'planta' && !$this->suficiencia->esEntera($cantidad)) {
            throw $this->errorCampo('cantidad_requerida', 'Las plantas se cuentan en unidades enteras.');
        }

        return DB::transaction(function () use ($usuario, $validados, $cantidad) {
            $esPlanta = $validados['tipo_item'] === 'planta';
            $stock = $esPlanta
                ? (float) Planta::query()->findOrFail($validados['planta_id'])->stock_actual
                : (float) RecursoCatalogo::query()->findOrFail($validados['recurso_id'])->stock_actual;

            $gestion = GestionInventario::create([
                'codigo' => 'TMP-' . Str::uuid(),
                'tipo_item' => $validados['tipo_item'],
                'planta_id' => $esPlanta ? $validados['planta_id'] : null,
                'recurso_id' => $esPlanta ? null : $validados['recurso_id'],
                'cantidad_requerida' => $cantidad,
                'motivo' => $validados['motivo'] ?? null,
                'stock_sistema' => $stock,
                'estado' => 'existencias_revisadas',
                'creador_id' => $usuario->id,
            ]);
            $gestion->update(['codigo' => sprintf('INV-%s-%05d', now()->format('Y'), $gestion->id)]);

            return $gestion->fresh(self::RELACIONES);
        });
    }

    /** Actividades 2 y 3: verificar estado y cantidades; decidir si el stock es suficiente. */
    public function verificarEstado(GestionInventario $gestion, User $usuario, array $datos): GestionInventario
    {
        return $this->ejecutar($gestion, $usuario, ['existencias_revisadas'], function (GestionInventario $g) use ($datos) {
            $v = $this->validar($datos, [
                'cantidad_fisica' => ['required', 'numeric', 'min:0', 'max:99999999'],
                'cantidad_buen_estado' => ['required', 'numeric', 'min:0', 'max:99999999'],
                'observaciones_verificacion' => ['nullable', 'string', 'max:2000'],
            ]);
            $this->exigirEntero($g, 'cantidad_fisica', (float) $v['cantidad_fisica']);
            $this->exigirEntero($g, 'cantidad_buen_estado', (float) $v['cantidad_buen_estado']);

            $stock = $this->stockActual($g);
            $evaluacion = $this->suficiencia->evaluar(
                (float) $g->cantidad_requerida,
                (float) $v['cantidad_fisica'],
                (float) $v['cantidad_buen_estado'],
                $stock,
            );

            $g->fill([
                'stock_sistema' => $stock,
                'cantidad_fisica' => $v['cantidad_fisica'],
                'cantidad_buen_estado' => $v['cantidad_buen_estado'],
                'cantidad_disponible' => $evaluacion['disponible'],
                'faltante' => $evaluacion['faltante'],
                'observaciones_verificacion' => $v['observaciones_verificacion'] ?? null,
            ]);

            return $evaluacion['suficiente'] ? 'stock_suficiente' : 'stock_insuficiente';
        });
    }

    /** Actividad: gestionar reposición (compras). */
    public function gestionarReposicion(GestionInventario $gestion, User $usuario, array $datos): GestionInventario
    {
        return $this->ejecutar($gestion, $usuario, ['stock_insuficiente'], function (GestionInventario $g) use ($datos) {
            $v = $this->validar($datos, [
                'cantidad_reposicion' => ['required', 'numeric', 'gt:0', 'max:99999999'],
                'proveedor' => ['required', 'string', 'max:150'],
                'costo_estimado' => ['nullable', 'numeric', 'min:0', 'max:9999999999'],
            ]);
            $cantidad = (float) $v['cantidad_reposicion'];
            $this->exigirEntero($g, 'cantidad_reposicion', $cantidad);
            if (round($cantidad, 2) < round((float) $g->faltante, 2)) {
                throw $this->errorCampo('cantidad_reposicion', "La reposición debe cubrir al menos el faltante ({$g->faltante}).");
            }

            $g->fill([
                'cantidad_reposicion' => $cantidad,
                'proveedor' => trim($v['proveedor']),
                'costo_estimado' => $v['costo_estimado'] ?? null,
            ]);

            return 'reposicion_gestionada';
        });
    }

    /** Actividad: realizar compra o abastecimiento (compras). */
    public function registrarCompra(GestionInventario $gestion, User $usuario, array $datos): GestionInventario
    {
        return $this->ejecutar($gestion, $usuario, ['reposicion_gestionada'], function (GestionInventario $g) use ($datos) {
            $v = $this->validar($datos, [
                'numero_orden' => ['required', 'string', 'max:60'],
                'fecha_compra' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
                'costo_total' => ['nullable', 'numeric', 'min:0', 'max:9999999999'],
            ]);

            $g->fill([
                'numero_orden' => trim($v['numero_orden']),
                'fecha_compra' => $v['fecha_compra'],
                'costo_total' => $v['costo_total'] ?? null,
            ]);

            return 'compra_realizada';
        });
    }

    /** Actividad: recibir plantas o insumos (también tras gestionar una incidencia). */
    public function registrarRecepcion(GestionInventario $gestion, User $usuario, array $datos): GestionInventario
    {
        return $this->ejecutar($gestion, $usuario, ['compra_realizada', 'incidencia_gestionada'], function (GestionInventario $g) use ($datos, $usuario) {
            $v = $this->validar($datos, [
                'cantidad_recibida' => ['required', 'numeric', 'gt:0', 'max:99999999'],
                'observaciones' => ['nullable', 'string', 'max:2000'],
            ]);
            $this->exigirEntero($g, 'cantidad_recibida', (float) $v['cantidad_recibida']);

            $g->recepciones()->create([
                'cantidad_recibida' => $v['cantidad_recibida'],
                'observaciones' => $v['observaciones'] ?? null,
                'autor_id' => $usuario->id,
            ]);

            return 'material_recibido';
        });
    }

    /** Actividad: verificar recepción. Decide recepción conforme o incidencia. */
    public function verificarRecepcion(GestionInventario $gestion, User $usuario, array $datos): GestionInventario
    {
        return $this->ejecutar($gestion, $usuario, ['material_recibido'], function (GestionInventario $g) use ($datos, $usuario) {
            $v = $this->validar($datos, [
                'conforme' => ['required', 'boolean'],
                'tipo_incidencia' => ['nullable', Rule::in(SuficienciaInventarioService::TIPOS_INCIDENCIA)],
                'descripcion' => ['nullable', 'string', 'max:2000'],
                'cantidad_afectada' => ['nullable', 'numeric', 'gt:0', 'max:99999999'],
            ]);

            $recepcion = $g->recepciones()->whereNull('conforme')->latest('id')->first();
            if (!$recepcion) {
                throw new InvalidArgumentException('No hay una recepción pendiente de verificar.');
            }

            $aceptadaPrevia = (float) $g->recepciones()->whereNotNull('conforme')->sum('cantidad_aceptada');
            $recibida = (float) $recepcion->cantidad_recibida;
            $evaluacion = $this->suficiencia->evaluarRecepcion((float) $g->cantidad_reposicion, $aceptadaPrevia, $recibida);
            $conforme = filter_var($v['conforme'], FILTER_VALIDATE_BOOLEAN);

            if ($conforme) {
                if (!$evaluacion['coincide']) {
                    throw new InvalidArgumentException(
                        'Lo recibido no coincide con lo solicitado (diferencia: ' . $evaluacion['diferencia'] . '). Marca la recepción como no conforme y registra la incidencia.'
                    );
                }

                $recepcion->update([
                    'conforme' => true,
                    'cantidad_aceptada' => $recibida,
                    'verificada_por_id' => $usuario->id,
                    'verificada_en' => now(),
                ]);

                return 'recepcion_conforme';
            }

            if (blank($v['tipo_incidencia'] ?? null)) {
                throw $this->errorCampo('tipo_incidencia', 'Selecciona el tipo de diferencia o incidencia.');
            }
            if (blank($v['descripcion'] ?? null)) {
                throw $this->errorCampo('descripcion', 'Describe la diferencia o incidencia.');
            }
            $afectada = isset($v['cantidad_afectada']) ? (float) $v['cantidad_afectada'] : null;
            if ($afectada !== null) {
                $this->exigirEntero($g, 'cantidad_afectada', $afectada);
            }

            $calculo = $this->suficiencia->clasificarIncidencia($v['tipo_incidencia'], $evaluacion, $recibida, $afectada);

            $recepcion->update([
                'conforme' => false,
                'cantidad_aceptada' => $calculo['cantidad_aceptada'],
                'verificada_por_id' => $usuario->id,
                'verificada_en' => now(),
            ]);
            $g->incidencias()->create([
                'recepcion_id' => $recepcion->id,
                'tipo' => $v['tipo_incidencia'],
                'cantidad_afectada' => $calculo['cantidad_afectada'],
                'descripcion' => trim($v['descripcion']),
                'estado' => 'abierta',
                'autor_id' => $usuario->id,
            ]);

            return 'en_incidencia';
        });
    }

    /** Actividad: gestionar diferencia o incidencia (compras). */
    public function gestionarIncidencia(GestionInventario $gestion, User $usuario, array $datos): GestionInventario
    {
        return $this->ejecutar($gestion, $usuario, ['en_incidencia'], function (GestionInventario $g) use ($datos, $usuario) {
            $v = $this->validar($datos, ['resolucion' => ['required', 'string', 'max:2000']]);

            $abiertas = $g->incidencias()->where('estado', 'abierta')->get();
            if ($abiertas->isEmpty()) {
                throw new InvalidArgumentException('No hay incidencias abiertas para gestionar.');
            }

            $abiertas->each(fn (IncidenciaInventario $incidencia) => $incidencia->update([
                'estado' => 'gestionada',
                'resolucion' => trim($v['resolucion']),
                'gestionada_por_id' => $usuario->id,
                'gestionada_en' => now(),
            ]));

            return 'incidencia_gestionada';
        });
    }

    /** Actividad: registrar ingreso (recepción conforme). */
    public function registrarIngreso(GestionInventario $gestion, User $usuario, array $datos): GestionInventario
    {
        return $this->ejecutar($gestion, $usuario, ['recepcion_conforme'], function (GestionInventario $g) use ($datos) {
            $v = $this->validar($datos, ['documento_ingreso' => ['nullable', 'string', 'max:100']]);

            $g->fill([
                'cantidad_ingresada' => round((float) $g->recepciones()->whereNotNull('conforme')->sum('cantidad_aceptada'), 2),
                'documento_ingreso' => filled($v['documento_ingreso'] ?? null) ? trim($v['documento_ingreso']) : null,
            ]);

            return 'ingreso_registrado';
        });
    }

    /** Actividad: almacenar u organizar. */
    public function almacenar(GestionInventario $gestion, User $usuario, array $datos): GestionInventario
    {
        return $this->ejecutar($gestion, $usuario, ['ingreso_registrado'], function (GestionInventario $g) use ($datos) {
            $v = $this->validar($datos, ['ubicacion' => ['required', 'string', 'max:120']]);
            $g->ubicacion = trim($v['ubicacion']);

            return 'almacenado';
        });
    }

    /**
     * Actividad: registrar movimiento de inventario y actualizar existencias.
     * Con stock suficiente registra la salida del requerimiento; tras la reposición, la entrada.
     */
    public function registrarMovimiento(GestionInventario $gestion, User $usuario): GestionInventario
    {
        return $this->ejecutar($gestion, $usuario, ['stock_suficiente', 'almacenado'], function (GestionInventario $g) use ($usuario) {
            try {
                if ($g->estado === 'stock_suficiente') {
                    $this->movimientos->registrar($g, 'salida', (float) $g->cantidad_requerida, "Salida por requerimiento {$g->codigo}", $usuario->id);
                    $g->resultado = 'stock_suficiente';
                } else {
                    $this->movimientos->registrar($g, 'entrada', (float) $g->cantidad_ingresada, "Ingreso por reposición {$g->codigo}", $usuario->id);
                    $g->resultado = 'reposicion_completada';
                }
            } catch (StockInsuficienteException $excepcion) {
                throw new InvalidArgumentException($excepcion->getMessage());
            }

            $g->cerrada_en = now();

            return 'cerrada';
        });
    }

    // ------------------------------------------------------------------
    // Soporte interno
    // ------------------------------------------------------------------

    /**
     * Bloquea la gestión, comprueba estado de origen y carril, ejecuta la actividad
     * (que devuelve el estado destino) y guarda la transición.
     */
    private function ejecutar(GestionInventario $gestion, User $usuario, array $estadosOrigen, callable $actividad): GestionInventario
    {
        $this->autorizarEstado($usuario, $gestion->estado);

        return DB::transaction(function () use ($gestion, $usuario, $estadosOrigen, $actividad) {
            $actual = GestionInventario::query()->lockForUpdate()->findOrFail($gestion->id);

            if (!in_array($actual->estado, $estadosOrigen, true)) {
                throw new InvalidArgumentException(
                    'Esta actividad no está disponible cuando la gestión está en "' . str_replace('_', ' ', $actual->estado) . '".'
                );
            }
            $this->autorizarEstado($usuario, $actual->estado);

            $destino = $actividad($actual);

            if (!$this->puedeTransicionar($actual->estado, $destino)) {
                throw new InvalidArgumentException("La transición de '{$actual->estado}' a '{$destino}' no está permitida.");
            }

            $actual->estado = $destino;
            $actual->save();

            return $actual->fresh(self::RELACIONES);
        });
    }

    private function autorizarEstado(User $usuario, string $estado): void
    {
        $carril = $this->carrilDeEstado($estado);
        if ($carril === null) {
            throw new InvalidArgumentException('La gestión ya está cerrada.');
        }

        $this->autorizarCarril($usuario, $carril);
    }

    private function autorizarCarril(User $usuario, string $carril): void
    {
        $rolesPermitidos = self::ROLES_POR_CARRIL[$carril] ?? [];
        $rolActual = User::query()->whereKey($usuario->id)->value('rol');

        if (!in_array($rolActual, $rolesPermitidos, true)) {
            throw new AuthorizationException('No tienes permiso para realizar esta actividad de inventario.');
        }
    }

    private function stockActual(GestionInventario $gestion): float
    {
        return $gestion->tipo_item === 'planta'
            ? (float) Planta::query()->whereKey($gestion->planta_id)->value('stock_actual')
            : (float) RecursoCatalogo::query()->whereKey($gestion->recurso_id)->value('stock_actual');
    }

    private function exigirEntero(GestionInventario $gestion, string $campo, float $cantidad): void
    {
        if ($gestion->tipo_item === 'planta' && !$this->suficiencia->esEntera($cantidad)) {
            throw $this->errorCampo($campo, 'Las plantas se cuentan en unidades enteras.');
        }
    }

    private function validar(array $datos, array $reglas): array
    {
        return Validator::make($datos, $reglas)->validate();
    }

    private function errorCampo(string $campo, string $mensaje): \Illuminate\Validation\ValidationException
    {
        return \Illuminate\Validation\ValidationException::withMessages([$campo => $mensaje]);
    }
}
