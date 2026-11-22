<?php

namespace App\Services;

use App\Exceptions\AsistenteModeloException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class OllamaChatClient
{
    public function chatear(array $mensajes, array $herramientas): array
    {
        $baseUrl = config('services.ollama.base_url');
        $modelo = config('services.ollama.model');
        $timeout = filter_var(config('services.ollama.timeout'), FILTER_VALIDATE_INT);
        $contexto = filter_var(config('services.ollama.num_ctx'), FILTER_VALIDATE_INT);
        $maxTokens = filter_var(config('services.ollama.num_predict'), FILTER_VALIDATE_INT);
        $think = filter_var(config('services.ollama.think'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $keepAlive = config('services.ollama.keep_alive');

        if (! is_string($baseUrl) || ! is_string($modelo) || trim($modelo) === ''
            || $timeout === false || $timeout < 1 || $timeout > 180
            || $contexto === false || $contexto < 1024 || $contexto > 8192
            || $maxTokens === false || $maxTokens < 32 || $maxTokens > 1024
            || $think === null
            || ! is_string($keepAlive) || ! preg_match('/^[1-9][0-9]*(s|m|h)$/', $keepAlive)) {
            throw new AsistenteModeloException('La configuración local del modelo no es válida.');
        }

        $this->validarUrlLocal($baseUrl);
        $payload = [
            'model' => $modelo,
            'messages' => $mensajes,
            'stream' => false,
            'think' => $think,
            'keep_alive' => $keepAlive,
            'options' => [
                'num_ctx' => $contexto,
                'num_predict' => $maxTokens,
                'temperature' => 0.2,
            ],
        ];
        if ($herramientas !== []) {
            $payload['tools'] = $herramientas;
        }

        try {
            $response = Http::baseUrl(rtrim($baseUrl, '/'))
                ->acceptJson()
                ->timeout($timeout)
                ->post('/api/chat', $payload);
        } catch (ConnectionException $exception) {
            throw new AsistenteModeloException('No se pudo conectar con el modelo local.', $exception);
        }

        if (! $response->successful()) {
            throw new AsistenteModeloException(
                "El modelo local respondió con el estado HTTP {$response->status()}."
            );
        }

        $resultado = $response->json();
        if (! is_array($resultado) || ! isset($resultado['message']) || ! is_array($resultado['message'])) {
            throw new AsistenteModeloException('La respuesta del modelo local no tiene el formato esperado.');
        }

        return $resultado;
    }

    private function validarUrlLocal(string $baseUrl): void
    {
        $partes = parse_url($baseUrl);
        $hostsLocales = ['localhost', '127.0.0.1', '::1'];

        if (! is_array($partes)
            || ! in_array($partes['scheme'] ?? null, ['http', 'https'], true)
            || ! in_array(strtolower($partes['host'] ?? ''), $hostsLocales, true)
            || array_intersect(['user', 'pass', 'query', 'fragment'], array_keys($partes)) !== []) {
            throw new AsistenteModeloException('El asistente solo puede conectarse a un servidor Ollama local.');
        }
    }
}
