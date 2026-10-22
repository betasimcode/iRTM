<?php

namespace App\\Services\\Telemetry;

use JsonException;
use RuntimeException;

/**
 * Almacenamiento transparente de artefactos JSON de telemetría.
 *
 * Los consumidores pueden solicitar la ruta lógica .json; las escrituras
 * generan .json.gz y las lecturas aceptan ambos formatos durante la migración.
 */
class TelemetryJsonStorage
{
    private const GZIP_LEVEL = 6;

    /**
     * Escribe un array como JSON gzip, usando escritura temporal y rename.
     * Devuelve la ruta física creada (.json.gz).
     */
    public function writeJson(string $path, array $payload): string
    {
        $json = json_encode(
            $payload,
            JSON_PRETTY_PRINT
                | JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
                | JSON_THROW_ON_ERROR
        );

        return $this->write($path, $json . PHP_EOL);
    }

    /**
     * Lee JSON plano o comprimido. Si se recibe una ruta .json lógica,
     * primero busca su pareja .json.gz y después el JSON histórico.
     */
    public function readJson(string $path): array
    {
        $resolvedPath = $this->resolveReadPath($path);

        if ($resolvedPath === null) {
            throw new RuntimeException("Archivo JSON de telemetría no encontrado: {$path}");
        }

        $contents = $this->read($resolvedPath);

        try {
            $decoded = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new RuntimeException(
                "JSON de telemetría inválido: {$resolvedPath}. " . $e->getMessage(),
                0,
                $e
            );
        }

        if (!is_array($decoded)) {
            throw new RuntimeException("La raíz del JSON debe ser un objeto o array: {$resolvedPath}");
        }

        return $decoded;
    }

    /**
     * Escribe bytes de texto como gzip en una ruta .json.gz.
     * La ruta lógica .json se convierte automáticamente a .json.gz.
     */
    public function write(string $path, string $contents): string
    {
        $targetPath = $this->compressedPath($path);
        $directory = dirname($targetPath);

        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException("No se pudo crear el directorio: {$directory}");
        }

        $compressed = gzencode($contents, self::GZIP_LEVEL);

        if ($compressed === false) {
            throw new RuntimeException("No se pudo comprimir el JSON: {$targetPath}");
        }

        $temporaryPath = $targetPath . '.tmp.' . bin2hex(random_bytes(8));

        if (file_put_contents($temporaryPath, $compressed, LOCK_EX) === false) {
            throw new RuntimeException("No se pudo escribir el temporal: {$temporaryPath}");
        }

        if (gzdecode((string) file_get_contents($temporaryPath)) !== $contents) {
            @unlink($temporaryPath);
            throw new RuntimeException("La verificación gzip ha fallado: {$targetPath}");
        }

        if (!rename($temporaryPath, $targetPath)) {
            @unlink($temporaryPath);
            throw new RuntimeException("No se pudo finalizar el archivo comprimido: {$targetPath}");
        }

        return $targetPath;
    }

    /**
     * Comprueba si existe la representación comprimida o la histórica.
     */
    public function exists(string $path): bool
    {
        return $this->resolveReadPath($path) !== null;
    }

    /**
     * Devuelve la ruta comprimida canónica correspondiente a una ruta lógica.
     */
    public function compressedPath(string $path): string
    {
        return str_ends_with($path, '.json.gz')
            ? $path
            : (str_ends_with($path, '.json') ? $path . '.gz' : $path . '.json.gz');
    }

    private function resolveReadPath(string $path): ?string
    {
        $compressedPath = $this->compressedPath($path);

        if (is_file($compressedPath)) {
            return $compressedPath;
        }

        if (is_file($path)) {
            return $path;
        }

        return null;
    }

    private function read(string $path): string
    {
        if (str_ends_with($path, '.gz')) {
            $contents = gzdecode((string) file_get_contents($path));

            if ($contents === false) {
                throw new RuntimeException("No se pudo descomprimir el JSON: {$path}");
            }

            return $contents;
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new RuntimeException("No se pudo leer el JSON: {$path}");
        }

        return $contents;
    }
}
