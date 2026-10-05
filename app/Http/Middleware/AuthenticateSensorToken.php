<?php

namespace App\Http\Middleware;

use App\Models\Sensor;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateSensorToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('X-Sensor-Token');

        if (! is_string($token) || $token === '') {
            return response()->json(['message' => 'Credencial de sensor requerida.'], 401);
        }

        $sensor = Sensor::query()
            ->where('token_hash', hash('sha256', $token))
            ->where('estado', 'activo')
            ->first();

        if (! $sensor) {
            return response()->json(['message' => 'Credencial de sensor inválida.'], 401);
        }

        $request->attributes->set('authenticated_sensor', $sensor);

        return $next($request);
    }
}
