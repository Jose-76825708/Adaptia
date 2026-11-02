<?php

namespace App\Services;

use App\Exceptions\MovimientoNoAnulableException;
use App\Exceptions\StockInsuficienteException;
use App\Models\MovimientoInventario;
use App\Models\Planta;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class MovimientoInventarioService
{
    public const POR_PAGINA = 15;

    private const COLUMNA_FECHA = 'created_at';

    private const FILTROS_PERMITIDOS = ['planta_id', 'tipo', 'desde', 'hasta'];

    public function __construct(private AlertaStockService $alertaStockService)
    {
    }

    /**
     * Obtiene todos los movimientos de inventario con sus relaciones.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return MovimientoInventario::with(['planta', 'user', 'anulacion'])->get();
    }

    /**
     * Lista los movimientos paginados (más recientes primero) con filtros opcionales.
     * Filtros soportados: planta_id, tipo (entrada|salida), desde y hasta (Y-m-d, ambos inclusivos).
     * Cualquier otra clave de $filtros se ignora.
     *
     * @param array $filtros
     * @param int $porPagina
     * @return LengthAwarePaginator
     */
    public function listarPaginado(array $filtros = [], int $porPagina = self::POR_PAGINA): LengthAwarePaginator
    {
        $filtros = Arr::only($filtros, self::FILTROS_PERMITIDOS);

        $query = MovimientoInventario::query()
            ->with(['planta', 'user', 'anulacion'])
            ->orderByDesc(self::COLUMNA_FECHA)
            ->orderByDesc('id');

        return $this->aplicarFiltros($query, $filtros)
            ->paginate($porPagina)
            ->appends($filtros);
    }

    /**
     * Plantas disponibles para el selector del filtro, ordenadas por nombre.
     *
     * @return Collection
     */
    public function plantasParaFiltro(): Collection
    {
        return Planta::query()->orderBy('nombre')->get(['id', 'nombre']);
    }

    /**
     * Busca un movimiento de inventario por su ID.
     *
     * @param string $id
     * @return Model
     */
    public function getById(string $id): Model
    {
        return MovimientoInventario::with(['planta', 'user'])->findOrFail($id);
    }

    /**
     * Crea un nuevo movimiento de inventario.
     *
     * @param array $data
     * @return Model
     */
    public function createMovimiento(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $planta = Planta::query()->lockForUpdate()->findOrFail($data['planta_id']);
            $stockAntes = $planta->stock_actual;

            $this->aplicarCambioStock($planta, $data['tipo'], (int) $data['cantidad']);

            $movimiento = MovimientoInventario::create([
                ...$data,
                'stock_antes' => $stockAntes,
                'stock_despues' => $planta->stock_actual,
            ]);

            $this->alertaStockService->evaluar($planta, 'Stock bajo detectado tras movimiento de inventario', [
                'planta_id' => $planta->id,
                'planta_nombre' => $planta->nombre,
                'stock_actual' => $planta->stock_actual,
                'stock_minimo' => $planta->stock_minimo,
                'tipo_movimiento' => $data['tipo'],
                'cantidad' => $data['cantidad'],
            ]);

            return $movimiento;
        });
    }

    /**
     * Anula un movimiento creando un contramovimiento de signo contrario.
     * El movimiento original nunca se modifica ni se elimina.
     *
     * @param string $id
     * @param string $motivo
     * @param int $userId
     * @return Model
     *
     * @throws MovimientoNoAnulableException
     * @throws StockInsuficienteException
     */
    public function anularMovimiento(string $id, string $motivo, int $userId): Model
    {
        return DB::transaction(function () use ($id, $motivo, $userId) {
            $original = MovimientoInventario::query()->lockForUpdate()->findOrFail($id);

            if ($original->esAnulacion()) {
                throw new MovimientoNoAnulableException('No se puede anular un movimiento que ya es una anulación.');
            }

            if ($original->anulacion()->exists()) {
                throw new MovimientoNoAnulableException('El movimiento ya fue anulado.');
            }

            $planta = Planta::query()->lockForUpdate()->findOrFail($original->planta_id);
            $stockAntes = $planta->stock_actual;
            $tipoInverso = $original->tipo === 'entrada' ? 'salida' : 'entrada';

            $this->aplicarCambioStock($planta, $tipoInverso, (int) $original->cantidad);

            $anulacion = MovimientoInventario::create([
                'planta_id' => $original->planta_id,
                'user_id' => $userId,
                'tipo' => $tipoInverso,
                'cantidad' => $original->cantidad,
                'motivo' => $motivo,
                'stock_antes' => $stockAntes,
                'stock_despues' => $planta->stock_actual,
                'movimiento_original_id' => $original->id,
            ]);

            $this->alertaStockService->evaluar($planta, 'Stock bajo detectado tras anulación de movimiento', [
                'planta_id' => $planta->id,
                'planta_nombre' => $planta->nombre,
                'stock_actual' => $planta->stock_actual,
                'stock_minimo' => $planta->stock_minimo,
                'movimiento_original_id' => $original->id,
                'tipo_anulacion' => $tipoInverso,
                'cantidad_revertida' => $original->cantidad,
            ]);

            return $anulacion;
        });
    }

    /**
     * Agrega al query solo los filtros que vienen con valor.
     */
    private function aplicarFiltros(Builder $query, array $filtros): Builder
    {
        if (! empty($filtros['planta_id'])) {
            $query->where('planta_id', $filtros['planta_id']);
        }

        if (! empty($filtros['tipo'])) {
            $query->where('tipo', $filtros['tipo']);
        }

        if (! empty($filtros['desde'])) {
            $query->whereDate(self::COLUMNA_FECHA, '>=', $filtros['desde']);
        }

        if (! empty($filtros['hasta'])) {
            $query->whereDate(self::COLUMNA_FECHA, '<=', $filtros['hasta']);
        }

        return $query;
    }

    /**
     * Aplica una entrada o salida sobre el stock de la planta bloqueada.
     *
     * @throws StockInsuficienteException
     */
    private function aplicarCambioStock(Planta $planta, string $tipo, int $cantidad): void
    {
        if ($tipo === 'entrada') {
            $planta->increment('stock_actual', $cantidad);
        } else {
            if ($planta->stock_actual < $cantidad) {
                throw new StockInsuficienteException("Stock insuficiente para {$planta->nombre}. Disponible: {$planta->stock_actual}, requerido: {$cantidad}");
            }
            $planta->decrement('stock_actual', $cantidad);
        }
        $planta->save();
    }
}
