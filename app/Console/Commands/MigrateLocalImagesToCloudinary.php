<?php

namespace App\Console\Commands;

use App\Exceptions\CloudinaryImageException;
use App\Models\EvidenciaCaso;
use App\Models\Planta;
use App\Services\CloudinaryImageService;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;

class MigrateLocalImagesToCloudinary extends Command
{
    protected $signature = 'cloudinary:migrate-local-images
                            {--batch=100 : Cantidad de registros por lote}
                            {--dry-run : Mostrar candidatos sin subir archivos}';

    protected $description = 'Migra imágenes locales a Cloudinary sin eliminar los archivos originales';

    public function handle(CloudinaryImageService $cloudinaryImages): int
    {
        $batch = filter_var($this->option('batch'), FILTER_VALIDATE_INT);
        if ($batch === false || $batch < 1) {
            $this->error('El tamaño del lote debe ser un entero mayor que cero.');

            return self::FAILURE;
        }

        $totales = ['migrated' => 0, 'skipped' => 0, 'failed' => 0];

        Planta::query()
            ->whereNull('imagen_public_id')
            ->whereNotNull('imagen')
            ->chunkById($batch, function ($plantas) use ($cloudinaryImages, &$totales): void {
                foreach ($plantas as $planta) {
                    $this->migrarPlanta($planta, $cloudinaryImages, $totales);
                }
            });

        EvidenciaCaso::query()
            ->where('tipo', 'foto')
            ->whereNull('public_id')
            ->whereNotNull('ruta')
            ->chunkById($batch, function ($evidencias) use ($cloudinaryImages, &$totales): void {
                foreach ($evidencias as $evidencia) {
                    $this->migrarEvidencia($evidencia, $cloudinaryImages, $totales);
                }
            });

        $this->newLine();
        $this->info("Migradas: {$totales['migrated']}; omitidas: {$totales['skipped']}; errores: {$totales['failed']}.");

        return $totales['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function migrarPlanta(Planta $planta, CloudinaryImageService $cloudinaryImages, array &$totales): void
    {
        $rutaLocal = $planta->imagen_ruta_local ?: $planta->imagen;
        if ($this->esUrl($rutaLocal)) {
            $this->error("Planta {$planta->id}: ya tiene una URL pero no public_id; requiere revisión manual.");
            $totales['failed']++;

            return;
        }

        if (! $this->archivoLocalExiste($rutaLocal, "Planta {$planta->id}")) {
            $totales['failed']++;

            return;
        }

        if ($this->option('dry-run')) {
            $this->line("Planta {$planta->id}: se migraría {$rutaLocal}.");
            $totales['skipped']++;

            return;
        }

        try {
            $asset = $cloudinaryImages->upload(
                Storage::disk('public')->path($rutaLocal),
                'adaptia/plantas',
            );
        } catch (CloudinaryImageException $exception) {
            $this->error("Planta {$planta->id}: {$exception->getMessage()}");
            $totales['failed']++;

            return;
        }

        try {
            $actualizadas = Planta::query()
                ->whereKey($planta->id)
                ->whereNull('imagen_public_id')
                ->update([
                    'imagen' => $asset['secure_url'],
                    'imagen_public_id' => $asset['public_id'],
                    'imagen_ruta_local' => $planta->imagen_ruta_local ?: $rutaLocal,
                ]);
        } catch (QueryException $exception) {
            $this->limpiarCargaNoPersistida($asset['public_id'], $cloudinaryImages, "Planta {$planta->id}");
            $this->error("Planta {$planta->id}: no se pudo actualizar su registro.");
            $totales['failed']++;

            return;
        }

        if ($actualizadas === 0) {
            if (! $this->limpiarCargaNoPersistida($asset['public_id'], $cloudinaryImages, "Planta {$planta->id}")) {
                $totales['failed']++;
            }
            $this->line("Planta {$planta->id}: otro proceso ya la migró; no se modificó el registro.");
            $totales['skipped']++;

            return;
        }

        $this->info("Planta {$planta->id}: imagen migrada; original conservado en {$rutaLocal}.");
        $totales['migrated']++;
    }

    private function migrarEvidencia(EvidenciaCaso $evidencia, CloudinaryImageService $cloudinaryImages, array &$totales): void
    {
        $rutaLocal = $evidencia->ruta_local ?: $evidencia->ruta;
        if ($this->esUrl($rutaLocal)) {
            $this->error("Evidencia {$evidencia->id}: ya tiene una URL pero no public_id; requiere revisión manual.");
            $totales['failed']++;

            return;
        }

        if (! $this->archivoLocalExiste($rutaLocal, "Evidencia {$evidencia->id}")) {
            $totales['failed']++;

            return;
        }

        if ($this->option('dry-run')) {
            $this->line("Evidencia {$evidencia->id}: se migraría {$rutaLocal}.");
            $totales['skipped']++;

            return;
        }

        try {
            $asset = $cloudinaryImages->upload(
                Storage::disk('public')->path($rutaLocal),
                'adaptia/evidencias/'.$evidencia->caso_id,
            );
        } catch (CloudinaryImageException $exception) {
            $this->error("Evidencia {$evidencia->id}: {$exception->getMessage()}");
            $totales['failed']++;

            return;
        }

        try {
            $actualizadas = EvidenciaCaso::query()
                ->whereKey($evidencia->id)
                ->whereNull('public_id')
                ->update([
                    'ruta' => $asset['secure_url'],
                    'public_id' => $asset['public_id'],
                    'ruta_local' => $evidencia->ruta_local ?: $rutaLocal,
                ]);
        } catch (QueryException $exception) {
            $this->limpiarCargaNoPersistida($asset['public_id'], $cloudinaryImages, "Evidencia {$evidencia->id}");
            $this->error("Evidencia {$evidencia->id}: no se pudo actualizar su registro.");
            $totales['failed']++;

            return;
        }

        if ($actualizadas === 0) {
            if (! $this->limpiarCargaNoPersistida($asset['public_id'], $cloudinaryImages, "Evidencia {$evidencia->id}")) {
                $totales['failed']++;
            }
            $this->line("Evidencia {$evidencia->id}: otro proceso ya la migró; no se modificó el registro.");
            $totales['skipped']++;

            return;
        }

        $this->info("Evidencia {$evidencia->id}: foto migrada; original conservado en {$rutaLocal}.");
        $totales['migrated']++;
    }

    private function archivoLocalExiste(?string $ruta, string $registro): bool
    {
        if (! $ruta || ! Storage::disk('public')->exists($ruta)) {
            $this->error("{$registro}: no se encontró el archivo local.");

            return false;
        }

        return true;
    }

    private function limpiarCargaNoPersistida(string $publicId, CloudinaryImageService $cloudinaryImages, string $registro): bool
    {
        try {
            $cloudinaryImages->delete($publicId);

            return true;
        } catch (CloudinaryImageException $exception) {
            $this->error("{$registro}: no se pudo limpiar el recurso Cloudinary no asociado a un registro.");

            return false;
        }
    }

    private function esUrl(?string $valor): bool
    {
        return $valor !== null && filter_var($valor, FILTER_VALIDATE_URL) !== false;
    }
}
