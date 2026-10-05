<?php

namespace App\Services;

use App\Models\PlantaVendida;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class MonitoreoClienteService
{
    public function obtenerSensoresAsignados(User $cliente, bool $incluirHistorial = false): Collection
    {
        $limiteLecturas = $incluirHistorial ? 30 : 1;
        $limiteAlertas = $incluirHistorial ? 50 : 10;

        return PlantaVendida::query()
            ->where('user_id', $cliente->id)
            ->whereNotNull('sensor_id')
            ->with([
                'sensor',
                'venta.planta',
                'lecturasSensores' => fn ($query) => $query
                    ->orderByDesc('fecha_hora')
                    ->orderByDesc('id')
                    ->limit($limiteLecturas),
                'alertas' => function ($query) use ($incluirHistorial, $limiteAlertas) {
                    $query->orderByDesc('created_at')
                        ->orderByDesc('id')
                        ->limit($limiteAlertas);

                    if (! $incluirHistorial) {
                        $query->whereNull('resuelta_en');
                    }
                },
            ])
            ->orderByDesc('created_at')
            ->get();
    }
}
