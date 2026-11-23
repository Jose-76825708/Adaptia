<?php

namespace App\Http\Controllers;

use App\Exceptions\CloudinaryImageException;
use App\Models\CasoMantenimiento;
use App\Models\EvidenciaCaso;
use App\Services\DiagnosticoCasoService;
use App\Services\EvidenciaCasoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DiagnosticoCasoController extends Controller
{
    public function registrarNota(Request $request, CasoMantenimiento $caso, EvidenciaCasoService $service): RedirectResponse
    {
        try {
            $service->registrarNota($caso, (int) $request->user()->id, (string) $request->input('contenido', ''));
            return redirect()->route('mantenimiento.casos.show', $caso)->with('success', 'Nota registrada correctamente.');
        } catch (ValidationException|\InvalidArgumentException $exception) {
            $mensajes = $exception instanceof ValidationException
                ? $exception->errors()
                : ['contenido' => $exception->getMessage()];

            return redirect()->route('mantenimiento.casos.show', $caso)
                ->withErrors($mensajes, 'diagnostico');
        }
    }

    public function registrarFoto(Request $request, CasoMantenimiento $caso, EvidenciaCasoService $service): RedirectResponse
    {
        try {
            $archivo = $request->file('archivo');
            if (!$archivo) {
                throw ValidationException::withMessages(['archivo' => 'Debe seleccionar una foto.']);
            }

            $service->registrarFoto(
                $caso,
                (int) $request->user()->id,
                $archivo,
                $request->input('descripcion')
                    ? (string) $request->input('descripcion')
                    : null,
            );

            return redirect()->route('mantenimiento.casos.show', $caso)->with('success', 'Foto registrada correctamente.');
        } catch (CloudinaryImageException $exception) {
            return redirect()->route('mantenimiento.casos.show', $caso)
                ->withErrors(['archivo' => $exception->getMessage()], 'diagnostico');
        } catch (ValidationException|\InvalidArgumentException $exception) {
            $mensajes = $exception instanceof ValidationException
                ? $exception->errors()
                : ['archivo' => $exception->getMessage()];

            return redirect()->route('mantenimiento.casos.show', $caso)
                ->withErrors($mensajes, 'diagnostico');
        }
    }

    public function eliminarFoto(CasoMantenimiento $caso, EvidenciaCaso $evidencia, EvidenciaCasoService $service): RedirectResponse
    {
        try {
            $service->eliminarFoto($caso, $evidencia);
            return redirect()->route('mantenimiento.casos.show', $caso)->with('success', 'Foto eliminada correctamente.');
        } catch (CloudinaryImageException $exception) {
            return redirect()->route('mantenimiento.casos.show', $caso)
                ->withErrors(['evidencia' => $exception->getMessage()], 'diagnostico');
        } catch (ValidationException|\InvalidArgumentException $exception) {
            $mensajes = $exception instanceof ValidationException
                ? $exception->errors()
                : ['evidencia' => $exception->getMessage()];

            return redirect()->route('mantenimiento.casos.show', $caso)
                ->withErrors($mensajes, 'diagnostico');
        }
    }

    public function guardar(Request $request, CasoMantenimiento $caso, EvidenciaCasoService $evidenciaService, DiagnosticoCasoService $diagnosticoService): RedirectResponse
    {
        try {
            $contenido = trim((string) $request->input('contenido', ''));
            $archivo = $request->file('archivo');

            if ($contenido === '' && !$archivo) {
                throw ValidationException::withMessages([
                    'guardar' => 'Debe ingresar una nota o seleccionar una foto para guardar el diagnóstico.',
                ]);
            }
            if ($contenido !== '') {
                $evidenciaService->registrarNota($caso, (int) $request->user()->id, $contenido);
            }

            if ($archivo) {
                $evidenciaService->registrarFoto(
                    $caso,
                    (int) $request->user()->id,
                    $archivo,
                    $request->input('descripcion') ? (string) $request->input('descripcion') : null,
                );
            }

            $diagnosticoService->evaluarYPersistir($caso, (int) $request->user()->id);

            return redirect()->route('mantenimiento.casos.show.etapa', ['caso' => $caso, 'etapa' => 'diagnostico'])
                ->with('success', 'Diagnóstico guardado y evaluado correctamente.');
        } catch (CloudinaryImageException $exception) {
            return redirect()->route('mantenimiento.casos.show.etapa', ['caso' => $caso, 'etapa' => 'diagnostico'])
                ->withErrors(['archivo' => $exception->getMessage()], 'diagnostico');
        } catch (ValidationException|\InvalidArgumentException $exception) {
            $mensajes = $exception instanceof ValidationException
                ? $exception->errors()
                : ['guardar' => $exception->getMessage()];

            return redirect()->route('mantenimiento.casos.show.etapa', ['caso' => $caso, 'etapa' => 'diagnostico'])
                ->withErrors($mensajes, 'diagnostico');
        }
    }

    public function evaluar(CasoMantenimiento $caso, DiagnosticoCasoService $service, Request $request): RedirectResponse
    {
        try {
            $service->evaluarYPersistir($caso, (int) $request->user()->id);
            return redirect()->route('mantenimiento.casos.show', $caso)->with('success', 'Diagnóstico generado correctamente.');
        } catch (\InvalidArgumentException $exception) {
            return redirect()->route('mantenimiento.casos.show', $caso)
                ->withErrors(['evaluar' => $exception->getMessage()], 'diagnostico');
        }
    }
}
