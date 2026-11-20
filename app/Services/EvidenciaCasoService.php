<?php

namespace App\Services;

use App\Models\CasoMantenimiento;
use App\Models\EvidenciaCaso;
use App\Models\PlantaVendida;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class EvidenciaCasoService
{
    public const TIPOS_EVIDENCIA = ['nota', 'foto'];
    public const MIN_NOTA_CARACTERES = 3;
    public const MAX_NOTA_CARACTERES = 1000;
    public const MAX_FOTOS_POR_CASO = 10;
    public const MAX_TAMANIO_FOTO_BYTES = 5 * 1024 * 1024;
    public const VENTANA_LECTURAS_DIAS = 7;
    public const EXTENSIONES_FOTO = ['jpg', 'jpeg', 'png', 'webp'];

    public function registrarNota(CasoMantenimiento $caso, int $autorId, string $contenido): EvidenciaCaso
    {
        $this->validarCasoAbierto($caso);

        $texto = $this->normalizarTexto($contenido);

        if ($texto === null || mb_strlen($texto) < self::MIN_NOTA_CARACTERES || mb_strlen($texto) > self::MAX_NOTA_CARACTERES) {
            throw ValidationException::withMessages([
                'contenido' => 'La nota debe tener entre 3 y 1000 caracteres.',
            ]);
        }

        return EvidenciaCaso::create([
            'caso_id' => $caso->id,
            'autor_id' => $autorId,
            'tipo' => 'nota',
            'contenido' => $texto,
            'ruta' => null,
            'descripcion' => null,
        ]);
    }

    public function registrarFoto(CasoMantenimiento $caso, int $autorId, UploadedFile $archivo, ?string $descripcion = null): EvidenciaCaso
    {
        $this->validarCasoAbierto($caso);

        $extension = strtolower($archivo->getClientOriginalExtension() ?: $archivo->extension());
        if (!in_array($extension, self::EXTENSIONES_FOTO, true)) {
            throw ValidationException::withMessages([
                'archivo' => 'La foto debe tener un formato jpg, jpeg, png o webp.',
            ]);
        }

        if ($archivo->getSize() > self::MAX_TAMANIO_FOTO_BYTES) {
            throw ValidationException::withMessages([
                'archivo' => 'La foto supera el tamaño máximo permitido de 5 MB.',
            ]);
        }

        $totalFotos = EvidenciaCaso::query()
            ->where('caso_id', $caso->id)
            ->where('tipo', 'foto')
            ->count();

        if ($totalFotos >= self::MAX_FOTOS_POR_CASO) {
            throw ValidationException::withMessages([
                'archivo' => 'Este caso ya alcanzó el máximo de 10 fotos.',
            ]);
        }

        $rutaDescripcion = $this->normalizarDescripcion($descripcion);
        $rutaDestino = 'evidencias/' . $caso->id;
        $rutaArchivo = $archivo->storeAs($rutaDestino, $archivo->hashName(), 'public');

        if ($rutaArchivo === false) {
            throw ValidationException::withMessages([
                'archivo' => 'No se pudo guardar la foto.',
            ]);
        }

        return EvidenciaCaso::create([
            'caso_id' => $caso->id,
            'autor_id' => $autorId,
            'tipo' => 'foto',
            'contenido' => null,
            'ruta' => $rutaArchivo,
            'descripcion' => $rutaDescripcion,
        ]);
    }

    public function compilar(CasoMantenimiento $caso, ?CarbonInterface $hoy = null): array
    {
        $hoy = $hoy ?? Carbon::now();
        $inicio = $hoy->copy()->subDays(self::VENTANA_LECTURAS_DIAS)->startOfDay();
        $fin = $hoy->copy()->endOfDay();

        $evidencias = EvidenciaCaso::query()
            ->where('caso_id', $caso->id)
            ->with('autor')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $notas = [];
        $fotos = [];

        foreach ($evidencias as $evidencia) {
            $item = [
                'id' => $evidencia->id,
                'autor' => $evidencia->autor?->name ?? 'Sin autor',
                'fecha' => $evidencia->created_at?->toDateTimeString(),
            ];

            if ($evidencia->tipo === 'nota') {
                $notas[] = array_merge($item, [
                    'contenido' => $evidencia->contenido,
                    'ruta' => null,
                    'descripcion' => null,
                ]);
                continue;
            }

            $fotos[] = array_merge($item, [
                'contenido' => null,
                'ruta' => $evidencia->ruta,
                'descripcion' => $evidencia->descripcion,
            ]);
        }

        $plantasVendidas = PlantaVendida::query()
            ->where('user_id', $caso->solicitante_id)
            ->with(['sensor', 'lecturasSensores' => function ($query) use ($inicio, $fin) {
                $query->whereBetween('fecha_hora', [$inicio, $fin])
                    ->orderBy('fecha_hora', 'asc')
                    ->orderBy('id', 'asc');
            }])
            ->get();

        $sensores = [];
        foreach ($plantasVendidas as $plantaVendida) {
            if ($plantaVendida->sensor === null) {
                continue;
            }

            $variables = [];
            foreach (['humedad_suelo', 'temperatura', 'humedad_ambiental', 'luz'] as $variable) {
                $lecturas = [];
                foreach ($plantaVendida->lecturasSensores as $lectura) {
                    $valor = $lectura->{$variable} ?? null;
                    if ($valor === null || $valor === '' || $valor === []) {
                        continue;
                    }

                    $lecturas[] = [$variable => $valor];
                }

                $variables[$variable] = $this->resumirLecturas($lecturas);
            }

            $sensores[] = [
                'planta_vendida_id' => $plantaVendida->id,
                'sensor_id' => $plantaVendida->sensor_id,
                'variables' => $variables,
            ];
        }

        return [
            'caso' => [
                'id' => $caso->id,
                'codigo' => $caso->codigo,
                'area_verde' => $caso->area_verde,
                'descripcion' => $caso->descripcion,
                'observaciones' => $caso->observaciones,
                'fecha_deseada' => $caso->fecha_deseada,
                'tipo_mantenimiento' => $caso->tipo_mantenimiento,
                'urgencia' => $caso->urgencia,
                'estado' => $caso->estado,
            ],
            'evidencias' => [
                'notas' => $notas,
                'fotos' => $fotos,
            ],
            'sensores' => $sensores,
        ];
    }

    public function eliminarFoto(CasoMantenimiento $caso, EvidenciaCaso $evidencia): void
    {
        $this->validarCasoAbierto($caso);

        if ((int) $evidencia->caso_id !== (int) $caso->id || $evidencia->tipo !== 'foto') {
            throw ValidationException::withMessages([
                'evidencia' => 'La foto no pertenece al caso indicado.',
            ]);
        }

        if ($evidencia->ruta) {
            Storage::disk('public')->delete($evidencia->ruta);
        }

        $evidencia->delete();
    }

    public function resumirLecturas(array $lecturas): array
    {
        $valores = [];

        foreach ($lecturas as $lectura) {
            $valor = null;
            if (is_array($lectura)) {
                foreach (['humedad_suelo', 'temperatura', 'humedad_ambiental', 'luz'] as $campo) {
                    if (array_key_exists($campo, $lectura)) {
                        $valor = $lectura[$campo];
                        break;
                    }
                }
            } elseif (is_object($lectura)) {
                foreach (['humedad_suelo', 'temperatura', 'humedad_ambiental', 'luz'] as $campo) {
                    if (property_exists($lectura, $campo)) {
                        $valor = $lectura->{$campo};
                        break;
                    }
                }
            }

            if ($valor === null || $valor === '' || $valor === []) {
                continue;
            }

            if (is_string($valor)) {
                $valor = trim($valor);
                if ($valor === '') {
                    continue;
                }
                $valor = (float) $valor;
            }

            $valores[] = (float) $valor;
        }

        if ($valores === []) {
            return [
                'cantidad' => 0,
                'ultima' => null,
                'minimo' => null,
                'maximo' => null,
                'promedio' => null,
            ];
        }

        $ultima = end($valores);
        $promedio = array_sum($valores) / count($valores);

        return [
            'cantidad' => count($valores),
            'ultima' => (float) $ultima,
            'minimo' => (float) min($valores),
            'maximo' => (float) max($valores),
            'promedio' => (float) $promedio,
        ];
    }

    private function normalizarTexto(?string $valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        $texto = preg_replace('/\s+/', ' ', trim($valor));

        return $texto === '' ? null : $texto;
    }

    private function normalizarDescripcion(?string $descripcion): ?string
    {
        if ($descripcion === null) {
            return null;
        }

        $texto = preg_replace('/\s+/', ' ', trim($descripcion));

        return $texto === '' ? null : $texto;
    }

    private function validarCasoAbierto(CasoMantenimiento $caso): void
    {
        if ($caso->estado === 'cerrado') {
            throw ValidationException::withMessages([
                'caso' => 'El caso está cerrado. Reábralo primero para registrar evidencia.',
            ]);
        }
    }
}
