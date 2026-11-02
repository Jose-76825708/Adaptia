<?php

namespace App\Services;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;
use Illuminate\Validation\ValidationException;

class SolicitudMantenimientoService
{
    /**
     * Normaliza una solicitud de mantenimiento a un contrato estándar.
     *
     * @param  array<string, mixed>  $datos
     * @return array<string, mixed>
     */
    public function normalizar(array $datos): array
    {
        $salida = [
            'area_verde' => null,
            'descripcion' => null,
            'fecha_deseada' => null,
            'observaciones' => [],
        ];

        $entrada = array_intersect_key($datos, array_flip(array_keys($salida)));

        if (array_key_exists('area_verde', $entrada)) {
            $salida['area_verde'] = $this->normalizarTexto($entrada['area_verde']);
        }

        if (array_key_exists('descripcion', $entrada)) {
            $salida['descripcion'] = $this->normalizarTexto($entrada['descripcion']);
        }

        if (array_key_exists('fecha_deseada', $entrada)) {
            $salida['fecha_deseada'] = $this->normalizarFecha($entrada['fecha_deseada']);
        }

        if (array_key_exists('observaciones', $entrada)) {
            $salida['observaciones'] = $this->normalizarObservaciones($entrada['observaciones']);
        }

        return $salida;
    }

    public function validar(array $solicitud, ?CarbonInterface $hoy = null): array
    {
        $hoy = $hoy ?? Carbon::now();

        $reglas = [
            'area_verde' => ['required', 'string', 'min:3', 'max:120'],
            'descripcion' => ['required', 'string', 'min:10', 'max:1000'],
            'fecha_deseada' => [
                'nullable',
                function ($attribute, $value, $fail) use ($hoy) {
                    if ($value === null || $value === '') {
                        return;
                    }

                    if (!is_string($value)) {
                        $fail('La fecha deseada no es una fecha válida.');
                        return;
                    }

                    $fecha = \DateTime::createFromFormat('Y-m-d', $value);

                    if ($fecha === false || $fecha->format('Y-m-d') !== $value) {
                        $fail('La fecha deseada no es una fecha válida.');
                        return;
                    }

                    $fechaHoy = $hoy->copy()->startOfDay();

                    if ($fecha < $fechaHoy) {
                        $fail('La fecha deseada no puede ser anterior a hoy.');
                    }
                },
            ],
            'observaciones' => ['nullable', 'array'],
            'observaciones.*' => ['string', 'max:500'],
        ];

        $mensajes = [
            'area_verde.required' => 'El campo área verde es obligatorio.',
            'area_verde.string' => 'El campo área verde debe ser un texto válido.',
            'area_verde.min' => 'El campo área verde debe tener entre 3 y 120 caracteres.',
            'area_verde.max' => 'El campo área verde debe tener entre 3 y 120 caracteres.',
            'descripcion.required' => 'El campo descripción es obligatorio.',
            'descripcion.string' => 'El campo descripción debe ser un texto válido.',
            'descripcion.min' => 'El campo descripción debe tener entre 10 y 1000 caracteres.',
            'descripcion.max' => 'El campo descripción debe tener entre 10 y 1000 caracteres.',
            'fecha_deseada.date_format' => 'La fecha deseada no es una fecha válida.',
            'fecha_deseada.after_or_equal' => 'La fecha deseada no puede ser anterior a hoy.',
            'observaciones.array' => 'El campo observaciones debe ser un arreglo de textos.',
            'observaciones.*.string' => 'Cada observación debe ser un texto válido.',
            'observaciones.*.max' => 'La observación 1 no debe tener más de 500 caracteres.',
        ];

        $factory = new Factory(
            new Translator(new ArrayLoader(), 'es')
        );

        $validator = $factory->make($solicitud, $reglas, $mensajes);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    private function normalizarTexto(mixed $valor): ?string
    {
        if (!is_scalar($valor) && !is_null($valor)) {
            return null;
        }

        if (is_bool($valor)) {
            return $valor ? 'true' : 'false';
        }

        $texto = trim((string) $valor);
        $texto = preg_replace('/\s+/', ' ', $texto) ?? $texto;

        if ($texto === '') {
            return null;
        }

        return $texto;
    }

    private function normalizarFecha(mixed $valor): ?string
    {
        if (!is_scalar($valor) && !is_null($valor)) {
            return null;
        }

        $texto = $this->normalizarTexto($valor);

        if ($texto === null) {
            return null;
        }

        $input = $texto;

        $candidatos = [
            'Y-m-d' => $this->parseFechaConFormato($input, 'Y-m-d'),
            'd/m/Y' => $this->parseFechaConFormato($input, 'd/m/Y'),
        ];

        foreach ($candidatos as $formato => $fecha) {
            if ($fecha !== null) {
                return $fecha;
            }
        }

        return $input;
    }

    private function parseFechaConFormato(string $valor, string $formato): ?string
    {
        $fecha = \DateTime::createFromFormat($formato, $valor);

        if ($fecha === false) {
            return null;
        }

        if ($fecha->format($formato) !== $valor) {
            return null;
        }

        return $fecha->format('Y-m-d');
    }

    private function normalizarObservaciones(mixed $valor): array
    {
        if (is_null($valor)) {
            return [];
        }

        if (is_array($valor)) {
            $items = $valor;
        } else {
            $texto = $this->normalizarTexto($valor);
            if ($texto === null) {
                return [];
            }

            $items = preg_split('/[\r\n;]+/', $texto) ?: [$texto];
        }

        $normalizadas = [];

        foreach ($items as $item) {
            $valorNormalizado = $this->normalizarTexto($item);
            if ($valorNormalizado !== null) {
                $normalizadas[] = $valorNormalizado;
            }
        }

        $normalizadas = array_slice($normalizadas, 0, 6);

        return array_values($normalizadas);
    }
}
