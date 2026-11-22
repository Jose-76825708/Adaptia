<?php

namespace App\Services;

use App\Models\ProyectoPaisajismo;
use App\Models\PropuestaDiseno;
use App\Models\Planta;
use App\Models\AvanceInstalacion;
use App\Exceptions\StockInsuficienteException;
use App\Services\MovimientoInventarioService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InstalacionProyectoService
{
    public function registrarAvance(ProyectoPaisajismo $proyecto,int $autorId,string $descripcion,int $porcentaje): AvanceInstalacion
    {
        if ($proyecto->estado !== 'en_instalacion') {
            throw new InvalidArgumentException('Los avances solo se registran durante la instalación.');
        }

        $descripcion=trim($descripcion);
        if (mb_strlen($descripcion)<3 || mb_strlen($descripcion)>500) {
            throw new InvalidArgumentException('La descripción debe tener entre 3 y 500 caracteres.');
        }
        if ($porcentaje<0 || $porcentaje>100) {
            throw new InvalidArgumentException('El porcentaje debe estar entre 0 y 100.');
        }

        $ultimo=AvanceInstalacion::where('proyecto_id',$proyecto->id)->orderByDesc('porcentaje')->orderByDesc('id')->first();
        if ($ultimo && $porcentaje<(int)$ultimo->porcentaje) {
            throw new InvalidArgumentException('El porcentaje no puede disminuir respecto al último avance.');
        }

        return AvanceInstalacion::create([
            'proyecto_id'=>$proyecto->id,
            'autor_id'=>$autorId,
            'descripcion'=>$descripcion,
            'porcentaje'=>$porcentaje,
        ]);
    }

    public function iniciarInstalacion(ProyectoPaisajismo $proyecto, int $autorId): ProyectoPaisajismo
    {
        if ($proyecto->estado !== 'aprobado') {
            throw new InvalidArgumentException('Solo se puede iniciar la instalación desde el estado aprobado.');
        }

        $propuesta=PropuestaDiseno::where('proyecto_id',$proyecto->id)->orderByDesc('version')->orderByDesc('id')->first();
        if (!$propuesta || $propuesta->decision !== 'aprobada') {
            throw new InvalidArgumentException('El proyecto no tiene una propuesta aprobada vigente.');
        }

        $lineas=[];
        foreach ($propuesta->plantas()->with('planta')->get() as $linea) {
            $planta=$linea->planta;
            $lineas[]=['planta_id'=>$planta->id,'nombre'=>$planta->nombre,'requerido'=>$linea->cantidad,'disponible'=>$planta->stock_actual];
        }

        $disponibilidad=$this->verificarDisponibilidad($lineas);
        if ($disponibilidad['estado'] !== 'suficiente') {
            throw new StockInsuficienteException('No hay stock suficiente para iniciar la instalación.');
        }

        return DB::transaction(function () use ($proyecto,$autorId,$propuesta) {
            $inventario=new MovimientoInventarioService();

            foreach ($propuesta->plantas()->with('planta')->get() as $linea) {
                $inventario->createMovimiento([
                    'planta_id'=>$linea->planta_id,
                    'user_id'=>$autorId,
                    'tipo'=>'salida',
                    'cantidad'=>$linea->cantidad,
                    'motivo'=>'Salida para proyecto '.$proyecto->codigo,
                ]);
            }

            return (new ProyectoPaisajismoService())->cambiarEstado($proyecto,'en_instalacion');
        });
    }

    public function verificarDisponibilidad(array $lineas): array
    {
        $resultado=[];

        foreach ($lineas as $linea) {
            $requerido=(int)($linea['requerido'] ?? 0);
            $disponible=(int)($linea['disponible'] ?? 0);
            $resultado[]=[
                'planta_id'=>(int)($linea['planta_id'] ?? 0),
                'nombre'=>(string)($linea['nombre'] ?? ''),
                'requerido'=>$requerido,
                'disponible'=>$disponible,
                'faltante'=>max(0,$requerido-$disponible),
            ];
        }

        $insuficiente=false;
        foreach ($resultado as $linea) {
            if ($linea['faltante']>0) {
                $insuficiente=true;
                break;
            }
        }

        return [
            'estado'=>$insuficiente?'insuficiente':'suficiente',
            'lineas'=>$resultado,
        ];
    }
}
