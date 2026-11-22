<?php

namespace App\Services;

use App\Models\ProyectoPaisajismo;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class ProyectoPaisajismoService
{
    public const ESTADOS = ['solicitado', 'propuesto', 'aprobado', 'en_instalacion', 'entregado'];

    public const TRANSICIONES = [
        'solicitado' => ['propuesto'],
        'propuesto' => ['aprobado', 'solicitado'],
        'aprobado' => ['en_instalacion'],
        'en_instalacion' => ['entregado'],
        'entregado' => [],
    ];

    public function normalizar(array $datos): array
    {
        $claves = ['nombre', 'ubicacion', 'descripcion', 'area_m2', 'presupuesto_estimado', 'fecha_deseada'];
        $entrada = array_intersect_key($datos, array_flip($claves));

        return [
            'nombre' => $this->texto($entrada['nombre'] ?? null),
            'ubicacion' => $this->texto($entrada['ubicacion'] ?? null),
            'descripcion' => $this->texto($entrada['descripcion'] ?? null),
            'area_m2' => $this->decimal($entrada['area_m2'] ?? null),
            'presupuesto_estimado' => $this->decimal($entrada['presupuesto_estimado'] ?? null),
            'fecha_deseada' => $this->fecha($entrada['fecha_deseada'] ?? null),
        ];
    }

    public function validar(array $datos, ?CarbonInterface $hoy = null): array
    {
        $hoy = $hoy ?? now();
        $datos = $this->normalizar($datos);
        $errores = [];

        $nombre = (string) ($datos['nombre'] ?? '');
        if (mb_strlen($nombre) < 3 || mb_strlen($nombre) > 120) {
            $errores['nombre'] = 'El nombre debe tener entre 3 y 120 caracteres.';
        }

        $ubicacion = (string) ($datos['ubicacion'] ?? '');
        if (mb_strlen($ubicacion) < 3 || mb_strlen($ubicacion) > 160) {
            $errores['ubicacion'] = 'La ubicación debe tener entre 3 y 160 caracteres.';
        }

        $descripcion = (string) ($datos['descripcion'] ?? '');
        if (mb_strlen($descripcion) < 10 || mb_strlen($descripcion) > 1000) {
            $errores['descripcion'] = 'La descripción debe tener entre 10 y 1000 caracteres.';
        }

        $area = $this->numero($datos['area_m2']);
        if ($area === null || $area <= 0 || $area > 100000) {
            $errores['area_m2'] = 'El área debe ser mayor que 0 y no superar 100000 m².';
        }

        $presupuesto = $this->numero($datos['presupuesto_estimado']);
        if (($datos['presupuesto_estimado'] !== null && $presupuesto === null) || ($presupuesto !== null && $presupuesto < 0)) {
            $errores['presupuesto_estimado'] = 'El presupuesto debe ser un monto válido mayor o igual a cero.';
        }

        $fecha = $datos['fecha_deseada'] ?? null;
        if ($fecha !== null) {
            $fechaValor = $this->fecha($fecha);
            if ($fechaValor === null || $fechaValor < $hoy->copy()->startOfDay()->format('Y-m-d')) {
                $errores['fecha_deseada'] = 'La fecha deseada debe ser válida y no anterior a hoy.';
            }
        }

        return $errores;
    }

    public function abrir(int $solicitanteId, array $datos, ?CarbonInterface $hoy = null): ProyectoPaisajismo
    {
        $hoy = $hoy ?? Carbon::now();
        $normalizado = $this->normalizar($datos);
        $errores = $this->validar($normalizado, $hoy);

        if ($errores !== []) {
            throw ValidationException::withMessages($errores);
        }

        return DB::transaction(function () use ($solicitanteId, $normalizado, $hoy) {
            $proyecto = ProyectoPaisajismo::create([
                'codigo' => 'PAI-PENDIENTE',
                'solicitante_id' => $solicitanteId,
                'nombre' => $normalizado['nombre'],
                'ubicacion' => $normalizado['ubicacion'],
                'descripcion' => $normalizado['descripcion'],
                'area_m2' => $normalizado['area_m2'],
                'presupuesto_estimado' => $normalizado['presupuesto_estimado'],
                'fecha_deseada' => $normalizado['fecha_deseada'],
                'estado' => 'solicitado',
            ]);

            $codigo = sprintf('PAI-%s-%05d', $hoy->format('Y'), $proyecto->id);
            $proyecto->update(['codigo' => $codigo]);

            return $proyecto->fresh();
        });
    }

    public function cambiarEstado(ProyectoPaisajismo $proyecto, string $nuevoEstado): ProyectoPaisajismo
    {
        $nuevoEstado = strtolower(trim($nuevoEstado));

        if (!in_array($nuevoEstado, self::ESTADOS, true) || !$this->puedeTransicionar($proyecto->estado, $nuevoEstado)) {
            throw new InvalidArgumentException("La transición de '{$proyecto->estado}' a '{$nuevoEstado}' no está permitida.");
        }

        $proyecto->estado = $nuevoEstado;
        $proyecto->save();

        return $proyecto->fresh();
    }

    public function puedeTransicionar(string $desde, string $hacia): bool
    {
        return in_array($hacia, self::TRANSICIONES[$desde] ?? [], true);
    }

    private function texto(mixed $valor): ?string
    {
        if (!is_string($valor)) {
            return $valor === null ? null : (string) $valor;
        }

        $texto = trim(preg_replace('/\s+/', ' ', $valor) ?? '');
        return $texto === '' ? null : $texto;
    }

    private function decimal(mixed $valor): ?string
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        $texto = is_string($valor) ? str_replace(',', '.', trim($valor)) : (string) $valor;
        if (!is_numeric($texto)) {
            return $texto;
        }

        return number_format((float) $texto, 2, '.', '');
    }

    private function numero(mixed $valor): ?float
    {
        if ($valor === null || $valor === '' || !is_numeric(str_replace(',', '.', (string) $valor))) {
            return null;
        }

        return (float) str_replace(',', '.', (string) $valor);
    }

    private function fecha(mixed $valor): ?string
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        $texto = trim((string) $valor);
        $fecha = \DateTime::createFromFormat('Y-m-d', $texto);
        if ($fecha && $fecha->format('Y-m-d') === $texto) {
            return $texto;
        }

        $fecha = \DateTime::createFromFormat('d/m/Y', $texto);
        if ($fecha && $fecha->format('d/m/Y') === $texto) {
            return $fecha->format('Y-m-d');
        }

        return null;
    }
}
