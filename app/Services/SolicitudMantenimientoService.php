<?php

namespace App\Services;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Str;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;
use Illuminate\Validation\ValidationException;

class SolicitudMantenimientoService
{
    public const TIPOS = [
        'control_plagas',
        'riego',
        'poda',
        'fertilizacion',
        'reposicion',
        'limpieza',
        'general',
    ];

    public const URGENCIAS = ['baja', 'media', 'alta'];

    private const PALABRAS_TIPO = [
        'control_plagas' => ['plaga', 'plagas', 'insecto', 'insectos', 'hongo', 'hongos', 'maleza', 'control de plagas'],
        'riego' => ['riego', 'regar', 'seca', 'seco', 'sequedad', 'sin agua', 'humedad baja', 'riego urgente'],
        'poda' => ['poda', 'podar', 'ramas', 'recorte', 'corte de ramas', 'forma del arbol'],
        'fertilizacion' => ['fertilizacion', 'fertilizante', 'abono', 'nutrientes', 'suelo nutriente'],
        'reposicion' => ['reponer', 'reposicion', 'sustituir', 'cambiar planta', 'cambio de planta'],
        'limpieza' => ['limpieza', 'limpiar', 'basura', 'hojas secas', 'deshierbe'],
    ];

    private const PALABRAS_ALTA = ['urgente', 'peligro', 'riesgo', 'se muere', 'caido', 'caída', 'desplomado', 'sin espera'];
    private const PALABRAS_BAJA = ['sin apuro', 'estetico', 'estético', 'programado', 'mantenimiento programado', 'no urgente'];

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

    public function clasificar(array $solicitud, ?CarbonInterface $hoy = null): array
    {
        $hoy = $hoy ?? Carbon::now();

        $tipo = $this->resolverTipo($solicitud);
        $urgencia = $this->resolverUrgencia($solicitud, $hoy);

        return [
            'tipo_mantenimiento' => $tipo,
            'urgencia' => $urgencia,
        ];
    }

    private function resolverTipo(array $solicitud): string
    {
        $texto = $this->construirTextoAnalizable($solicitud);

        foreach (self::TIPOS as $tipo) {
            if ($tipo === 'general') {
                continue;
            }

            if ($this->coincideConTipo($texto, $tipo)) {
                return $tipo;
            }
        }

        return 'general';
    }

    private function resolverUrgencia(array $solicitud, CarbonInterface $hoy): string
    {
        $texto = $this->construirTextoAnalizable($solicitud);
        $tieneBaja = $this->contieneCualquiera($texto, self::PALABRAS_BAJA);

        $textoSinBaja = $tieneBaja ? $this->removerPatrones($texto, self::PALABRAS_BAJA) : $texto;
        $tieneAlta = $this->contieneCualquiera($textoSinBaja, self::PALABRAS_ALTA);

        $fecha = null;
        if (isset($solicitud['fecha_deseada']) && is_string($solicitud['fecha_deseada']) && $solicitud['fecha_deseada'] !== '') {
            $fecha = Carbon::parse($solicitud['fecha_deseada']);
        }

        if ($tieneAlta) {
            return 'alta';
        }

        if ($fecha !== null) {
            $diferenciaDias = $hoy->copy()->startOfDay()->diffInDays($fecha->copy()->startOfDay(), false);

            if ($diferenciaDias <= 3) {
                return 'alta';
            }
        }

        if ($tieneBaja) {
            return 'baja';
        }

        if ($fecha !== null) {
            $diferenciaDias = $hoy->copy()->startOfDay()->diffInDays($fecha->copy()->startOfDay(), false);

            if ($diferenciaDias > 30) {
                return 'baja';
            }
        }

        return 'media';
    }

    private function construirTextoAnalizable(array $solicitud): string
    {
        $fragmentos = [];

        if (isset($solicitud['descripcion']) && is_string($solicitud['descripcion'])) {
            $fragmentos[] = $solicitud['descripcion'];
        }

        if (isset($solicitud['observaciones']) && is_array($solicitud['observaciones'])) {
            foreach ($solicitud['observaciones'] as $observacion) {
                if (is_string($observacion)) {
                    $fragmentos[] = $observacion;
                }
            }
        }

        return implode(' ', $fragmentos);
    }

    private function coincideConTipo(string $texto, string $tipo): bool
    {
        foreach (self::PALABRAS_TIPO[$tipo] as $clave) {
            if ($this->contienePatron($texto, $clave)) {
                return true;
            }
        }

        return false;
    }

    private function contieneCualquiera(string $texto, array $claves): bool
    {
        foreach ($claves as $clave) {
            if ($this->contienePatron($texto, $clave)) {
                return true;
            }
        }

        return false;
    }

    private function contienePatron(string $texto, string $clave): bool
    {
        $textoNormalizado = $this->normalizarParaBusqueda($texto);
        $claveNormalizada = $this->normalizarParaBusqueda($clave);

        if ($claveNormalizada === '') {
            return false;
        }

        $patron = '/(?<![\\pL\\pN])' . preg_quote($claveNormalizada, '/') . '(?![\\pL\\pN])/u';

        return preg_match($patron, $textoNormalizado) === 1;
    }

    private function removerPatrones(string $texto, array $patrones): string
    {
        $resultado = $texto;

        foreach ($patrones as $patron) {
            $resultado = preg_replace('/\b' . preg_quote($this->normalizarParaBusqueda($patron), '/') . '\b/u', ' ', $this->normalizarParaBusqueda($resultado));
        }

        return preg_replace('/\s+/', ' ', trim((string) $resultado));
    }

    private function normalizarParaBusqueda(string $valor): string
    {
        $valor = Str::lower($valor);
        $valor = Str::ascii($valor);
        $valor = preg_replace('/[^\pL\pN\s]+/u', ' ', $valor) ?? $valor;
        $valor = preg_replace('/\s+/u', ' ', trim($valor)) ?? $valor;

        return $valor;
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
