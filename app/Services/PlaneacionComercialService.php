<?php

namespace App\Services;

use App\Models\CampanaComercial;
use App\Models\Planta;
use App\Models\ProductoCampana;
use App\Models\SolicitudDisponibilidad;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class PlaneacionComercialService
{
    public const ESTADOS = [
        'objetivos_definidos',
        'demanda_analizada',
        'productos_identificados',
        'oferta_disponible',
        'gestion_disponibilidad',
        'oferta_por_ajustar',
        'oferta_planificada',
        'promocion_preparada',
        'promocion_realizada',
        'resultados_evaluados',
        'cerrada',
    ];

    public const TRANSICIONES = [
        'objetivos_definidos' => ['demanda_analizada'],
        'demanda_analizada' => ['productos_identificados'],
        'productos_identificados' => ['oferta_disponible', 'gestion_disponibilidad'],
        'oferta_disponible' => ['oferta_planificada'],
        'gestion_disponibilidad' => ['productos_identificados', 'oferta_por_ajustar'],
        'oferta_por_ajustar' => ['oferta_planificada'],
        'oferta_planificada' => ['promocion_preparada'],
        'promocion_preparada' => ['promocion_realizada'],
        'promocion_realizada' => ['resultados_evaluados'],
        'resultados_evaluados' => ['cerrada'],
        'cerrada' => [],
    ];

    public const ROLES_POR_CARRIL = [
        'comercial' => 'vendedor',
        'produccion' => 'administrador',
        'administracion' => 'administrador',
    ];

    private const CARRIL_POR_TRANSICION = [
        'objetivos_definidos>demanda_analizada' => 'comercial',
        'demanda_analizada>productos_identificados' => 'comercial',
        'productos_identificados>oferta_disponible' => 'produccion',
        'productos_identificados>gestion_disponibilidad' => 'produccion',
        'gestion_disponibilidad>productos_identificados' => 'produccion',
        'gestion_disponibilidad>oferta_por_ajustar' => 'produccion',
        'oferta_disponible>oferta_planificada' => 'comercial',
        'oferta_por_ajustar>oferta_planificada' => 'comercial',
        'oferta_planificada>promocion_preparada' => 'comercial',
        'promocion_preparada>promocion_realizada' => 'comercial',
        'promocion_realizada>resultados_evaluados' => 'administracion',
        'resultados_evaluados>cerrada' => 'administracion',
    ];

    public function __construct(
        private readonly DisponibilidadCampanaService $disponibilidad,
        private readonly EvaluacionCampanaService $evaluacion,
    ) {}

    public function crear(User $usuario, array $datos): CampanaComercial
    {
        $this->autorizarCarril($usuario, 'comercial');
        $validados = $this->validarDatosCampana($datos);

        return DB::transaction(function () use ($usuario, $validados) {
            $campana = CampanaComercial::create([
                'codigo' => 'TMP-' . Str::uuid(),
                'objetivos' => $validados['objetivos'],
                'presupuesto' => $validados['presupuesto'],
                'fecha_inicio' => $validados['fecha_inicio'],
                'fecha_fin' => $validados['fecha_fin'],
                'estado' => 'objetivos_definidos',
                'creador_id' => $usuario->id,
            ]);

            $campana->update([
                'codigo' => sprintf('CAM-%s-%05d', now()->format('Y'), $campana->id),
            ]);

            foreach ($validados['productos'] as $producto) {
                $campana->productos()->create($producto + ['incluida' => true]);
            }

            return $campana->fresh(['productos.planta']);
        });
    }

    public function listar(array $filtros = [], int $porPagina = 15)
    {
        $consulta = CampanaComercial::query()->with('creador');
        $aplicados = [];

        $estado = $filtros['estado'] ?? null;
        if (is_string($estado) && in_array($estado, self::ESTADOS, true)) {
            $consulta->where('estado', $estado);
            $aplicados['estado'] = $estado;
        }

        $busqueda = $filtros['q'] ?? null;
        if (is_string($busqueda)) {
            $busqueda = trim(mb_substr($busqueda, 0, 100));
            if ($busqueda !== '') {
                $consulta->where(function ($subconsulta) use ($busqueda) {
                    $subconsulta->where('codigo', 'like', "%{$busqueda}%")
                        ->orWhere('objetivos', 'like', "%{$busqueda}%");
                });
                $aplicados['q'] = $busqueda;
            }
        }

        return $consulta->withCount(['productos' => fn ($query) => $query->where('incluida', true)])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($porPagina)
            ->appends($aplicados);
    }

    public function obtener(int $id): CampanaComercial
    {
        return CampanaComercial::query()
            ->with([
                'creador',
                'productos.planta',
                'productos.solicitudDisponibilidad.gestionadaPor',
            ])
            ->findOrFail($id);
    }

    public function buscarPorCodigo(string $codigo): ?CampanaComercial
    {
        return CampanaComercial::query()
            ->with(['productos.planta', 'productos.solicitudDisponibilidad'])
            ->where('codigo', strtoupper(trim($codigo)))
            ->first();
    }

    public function consultarCampana(string $codigo): array
    {
        $campana = $this->buscarPorCodigo($codigo);
        if (!$campana) {
            return ['encontrada' => false, 'codigo' => strtoupper(trim($codigo))];
        }

        return [
            'encontrada' => true,
            'codigo' => $campana->codigo,
            'estado' => $campana->estado,
            'objetivos' => $campana->objetivos,
            'fecha_inicio' => $campana->fecha_inicio->toDateString(),
            'fecha_fin' => $campana->fecha_fin->toDateString(),
            'productos' => $campana->productos
                ->where('incluida', true)
                ->take(20)
                ->map(fn (ProductoCampana $producto) => [
                    'planta' => $producto->planta->nombre,
                    'cantidad_meta' => $producto->cantidad_meta,
                    'descuento_porcentaje' => $producto->descuento_porcentaje,
                    'solicitud_disponibilidad' => $producto->solicitudDisponibilidad?->estado,
                ])
                ->values()
                ->all(),
        ];
    }

    public function campanasPorEstado(string $estado, int $limite = 20): array
    {
        if (!in_array($estado, self::ESTADOS, true) || $limite < 1 || $limite > 50) {
            throw new InvalidArgumentException('El estado o límite de campañas no es válido.');
        }

        return CampanaComercial::query()
            ->where('estado', $estado)
            ->orderByDesc('created_at')
            ->limit($limite)
            ->get(['codigo', 'estado', 'objetivos', 'fecha_inicio', 'fecha_fin'])
            ->map(fn (CampanaComercial $campana) => [
                'codigo' => $campana->codigo,
                'estado' => $campana->estado,
                'objetivos' => $campana->objetivos,
                'fecha_inicio' => $campana->fecha_inicio->toDateString(),
                'fecha_fin' => $campana->fecha_fin->toDateString(),
            ])
            ->all();
    }

    public function solicitudesPendientes(int $limite = 30): array
    {
        if ($limite < 1 || $limite > 50) {
            throw new InvalidArgumentException('El límite de solicitudes debe estar entre 1 y 50.');
        }

        return SolicitudDisponibilidad::query()
            ->where('estado', 'pendiente')
            ->whereHas('productoCampana', fn ($query) => $query->where('incluida', true))
            ->with(['productoCampana.campana', 'productoCampana.planta'])
            ->orderBy('created_at')
            ->limit($limite)
            ->get()
            ->map(fn (SolicitudDisponibilidad $solicitud) => [
                'codigo_campana' => $solicitud->productoCampana->campana->codigo,
                'planta' => $solicitud->productoCampana->planta->nombre,
                'faltante' => $solicitud->faltante,
                'notas' => $solicitud->notas,
                'solicitada_en' => $solicitud->created_at?->toDateTimeString(),
            ])
            ->all();
    }

    public function plantasDisponibles()
    {
        return Planta::query()
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'stock_actual', 'stock_minimo', 'precio']);
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

    public function cambiarEstado(
        CampanaComercial $campana,
        string $nuevoEstado,
        User $usuario,
        array $datos = []
    ): CampanaComercial {
        $nuevoEstado = strtolower(trim($nuevoEstado));
        $estadoAnterior = $campana->estado;

        if (!$this->puedeTransicionar($estadoAnterior, $nuevoEstado)) {
            throw new InvalidArgumentException(
                "La transición de '{$estadoAnterior}' a '{$nuevoEstado}' no está permitida."
            );
        }

        $carril = self::CARRIL_POR_TRANSICION["{$estadoAnterior}>{$nuevoEstado}"] ?? null;
        if ($carril === null) {
            throw new InvalidArgumentException('La transición no tiene un carril responsable configurado.');
        }
        $this->autorizarCarril($usuario, $carril);

        return DB::transaction(function () use ($campana, $nuevoEstado, $usuario, $datos) {
            $actual = CampanaComercial::query()->lockForUpdate()->findOrFail($campana->id);

            if (!$this->puedeTransicionar($actual->estado, $nuevoEstado)) {
                throw new InvalidArgumentException(
                    "La transición de '{$actual->estado}' a '{$nuevoEstado}' no está permitida."
                );
            }

            $this->validarReglasTransicion($actual, $nuevoEstado, $datos);

            if ($nuevoEstado === 'resultados_evaluados') {
                $this->guardarEvaluacion($actual);
            }

            if ($nuevoEstado === 'cerrada') {
                $actual->ajustes_siguiente_ciclo = trim($datos['ajustes_siguiente_ciclo']);
            }

            $actual->estado = $nuevoEstado;
            $actual->save();

            return $actual->fresh(['productos.planta']);
        });
    }

    public function ajustarOferta(CampanaComercial $campana, User $usuario, array $productos): CampanaComercial
    {
        $this->autorizarCarril($usuario, 'comercial');
        $productosValidados = $this->validarProductos($productos);

        return DB::transaction(function () use ($campana, $productosValidados) {
            $actual = CampanaComercial::query()->lockForUpdate()->findOrFail($campana->id);
            if ($actual->estado !== 'oferta_por_ajustar') {
                throw new InvalidArgumentException('La oferta solo puede ajustarse cuando está pendiente de ajuste.');
            }

            $idsIncluidos = array_column($productosValidados, 'planta_id');
            $actual->productos()->whereNotIn('planta_id', $idsIncluidos)->update(['incluida' => false]);

            foreach ($productosValidados as $producto) {
                $actual->productos()->updateOrCreate(
                    ['planta_id' => $producto['planta_id']],
                    $producto + ['incluida' => true]
                );
            }

            return $actual->fresh(['productos.planta']);
        });
    }

    public function gestionarSolicitud(
        SolicitudDisponibilidad $solicitud,
        User $usuario,
        string $estado,
        ?string $notas = null
    ): SolicitudDisponibilidad {
        $this->autorizarCarril($usuario, 'produccion');
        $estado = strtolower(trim($estado));
        if (!in_array($estado, ['atendida', 'no_lograda'], true)) {
            throw new InvalidArgumentException('El resultado debe ser atendida o no_lograda.');
        }
        if ($notas !== null && mb_strlen($notas) > 2000) {
            throw ValidationException::withMessages(['notas' => 'Las notas no pueden superar 2000 caracteres.']);
        }

        return DB::transaction(function () use ($solicitud, $usuario, $estado, $notas) {
            $actual = SolicitudDisponibilidad::query()
                ->with('productoCampana.campana', 'productoCampana.planta')
                ->lockForUpdate()
                ->findOrFail($solicitud->id);

            if ($actual->productoCampana->campana->estado !== 'gestion_disponibilidad'
                || !$actual->productoCampana->incluida) {
                throw new InvalidArgumentException('La solicitud no está en una etapa gestionable.');
            }

            if ($estado === 'atendida'
                && $actual->productoCampana->planta->stock_actual < $actual->productoCampana->cantidad_meta) {
                throw ValidationException::withMessages([
                    'estado' => 'No se puede marcar atendida mientras el stock no cubra la cantidad meta.',
                ]);
            }

            $actual->update([
                'estado' => $estado,
                'notas' => $notas,
                'gestionada_por_id' => $usuario->id,
                'gestionada_en' => now(),
            ]);

            return $actual->fresh(['productoCampana.planta']);
        });
    }

    private function validarDatosCampana(array $datos): array
    {
        $validados = Validator::make($datos, [
            'objetivos' => ['required', 'string', 'max:5000'],
            'presupuesto' => ['required', 'numeric', 'min:0'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'productos' => ['required', 'array', 'min:1'],
        ], [
            'objetivos.required' => 'Los objetivos comerciales son obligatorios.',
            'presupuesto.required' => 'El presupuesto es obligatorio.',
            'fecha_fin.after_or_equal' => 'La fecha final debe ser igual o posterior a la inicial.',
            'productos.min' => 'La campaña debe incluir al menos una planta.',
        ])->validate();

        $validados['productos'] = $this->validarProductos($validados['productos']);

        return $validados;
    }

    private function validarProductos(array $productos): array
    {
        $validados = Validator::make(['productos' => $productos], [
            'productos' => ['required', 'array', 'min:1'],
            'productos.*.planta_id' => ['required', 'integer', 'distinct', 'exists:plantas,id'],
            'productos.*.cantidad_meta' => ['required', 'integer', 'min:1'],
            'productos.*.descuento_porcentaje' => ['nullable', 'numeric', 'between:0,100'],
        ], [
            'productos.min' => 'La oferta debe incluir al menos una planta.',
            'productos.*.planta_id.distinct' => 'No se puede repetir una planta en la campaña.',
            'productos.*.descuento_porcentaje.between' => 'El descuento debe estar entre 0 y 100.',
        ])->validate()['productos'];

        return array_map(fn (array $producto) => [
            'planta_id' => (int) $producto['planta_id'],
            'cantidad_meta' => (int) $producto['cantidad_meta'],
            'descuento_porcentaje' => $producto['descuento_porcentaje'] ?? null,
        ], $validados);
    }

    private function validarReglasTransicion(CampanaComercial $campana, string $nuevoEstado, array $datos): void
    {
        if ($campana->estado === 'productos_identificados') {
            $veredicto = $this->disponibilidad->verificar($campana);
            if (($nuevoEstado === 'oferta_disponible') !== $veredicto['suficiente']) {
                throw ValidationException::withMessages([
                    'estado' => $veredicto['suficiente']
                        ? 'La disponibilidad es suficiente; no corresponde solicitar gestión.'
                        : 'Hay productos sin stock suficiente; no se puede continuar como disponible.',
                ]);
            }
            if ($nuevoEstado === 'gestion_disponibilidad') {
                $this->registrarFaltantes($campana, $veredicto['faltantes']);
            }
        }

        if ($campana->estado === 'gestion_disponibilidad') {
            $solicitudes = $campana->productos()
                ->where('incluida', true)
                ->with('solicitudDisponibilidad')
                ->get()
                ->pluck('solicitudDisponibilidad')
                ->filter();

            if ($solicitudes->isEmpty() || $solicitudes->contains(fn ($item) => $item->estado === 'pendiente')) {
                throw ValidationException::withMessages([
                    'estado' => 'Todas las solicitudes activas deben tener un resultado antes de continuar.',
                ]);
            }

            if ($nuevoEstado === 'productos_identificados') {
                $veredicto = $this->disponibilidad->verificar($campana);
                if ($solicitudes->contains(fn ($item) => $item->estado !== 'atendida') || !$veredicto['suficiente']) {
                    throw ValidationException::withMessages([
                        'estado' => 'Solo se puede volver a revisar cuando las solicitudes fueron atendidas y hay stock suficiente.',
                    ]);
                }
            } elseif (!$solicitudes->contains(fn ($item) => $item->estado === 'no_lograda')) {
                throw ValidationException::withMessages([
                    'estado' => 'Se requiere al menos una solicitud no lograda para ajustar la oferta.',
                ]);
            }
        }

        if (in_array($nuevoEstado, ['oferta_planificada'], true)) {
            if (!$this->disponibilidad->verificar($campana)['suficiente']) {
                throw ValidationException::withMessages([
                    'estado' => 'No se puede planificar una oferta con disponibilidad insuficiente.',
                ]);
            }
        }

        if ($nuevoEstado === 'resultados_evaluados') {
            if ($campana->fecha_fin->isToday() || $campana->fecha_fin->isFuture()) {
                throw ValidationException::withMessages([
                    'fecha_fin' => 'La campaña solo se puede evaluar después de la fecha final.',
                ]);
            }
            if (!$campana->productos()->where('incluida', true)->exists()) {
                throw ValidationException::withMessages([
                    'productos' => 'No hay productos activos para evaluar.',
                ]);
            }
        }

        if ($nuevoEstado === 'cerrada') {
            $validator = Validator::make($datos, [
                'ajustes_siguiente_ciclo' => ['required', 'string', 'max:5000'],
            ], [
                'ajustes_siguiente_ciclo.required' => 'Documenta los ajustes para el siguiente ciclo.',
            ]);
            $validator->validate();
        }
    }

    private function registrarFaltantes(CampanaComercial $campana, array $faltantes): void
    {
        foreach ($faltantes as $faltante) {
            SolicitudDisponibilidad::updateOrCreate(
                ['producto_campana_id' => $faltante['producto_campana_id']],
                [
                    'faltante' => $faltante['faltante'],
                    'estado' => 'pendiente',
                    'notas' => null,
                    'gestionada_por_id' => null,
                    'gestionada_en' => null,
                ]
            );
        }
    }

    private function guardarEvaluacion(CampanaComercial $campana): void
    {
        foreach ($this->evaluacion->evaluar($campana) as $resultado) {
            ProductoCampana::query()
                ->whereKey($resultado['producto_campana_id'])
                ->update([
                    'cantidad_vendida_evaluada' => $resultado['cantidad_vendida'],
                    'porcentaje_cumplimiento' => $resultado['porcentaje_cumplimiento'],
                    'ingresos_estimados' => $resultado['ingresos_estimados'],
                ]);
        }

        $campana->evaluada_en = now();
    }

    private function autorizarCarril(User $usuario, string $carril): void
    {
        $rolRequerido = self::ROLES_POR_CARRIL[$carril] ?? null;
        $rolActual = User::query()->whereKey($usuario->id)->value('rol');

        if ($rolRequerido === null || $rolActual !== $rolRequerido) {
            throw new AuthorizationException('No tienes permiso para realizar esta actividad comercial.');
        }
    }
}
