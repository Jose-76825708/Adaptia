<?php

namespace App\Services;

use App\Exceptions\CloudinaryImageException;
use Cloudinary\Api\Exception\ApiError;
use Cloudinary\Cloudinary;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\UploadedFile;

class CloudinaryImageService
{
    public function __construct(private readonly Cloudinary $cloudinary) {}

    /**
     * @return array{secure_url: string, public_id: string}
     */
    public function upload(UploadedFile|string $file, string $folder): array
    {
        $path = $file instanceof UploadedFile ? $file->getRealPath() : $file;

        if (! is_string($path) || $path === '') {
            throw new CloudinaryImageException('No se pudo leer el archivo de imagen.');
        }

        try {
            $response = $this->cloudinary->uploadApi()->upload($path, [
                'folder' => $folder,
                'resource_type' => 'image',
                'type' => 'upload',
                'overwrite' => false,
            ]);
        } catch (ApiError|GuzzleException $exception) {
            throw new CloudinaryImageException('No se pudo cargar la imagen en Cloudinary.', previous: $exception);
        }

        $secureUrl = $response->offsetGet('secure_url');
        $publicId = $response->offsetGet('public_id');

        if (! is_string($secureUrl) || ! str_starts_with($secureUrl, 'https://') || ! is_string($publicId) || $publicId === '') {
            throw new CloudinaryImageException('Cloudinary no devolvió secure_url y public_id para la imagen.');
        }

        return [
            'secure_url' => $secureUrl,
            'public_id' => $publicId,
        ];
    }

    public function delete(string $publicId): void
    {
        try {
            $response = $this->cloudinary->uploadApi()->destroy($publicId, [
                'resource_type' => 'image',
                'type' => 'upload',
            ]);
        } catch (ApiError|GuzzleException $exception) {
            throw new CloudinaryImageException(
                "No se pudo eliminar la imagen de Cloudinary (public_id: {$publicId}).",
                previous: $exception,
            );
        }

        $result = $response->offsetGet('result');
        if (! in_array($result, ['ok', 'not found'], true)) {
            throw new CloudinaryImageException("Cloudinary no confirmó la eliminación (public_id: {$publicId}).");
        }
    }
}
