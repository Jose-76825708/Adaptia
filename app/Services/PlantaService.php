<?php

namespace App\Services;

use App\Exceptions\CloudinaryImageException;
use App\Models\Planta;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;

class PlantaService
{
    /**
     * Obtiene todas las plantas con su tipo relacionado.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return Planta::with('tipoPlanta')->get();
    }

    /**
     * Obtiene todas las plantas con stock actual por debajo del mínimo
     *
     * @return Collection
     */
    public function getPlantasConStockBajo(): Collection
    {
        return Planta::whereColumn('stock_actual', '<', 'stock_minimo')->get();
    }

    /**
     * Busca una planta por su ID.
     *
     * @param string $id
     * @return Model
     */
    public function getPlantaById(string $id): Model
    {
        return Planta::findOrFail($id);
    }

    /**
     * Crea una nueva planta.
     *
     * @param array $data
     * @return Model
     */
    public function createPlanta(array $data): Model
    {
        $imagen = $data['imagen'] ?? null;
        if ($imagen instanceof UploadedFile) {
            $asset = $this->cloudinaryImages()->upload($imagen, 'adaptia/plantas');
            $data['imagen'] = $asset['secure_url'];
            $data['imagen_public_id'] = $asset['public_id'];

            try {
                return Planta::create($data);
            } catch (QueryException $exception) {
                $this->eliminarCargaFallida($asset['public_id']);
                throw $exception;
            }
        }

        return Planta::create($data);
    }

    /**
     * Actualiza una planta existente.
     *
     * @param string $id
     * @param array $data
     * @return Model
     */
    public function updatePlanta(string $id, array $data): Model
    {
        $planta = $this->getPlantaById($id);
        $imagen = $data['imagen'] ?? null;
        $assetAnterior = $planta->imagen_public_id;

        if ($imagen instanceof UploadedFile) {
            $assetNuevo = $this->cloudinaryImages()->upload($imagen, 'adaptia/plantas');
            $data['imagen_ruta_local'] = $planta->imagen_public_id
                ? $planta->imagen_ruta_local
                : ($planta->imagen_ruta_local ?: $planta->imagen);
            $data['imagen'] = $assetNuevo['secure_url'];
            $data['imagen_public_id'] = $assetNuevo['public_id'];

            try {
                $planta->update($data);
            } catch (QueryException $exception) {
                $this->eliminarCargaFallida($assetNuevo['public_id']);
                throw $exception;
            }

            if ($assetAnterior && $assetAnterior !== $assetNuevo['public_id']) {
                try {
                    $this->cloudinaryImages()->delete($assetAnterior);
                } catch (CloudinaryImageException $exception) {
                    throw new CloudinaryImageException(
                        "La planta se actualizó, pero no se pudo eliminar su imagen anterior de Cloudinary (public_id: {$assetAnterior}).",
                        recordUpdated: true,
                        previous: $exception,
                    );
                }
            }

            return $planta;
        }

        $planta->update($data);
        return $planta;
    }

    /**
     * Elimina una planta.
     *
     * @param string $id
     * @return bool
     */
    public function deletePlanta(string $id): bool
    {
        $planta = $this->getPlantaById($id);
        $publicId = $planta->imagen_public_id;
        if (!$planta->delete()) {
            return false;
        }

        if ($publicId) {
            try {
                $this->cloudinaryImages()->delete($publicId);
            } catch (CloudinaryImageException $exception) {
                throw new CloudinaryImageException(
                    "La planta se eliminó, pero no se pudo eliminar su imagen Cloudinary (public_id: {$publicId}).",
                    recordDeleted: true,
                    previous: $exception,
                );
            }
        }

        return true;
    }

    private function eliminarCargaFallida(string $publicId): void
    {
        try {
            $this->cloudinaryImages()->delete($publicId);
        } catch (CloudinaryImageException $cleanupException) {
            throw new CloudinaryImageException(
                "No se pudo guardar la planta ni limpiar la imagen cargada en Cloudinary (public_id: {$publicId}).",
                previous: $cleanupException,
            );
        }
    }

    private function cloudinaryImages(): CloudinaryImageService
    {
        return app(CloudinaryImageService::class);
    }
}
