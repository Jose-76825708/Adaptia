<?php

namespace App\Services;

use App\Models\Planta;
use App\Models\PropuestaDiseno;
use App\Models\PropuestaPlanta;
use App\Models\ProyectoPaisajismo;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DisenoProyectoService
{
    public const TARIFA_MANO_OBRA_M2 = 15.00;

    public function normalizarLineas(array $lineas): array
    {
        $normalizadas=[];

        foreach ($lineas as $linea) {
            $plantaId=$linea['planta_id'] ?? null;
            $cantidad=$linea['cantidad'] ?? null;

            if ($plantaId===null || $plantaId==='' || $cantidad===null || $cantidad==='') {
                continue;
            }

            $normalizadas[]=[
                'planta_id'=>(int)$plantaId,
                'cantidad'=>(int)$cantidad,
            ];
        }

        return $normalizadas;
    }

    public function validarLineas(array $lineas, array $plantasExistentes): array
    {
        $errores=[];
        $ids=[];

        if (count($lineas)<1 || count($lineas)>10) {
            $errores['lineas']='Debe agregar entre 1 y 10 líneas.';
        }

        $existentes=[];
        foreach ($plantasExistentes as $planta) {
            $id=is_array($planta) ? (int)($planta['id'] ?? 0) : (int)$planta->id;
            $existentes[$id]=true;
        }

        foreach ($lineas as $indice=>$linea) {
            $plantaId=(int)($linea['planta_id'] ?? 0);
            $cantidad=(int)($linea['cantidad'] ?? 0);

            if ($cantidad<1 || $cantidad>1000) {
                $errores['cantidad_'.$indice]='La cantidad debe estar entre 1 y 1000.';
            }

            if (isset($ids[$plantaId])) {
                $errores['duplicada_'.$indice]='No puede repetir una planta en la propuesta.';
            }
            $ids[$plantaId]=true;

            if ($existentes!==[] && !isset($existentes[$plantaId])) {
                $errores['planta_'.$indice]='La planta seleccionada no existe.';
            }
        }

        return $errores;
    }

    public function guardarPropuesta(ProyectoPaisajismo $proyecto, int $autorId, string $concepto, array $lineas, ?CarbonInterface $hoy = null): PropuestaDiseno
    {
        $hoy = $hoy ?? now();

        if ($proyecto->estado !== 'solicitado') {
            throw new InvalidArgumentException('Solo se puede guardar una propuesta desde el estado solicitado.');
        }

        $concepto = trim($concepto);
        if (mb_strlen($concepto)<10 || mb_strlen($concepto)>1000) {
            throw new InvalidArgumentException('El concepto debe tener entre 10 y 1000 caracteres.');
        }

        $lineas = $this->normalizarLineas($lineas);
        $existentes = $this->plantasDisponibles()->map(fn ($planta) => ['id'=>(int)$planta->id])->all();
        $errores = $this->validarLineas($lineas, $existentes);
        if ($errores !== []) {
            throw new InvalidArgumentException(implode(' ', $errores));
        }

        $snapshots=[];
        foreach ($lineas as $linea) {
            $planta=Planta::findOrFail($linea['planta_id']);
            $snapshots[]=[
                'planta_id'=>$planta->id,
                'cantidad'=>$linea['cantidad'],
                'precio_unitario'=>(float)$planta->precio,
                'subtotal'=>round($linea['cantidad']*(float)$planta->precio,2),
                'nombre'=>$planta->nombre,
            ];
        }

        $cotizacion=$this->cotizar($snapshots,(float)$proyecto->area_m2,$proyecto->presupuesto_estimado);

        return DB::transaction(function () use ($proyecto,$autorId,$concepto,$cotizacion,$snapshots,$hoy) {
            $version=(int)PropuestaDiseno::where('proyecto_id',$proyecto->id)->max('version')+1;
            $propuesta=PropuestaDiseno::create([
                'proyecto_id'=>$proyecto->id,
                'autor_id'=>$autorId,
                'version'=>$version,
                'concepto'=>$concepto,
                'costo_plantas'=>$cotizacion['costo_plantas'],
                'costo_mano_obra'=>$cotizacion['costo_mano_obra'],
                'total'=>$cotizacion['total'],
                'decision'=>null,
                'motivo_decision'=>null,
                'decidida_en'=>null,
            ]);

            foreach ($snapshots as $snapshot) {
                PropuestaPlanta::create([
                    'propuesta_id'=>$propuesta->id,
                    'planta_id'=>$snapshot['planta_id'],
                    'cantidad'=>$snapshot['cantidad'],
                    'precio_unitario'=>$snapshot['precio_unitario'],
                ]);
            }

            $proyecto->estado='propuesto';
            $proyecto->save();

            return $propuesta->fresh('plantas');
        });
    }

    public function registrarDecision(ProyectoPaisajismo $proyecto, int $autorId, string $decision, ?string $motivo = null, ?CarbonInterface $hoy = null): PropuestaDiseno
    {
        $hoy=$hoy ?? now();

        if ($proyecto->estado !== 'propuesto') {
            throw new InvalidArgumentException('Solo se puede decidir una propuesta desde el estado propuesto.');
        }

        $propuesta=PropuestaDiseno::where('proyecto_id',$proyecto->id)->orderByDesc('version')->orderByDesc('id')->first();
        if (!$propuesta) {
            throw new InvalidArgumentException('El proyecto no tiene una propuesta vigente.');
        }

        $decision=strtolower(trim($decision));
        if (!in_array($decision,['aprobada','rechazada'],true)) {
            throw new InvalidArgumentException('La decisión debe ser aprobada o rechazada.');
        }

        if ($decision==='rechazada') {
            $motivo=trim((string)$motivo);
            if (mb_strlen($motivo)<10 || mb_strlen($motivo)>500) {
                throw new InvalidArgumentException('El motivo del rechazo debe tener entre 10 y 500 caracteres.');
            }
        } else {
            $motivo=null;
        }

        return DB::transaction(function () use ($proyecto,$propuesta,$decision,$motivo,$hoy) {
            $propuesta->update([
                'decision'=>$decision,
                'motivo_decision'=>$motivo,
                'decidida_en'=>$hoy,
            ]);

            $nuevoEstado=$decision==='aprobada' ? 'aprobado' : 'solicitado';
            (new ProyectoPaisajismoService())->cambiarEstado($proyecto,$nuevoEstado);

            return $propuesta->fresh();
        });
    }

    public function plantasDisponibles()
    {
        return Planta::query()->where('stock_actual','>',0)
            ->orderBy('nombre')->orderBy('id')->get(['id','nombre','precio','stock_actual']);
    }

    public function cotizar(array $lineas, float|int|string $area, float|int|string|null $presupuesto): array
    {
        $costoPlantas=0.0;

        foreach ($lineas as $linea) {
            $cantidad=(int)$linea['cantidad'];
            $precio=(float)$linea['precio_unitario'];
            $costoPlantas+=round($cantidad*$precio,2);
        }

        $area=(float)$area;
        $costoMano=round($area*self::TARIFA_MANO_OBRA_M2,2);
        $total=round($costoPlantas+$costoMano,2);

        $excede=false;
        if ($presupuesto!==null) {
            $excede=$total>(float)$presupuesto;
        }

        return [
            'lineas'=>$lineas,
            'costo_plantas'=>round($costoPlantas,2),
            'costo_mano_obra'=>$costoMano,
            'total'=>$total,
            'excede_presupuesto'=>$excede,
        ];
    }
}
