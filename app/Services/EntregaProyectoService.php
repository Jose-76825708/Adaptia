<?php

namespace App\Services;

use App\Models\AvanceInstalacion;
use App\Models\EntregaProyecto;
use App\Models\ProyectoPaisajismo;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class EntregaProyectoService
{
    public function validarEntrega(string $resumen,string $recomendaciones,int $porcentaje): array
    {
        $errores=[];
        if (mb_strlen(trim($resumen))<10 || mb_strlen(trim($resumen))>1000) $errores['resumen']='El resumen debe tener entre 10 y 1000 caracteres.';
        if (mb_strlen(trim($recomendaciones))<10 || mb_strlen(trim($recomendaciones))>1000) $errores['recomendaciones']='Las recomendaciones deben tener entre 10 y 1000 caracteres.';
        if ($porcentaje!==100) $errores['porcentaje']='El último avance debe ser 100.';
        return $errores;
    }

    public function validarSatisfaccion(int $calificacion,string $comentario): array
    {
        $errores=[];
        if ($calificacion<1 || $calificacion>5) $errores['calificacion']='La calificación debe estar entre 1 y 5.';
        if (mb_strlen(trim($comentario))>500) $errores['comentario']='El comentario no puede superar 500 caracteres.';
        return $errores;
    }

    public function registrarEntrega(ProyectoPaisajismo $proyecto,int $autorId,string $resumen,string $recomendaciones): EntregaProyecto
    {
        if ($proyecto->estado !== 'en_instalacion') throw new InvalidArgumentException('La entrega solo se registra durante la instalación.');
        $ultimo=AvanceInstalacion::where('proyecto_id',$proyecto->id)->orderByDesc('porcentaje')->orderByDesc('id')->first();
        $porcentaje=(int)($ultimo?->porcentaje ?? -1);
        $errores=$this->validarEntrega($resumen,$recomendaciones,$porcentaje);
        if ($errores!==[]) throw new InvalidArgumentException(implode(' ', $errores));
        if (EntregaProyecto::where('proyecto_id',$proyecto->id)->exists()) throw new InvalidArgumentException('El proyecto ya tiene una entrega registrada.');

        return DB::transaction(function () use ($proyecto,$autorId,$resumen,$recomendaciones) {
            $entrega=EntregaProyecto::create(['proyecto_id'=>$proyecto->id,'autor_id'=>$autorId,'resumen'=>trim($resumen),'recomendaciones'=>trim($recomendaciones),'calificacion'=>null,'comentario'=>null,'calificado_en'=>null]);
            (new ProyectoPaisajismoService())->cambiarEstado($proyecto,'entregado');
            return $entrega->fresh();
        });
    }

    public function registrarSatisfaccion(ProyectoPaisajismo $proyecto,string $comentario,int $calificacion): EntregaProyecto
    {
        if ($proyecto->estado !== 'entregado') throw new InvalidArgumentException('Solo se puede calificar un proyecto entregado.');
        $entrega=EntregaProyecto::where('proyecto_id',$proyecto->id)->first();
        if (!$entrega) throw new InvalidArgumentException('El proyecto no tiene entrega registrada.');
        if ($entrega->calificacion!==null) throw new InvalidArgumentException('El proyecto ya fue calificado.');
        $errores=$this->validarSatisfaccion($calificacion,$comentario);
        if ($errores!==[]) throw new InvalidArgumentException(implode(' ', $errores));

        $entrega->update(['calificacion'=>$calificacion,'comentario'=>trim($comentario)===''?null:trim($comentario),'calificado_en'=>now()]);
        return $entrega->fresh();
    }

    public function compilarResumen(ProyectoPaisajismo $proyecto): array
    {
        $propuesta=\App\Models\PropuestaDiseno::where('proyecto_id',$proyecto->id)->with('plantas.planta')->orderByDesc('version')->orderByDesc('id')->first();
        $propuestaData=null;
        if ($propuesta) {
            $lineas=[];
            foreach ($propuesta->plantas as $linea) {
                $lineas[]=['planta_id'=>$linea->planta_id,'nombre'=>$linea->planta->nombre,'cantidad'=>$linea->cantidad,'precio_unitario'=>(float)$linea->precio_unitario,'subtotal'=>round($linea->cantidad*(float)$linea->precio_unitario,2)];
            }
            $propuestaData=['version'=>$propuesta->version,'concepto'=>$propuesta->concepto,'lineas'=>$lineas,'costo_plantas'=>(float)$propuesta->costo_plantas,'costo_mano_obra'=>(float)$propuesta->costo_mano_obra,'total'=>(float)$propuesta->total];
        }

        $avances=AvanceInstalacion::where('proyecto_id',$proyecto->id)->orderBy('porcentaje')->orderBy('id')->get()->map(fn ($avance)=>['porcentaje'=>$avance->porcentaje,'descripcion'=>$avance->descripcion,'fecha'=>$avance->created_at?->toDateTimeString()])->all();
        $entrega=EntregaProyecto::where('proyecto_id',$proyecto->id)->first();

        return ['proyecto'=>['id'=>$proyecto->id,'codigo'=>$proyecto->codigo,'nombre'=>$proyecto->nombre,'ubicacion'=>$proyecto->ubicacion,'estado'=>$proyecto->estado],'propuesta'=>$propuestaData,'avances'=>$avances,'entrega'=>$entrega?->toArray(),'satisfaccion'=>$entrega?->calificacion===null?null:['calificacion'=>$entrega->calificacion,'comentario'=>$entrega->comentario]];
    }
}
