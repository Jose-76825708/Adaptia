<?php

namespace App\Services;

use App\Models\Alerta;
use App\Models\CampanaComercial;
use App\Models\CasoMantenimiento;
use App\Models\LecturaSensor;
use App\Models\MovimientoInventario;
use App\Models\Planta;
use App\Models\ProyectoPaisajismo;
use App\Models\Sensor;
use App\Models\SolicitudDisponibilidad;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Database\Eloquent\Builder;

class DashboardOperativoService
{
    public function resumenAdministrador(): array
    {
        $ventasDelMes = $this->consultaVentas()->whereBetween('fecha', [
            now()->startOfMonth(),
            now()->endOfMonth(),
        ]);

        return [
            'total_plantas' => Planta::query()->count(),
            'unidades_en_stock' => (int) Planta::query()->sum('stock_actual'),
            'plantas_bajo_stock' => Planta::query()->whereColumn('stock_actual', '<', 'stock_minimo')->count(),
            'ventas_mes' => (clone $ventasDelMes)->count(),
            'unidades_vendidas_mes' => (int) (clone $ventasDelMes)->sum('cantidad'),
            'ingresos_estimados_mes' => $this->estimarIngresos(clone $ventasDelMes),
            'casos_abiertos' => CasoMantenimiento::query()->where('estado', '!=', 'cerrado')->count(),
            'paisajismo_por_estado' => $this->proyectosPaisajismoPorEstado(),
            'alertas_activas' => Alerta::query()->whereNull('resuelta_en')->count(),
            'campanas_activas' => CampanaComercial::query()->where('estado', '!=', 'cerrada')->count(),
            'solicitudes_disponibilidad' => SolicitudDisponibilidad::query()->where('estado', 'pendiente')->count(),
            'sensores_activos' => Sensor::query()->where('estado', 'activo')->count(),
            'ventas_recientes' => $this->ventasRecientes(),
            'plantas_criticas' => $this->plantasBajoStock(),
            'casos_recientes' => CasoMantenimiento::query()
                ->where('estado', '!=', 'cerrado')
                ->orderByDesc('created_at')
                ->limit(5)
                ->get(['codigo', 'area_verde', 'tipo_mantenimiento', 'urgencia', 'estado', 'created_at']),
            'alertas_recientes' => $this->alertasActivas()
                ->map(fn (Alerta $alerta) => [
                    'tipo' => $alerta->tipo,
                    'variable' => $alerta->variable,
                    'planta' => $alerta->plantaVendida?->venta?->planta?->nombre ?? 'Planta no disponible',
                    'fecha' => $alerta->created_at,
                ])
                ->all(),
            'campanas_recientes' => $this->campanasActivas(),
            'movimientos_recientes' => MovimientoInventario::query()
                ->with('planta:id,nombre')
                ->orderByDesc('created_at')
                ->limit(5)
                ->get(['id', 'planta_id', 'tipo', 'cantidad', 'created_at']),
        ];
    }

    public function resumenVendedor(User $vendedor): array
    {
        $ventasDelMes = $this->consultaVentas($vendedor->id)->whereBetween('fecha', [
            now()->startOfMonth(),
            now()->endOfMonth(),
        ]);
        $ventasDeHoy = $this->consultaVentas($vendedor->id)->whereDate('fecha', today());

        return [
            'ventas_hoy' => (clone $ventasDeHoy)->count(),
            'unidades_hoy' => (int) (clone $ventasDeHoy)->sum('cantidad'),
            'ventas_mes' => (clone $ventasDelMes)->count(),
            'unidades_mes' => (int) (clone $ventasDelMes)->sum('cantidad'),
            'ingresos_estimados_mes' => $this->estimarIngresos(clone $ventasDelMes),
            'plantas_bajo_stock' => Planta::query()->whereColumn('stock_actual', '<', 'stock_minimo')->count(),
            'ventas_recientes' => $this->ventasRecientes($vendedor->id),
            'plantas_criticas' => $this->plantasBajoStock(),
            'campanas_activas' => $this->campanasActivas(),
        ];
    }

    public function consultarCasosMantenimiento(?string $estado = null): array
    {
        $consulta = CasoMantenimiento::query();
        if ($estado !== null && $estado !== '') {
            $consulta->where('estado', $estado);
        } else {
            $consulta->where('estado', '!=', 'cerrado');
        }

        return [
            'total' => (clone $consulta)->count(),
            'casos' => $consulta
                ->orderByDesc('created_at')
                ->limit(20)
                ->get(['codigo', 'area_verde', 'tipo_mantenimiento', 'urgencia', 'estado', 'fecha_deseada'])
                ->map(fn (CasoMantenimiento $caso) => [
                    'codigo' => $caso->codigo,
                    'area' => $caso->area_verde,
                    'tipo' => $caso->tipo_mantenimiento,
                    'urgencia' => $caso->urgencia,
                    'estado' => $caso->estado,
                    'fecha_deseada' => $caso->fecha_deseada ? substr((string) $caso->fecha_deseada, 0, 10) : null,
                ])
                ->all(),
        ];
    }

    public function consultarAlertasMonitoreo(): array
    {
        return [
            'total_activas' => Alerta::query()->whereNull('resuelta_en')->count(),
            'alertas' => $this->alertasActivas(20)->map(fn (Alerta $alerta) => [
                'tipo' => $alerta->tipo,
                'variable' => $alerta->variable,
                'valor_medido' => $alerta->valor_medido,
                'rango_minimo' => $alerta->rango_minimo,
                'rango_maximo' => $alerta->rango_maximo,
                'nombre_planta' => $alerta->plantaVendida?->venta?->planta?->nombre,
                'fecha' => $alerta->created_at?->toDateTimeString(),
            ])->all(),
            'lecturas_recientes' => LecturaSensor::query()
                ->with(['plantaVendida.sensor', 'plantaVendida.venta.planta'])
                ->orderByDesc('fecha_hora')
                ->limit(20)
                ->get([
                    'planta_vendida_id',
                    'humedad_suelo',
                    'temperatura',
                    'humedad_ambiental',
                    'luz',
                    'fecha_hora',
                ])
                ->map(fn (LecturaSensor $lectura) => [
                    'planta' => $lectura->plantaVendida?->venta?->planta?->nombre,
                    'sensor' => $lectura->plantaVendida?->sensor?->identificador_fisico,
                    'humedad_suelo' => $lectura->humedad_suelo,
                    'temperatura' => $lectura->temperatura,
                    'humedad_ambiental' => $lectura->humedad_ambiental,
                    'luz' => $lectura->luz,
                    'fecha' => $lectura->fecha_hora?->toDateTimeString(),
                ])
                ->all(),
        ];
    }

    private function consultaVentas(?int $vendedorId = null): Builder
    {
        $consulta = Venta::query();
        if ($vendedorId !== null) {
            $consulta->where('vendedor_id', $vendedorId);
        }

        return $consulta;
    }

    private function proyectosPaisajismoPorEstado(): array
    {
        $estados = ['solicitado', 'propuesto', 'aprobado', 'en_instalacion', 'entregado'];
        $conteos = ProyectoPaisajismo::query()
            ->whereIn('estado', $estados)
            ->selectRaw('estado, COUNT(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        return collect($estados)
            ->mapWithKeys(fn (string $estado) => [$estado => (int) ($conteos[$estado] ?? 0)])
            ->all();
    }

    private function estimarIngresos(Builder $ventas): float
    {
        return round((float) $ventas
            ->join('plantas', 'ventas.planta_id', '=', 'plantas.id')
            ->selectRaw('COALESCE(SUM(ventas.cantidad * plantas.precio), 0) as total')
            ->value('total'), 2);
    }

    private function ventasRecientes(?int $vendedorId = null)
    {
        return $this->consultaVentas($vendedorId)
            ->with('planta:id,nombre,precio')
            ->orderByDesc('fecha')
            ->limit(6)
            ->get(['id', 'vendedor_id', 'planta_id', 'cantidad', 'fecha'])
            ->map(fn (Venta $venta) => [
                'id' => $venta->id,
                'planta' => $venta->planta?->nombre ?? 'Planta no disponible',
                'cantidad' => $venta->cantidad,
                'fecha' => $venta->fecha,
                'ingreso_estimado' => round((float) ($venta->planta?->precio ?? 0) * $venta->cantidad, 2),
            ]);
    }

    private function plantasBajoStock()
    {
        return Planta::query()
            ->whereColumn('stock_actual', '<', 'stock_minimo')
            ->orderBy('stock_actual')
            ->limit(6)
            ->get(['id', 'nombre', 'stock_actual', 'stock_minimo']);
    }

    private function campanasActivas()
    {
        return CampanaComercial::query()
            ->where('estado', '!=', 'cerrada')
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get(['id', 'codigo', 'objetivos', 'estado', 'fecha_inicio', 'fecha_fin']);
    }

    private function alertasActivas(int $limite = 5)
    {
        return Alerta::query()
            ->whereNull('resuelta_en')
            ->with('plantaVendida.venta.planta')
            ->orderByDesc('created_at')
            ->limit($limite)
            ->get();
    }
}
