<?php

namespace App\Services;

use App\Models\CuidadoLote;
use App\Models\LoteProduccion;
use App\Models\Planta;
use App\Models\RevisionLote;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class ProduccionVegetalService
{
    // Responsable de producción = administrador (mismo mapeo que Planeación comercial).
    public const ROL_RESPONSABLE = 'administrador';

    public const ESTADOS = [
        'necesidad_identificada',
        'especie_seleccionada',
        'material_seleccionado',
        'sustrato_preparado',
        'propagacion_realizada',
        'plantas_colocadas',
        'en_cuidados',
        'en_revision',
        'clasificada',
        'cerrado',
    ];

    public const TRANSICIONES = [
        'necesidad_identificada' => ['especie_seleccionada'],
        'especie_seleccionada' => ['material_seleccionado'],
        'material_seleccionado' => ['sustrato_preparado'],
        'sustrato_preparado' => ['propagacion_realizada'],
        'propagacion_realizada' => ['plantas_colocadas'],
        'plantas_colocadas' => ['en_cuidados'],
        'en_cuidados' => ['en_revision'],
        'en_revision' => ['en_cuidados', 'clasificada'],
        'clasificada' => ['cerrado'],
        'cerrado' => [],
    ];

    public const MOTIVOS = ['stock_bajo', 'campana', 'pedido', 'otro'];

    public const METODOS = ['semilla', 'esqueje', 'division', 'hijuelo', 'hoja'];

    public const TIPOS_CUIDADO = [
        'riego',
        'luz',
        'abono',
        'proteccion_heladas',
        'limpieza_poda',
        'control_plagas',
    ];

    // ---------- Cálculo puro (sin base de datos) ----------

    public static function transicionPermitida(string $actual, string $destino): bool
    {
        return in_array($destino, self::TRANSICIONES[$actual] ?? [], true);
    }

    public static function plantasVivas(int $sembradas, int $perdidas): int
    {
        return max(0, $sembradas - $perdidas);
    }

    public static function clasificacionCuadra(int $vivas, int $categoriaA, int $categoriaB, int $descarte): bool
    {
        return $categoriaA + $categoriaB + $descarte === $vivas;
    }

    // Solo las categorías A y B son vendibles y pasan a inventario.
    public static function cantidadProducida(int $categoriaA, int $categoriaB): int
    {
        return $categoriaA + $categoriaB;
    }

    // ---------- Consultas ----------

    public function listar(array $filtros = [], int $porPagina = 15)
    {
        $query = LoteProduccion::query()->with(['planta', 'responsable']);

        $estado = $filtros['estado'] ?? null;
        if (is_string($estado) && in_array($estado, self::ESTADOS, true)) {
            $query->where('estado', $estado);
        }

        return $query->orderByDesc('created_at')->orderByDesc('id')->paginate($porPagina)->withQueryString();
    }

    public function obtener(int $id): LoteProduccion
    {
        return LoteProduccion::with(['planta', 'responsable', 'cuidados', 'revisiones', 'movimiento'])
            ->findOrFail($id);
    }

    // Solo lectura: ayuda a "identificar la necesidad de producto".
    public function plantasBajoMinimo(): Collection
    {
        return Planta::whereColumn('stock_actual', '<', 'stock_minimo')->orderBy('nombre')->get();
    }

    public function plantasParaSeleccion(): Collection
    {
        return Planta::orderBy('nombre')->get(['id', 'nombre']);
    }

    // ---------- Acciones ----------

    public function crear(User $usuario, array $datos): LoteProduccion
    {
        $this->exigirResponsable($usuario);

        $validos = Validator::make($datos, [
            'motivo' => ['required', Rule::in(self::MOTIVOS)],
            'cantidad_meta' => ['required', 'integer', 'min:1', 'max:100000'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
        ])->validate();

        return DB::transaction(function () use ($usuario, $validos) {
            $lote = LoteProduccion::create([
                'codigo' => 'PENDIENTE-' . Str::uuid(),
                'responsable_id' => $usuario->id,
                'motivo' => $validos['motivo'],
                'cantidad_meta' => $validos['cantidad_meta'],
                'observaciones' => $validos['observaciones'] ?? null,
                'estado' => 'necesidad_identificada',
            ]);

            $lote->update(['codigo' => sprintf('PRO-%s-%05d', now()->format('Y'), $lote->id)]);

            return $lote->fresh();
        });
    }

    public function avanzar(LoteProduccion $lote, User $usuario, string $destino, array $datos = []): LoteProduccion
    {
        $this->exigirResponsable($usuario);
        $this->exigirTransicion($lote, $destino);

        $campos = match ($destino) {
            'especie_seleccionada' => Validator::make($datos, [
                'planta_id' => ['required', 'integer', 'exists:plantas,id'],
            ])->validate(),
            'material_seleccionado' => Validator::make($datos, [
                'metodo_propagacion' => ['required', Rule::in(self::METODOS)],
                'cantidad_material' => ['required', 'integer', 'min:1', 'max:100000'],
            ])->validate(),
            'sustrato_preparado' => Validator::make($datos, [
                'sustrato_mezcla' => ['required', 'string', 'max:1000'],
            ])->validate(),
            'propagacion_realizada' => Validator::make($datos, [
                'fecha_propagacion' => ['required', 'date', 'before_or_equal:today'],
            ])->validate(),
            'plantas_colocadas' => Validator::make($datos, [
                'cantidad_sembrada' => ['required', 'integer', 'min:1', 'max:100000'],
            ])->validate(),
            'en_cuidados' => $this->soloDesde($lote, 'plantas_colocadas'),
            'en_revision' => $this->exigirCuidadosPrevios($lote),
            default => throw new InvalidArgumentException('Esta etapa se registra desde su propio formulario.'),
        };

        $lote->update($campos + ['estado' => $destino]);

        return $lote->fresh();
    }

    public function registrarCuidado(LoteProduccion $lote, User $usuario, array $datos): CuidadoLote
    {
        $this->exigirResponsable($usuario);
        $this->exigirEstado($lote, 'en_cuidados', 'Solo se registran cuidados mientras el lote está en cuidados.');

        $validos = Validator::make($datos, [
            'tipo' => ['required', Rule::in(self::TIPOS_CUIDADO)],
            'fecha' => ['required', 'date', 'before_or_equal:today'],
            'observacion' => ['nullable', 'string', 'max:1000'],
        ])->validate();

        return $lote->cuidados()->create($validos + ['user_id' => $usuario->id]);
    }

    public function registrarRevision(LoteProduccion $lote, User $usuario, array $datos): RevisionLote
    {
        $this->exigirResponsable($usuario);
        $this->exigirEstado($lote, 'en_revision', 'Solo se revisa un lote que está en revisión.');

        $vivas = self::plantasVivas((int) $lote->cantidad_sembrada, (int) $lote->cantidad_perdida);

        $validos = Validator::make($datos, [
            'apta' => ['required', 'boolean'],
            'plantas_perdidas' => ['nullable', 'integer', 'min:0', 'max:' . $vivas],
            'observaciones' => ['nullable', 'string', 'max:1000'],
        ])->validate();

        $apta = (bool) $validos['apta'];
        $perdidas = (int) ($validos['plantas_perdidas'] ?? 0);

        if ($apta && $vivas - $perdidas < 1) {
            throw new InvalidArgumentException('No quedan plantas vivas para declarar el lote apto.');
        }

        return DB::transaction(function () use ($lote, $usuario, $apta, $perdidas, $validos) {
            $revision = $lote->revisiones()->create([
                'user_id' => $usuario->id,
                'apta' => $apta,
                'plantas_perdidas' => $perdidas,
                'observaciones' => $validos['observaciones'] ?? null,
            ]);

            $lote->increment('cantidad_perdida', $perdidas);

            // Si no está apta, vuelve a cuidados hasta la próxima revisión.
            if (!$apta) {
                $lote->update(['estado' => 'en_cuidados']);
            }

            return $revision;
        });
    }

    public function clasificar(LoteProduccion $lote, User $usuario, array $datos): LoteProduccion
    {
        $this->exigirResponsable($usuario);
        $this->exigirTransicion($lote, 'clasificada');

        $ultima = $lote->revisiones()->latest('id')->first();
        if (!$ultima || !$ultima->apta) {
            throw new InvalidArgumentException('Para clasificar, la última revisión debe ser apta.');
        }

        $validos = Validator::make($datos, [
            'cantidad_categoria_a' => ['required', 'integer', 'min:0'],
            'cantidad_categoria_b' => ['required', 'integer', 'min:0'],
            'cantidad_descarte' => ['required', 'integer', 'min:0'],
        ])->validate();

        $vivas = self::plantasVivas((int) $lote->cantidad_sembrada, (int) $lote->cantidad_perdida);

        if (!self::clasificacionCuadra(
            $vivas,
            (int) $validos['cantidad_categoria_a'],
            (int) $validos['cantidad_categoria_b'],
            (int) $validos['cantidad_descarte']
        )) {
            throw new InvalidArgumentException("La clasificación debe sumar {$vivas} plantas vivas.");
        }

        $lote->update($validos + ['estado' => 'clasificada']);

        return $lote->fresh();
    }

    // Registra la planta producida y la envía a inventario reutilizando el servicio existente.
    public function cerrar(LoteProduccion $lote, User $usuario): LoteProduccion
    {
        $this->exigirResponsable($usuario);

        return DB::transaction(function () use ($lote, $usuario) {
            $lote = LoteProduccion::lockForUpdate()->findOrFail($lote->id);
            $this->exigirTransicion($lote, 'cerrado');

            if (!$lote->planta_id) {
                throw new InvalidArgumentException('El lote no tiene una especie asignada.');
            }

            $producida = self::cantidadProducida(
                (int) $lote->cantidad_categoria_a,
                (int) $lote->cantidad_categoria_b
            );

            $movimiento = null;
            if ($producida > 0) {
                $movimiento = app(MovimientoInventarioService::class)->createMovimiento([
                    'planta_id' => $lote->planta_id,
                    'user_id' => $usuario->id,
                    'tipo' => 'entrada',
                    'cantidad' => $producida,
                ]);
            }

            $lote->update([
                'cantidad_producida' => $producida,
                'movimiento_inventario_id' => $movimiento?->id,
                'estado' => 'cerrado',
                'cerrado_en' => now(),
            ]);

            return $lote->fresh();
        });
    }

    // ---------- Validaciones internas ----------

    private function exigirResponsable(User $usuario): void
    {
        if ($usuario->rol !== self::ROL_RESPONSABLE) {
            throw new AuthorizationException('Solo el responsable de producción puede operar este macroproceso.');
        }
    }

    private function exigirTransicion(LoteProduccion $lote, string $destino): void
    {
        if (!self::transicionPermitida($lote->estado, $destino)) {
            throw new InvalidArgumentException("No se puede pasar de {$lote->estado} a {$destino}.");
        }
    }

    private function exigirEstado(LoteProduccion $lote, string $estado, string $mensaje): void
    {
        if ($lote->estado !== $estado) {
            throw new InvalidArgumentException($mensaje);
        }
    }

    private function soloDesde(LoteProduccion $lote, string $estado): array
    {
        $this->exigirEstado($lote, $estado, 'Esta transición no corresponde al estado actual del lote.');

        return [];
    }

    private function exigirCuidadosPrevios(LoteProduccion $lote): array
    {
        if (!$lote->cuidados()->exists()) {
            throw new InvalidArgumentException('Registra al menos un cuidado antes de enviar el lote a revisión.');
        }

        return [];
    }
}
