<?php

namespace App\Console\Commands;

use App\Services\Telemetry\TelemetryJsonStorage;
use Illuminate\Console\Command;
use JsonException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Throwable;

class CompressTelemetryJson extends Command
{
    protected $signature = 'telemetry:compress-json
        {--dry-run : Solo informa de los archivos que migraría}
        {--limit=500 : Número máximo de archivos a procesar}
        {--keep-original : Conserva el JSON original después de verificar el gzip}';

    protected $description = 'Comprime los JSON históricos de telemetría por vuelta a gzip';

    public function handle(TelemetryJsonStorage $storage): int
    {
        $root = storage_path('app/private/telemetry');
        $limit = max(1, (int) $this->option('limit'));
        $dryRun = (bool) $this->option('dry-run');
        $keepOriginal = (bool) $this->option('keep-original');

        if (!is_dir($root)) {
            $this->error("No existe el directorio de telemetría: {$root}");

            return self::FAILURE;
        }

        $processed = 0;
        $skipped = 0;
        $failed = 0;
        $originalBytes = 0;
        $compressedBytes = 0;

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $root,
                RecursiveDirectoryIterator::SKIP_DOTS
            )
        );

        foreach ($iterator as $fileInfo) {
            if (!$fileInfo instanceof SplFileInfo || !$fileInfo->isFile()) {
                continue;
            }

            $path = $fileInfo->getPathname();

            // Solo telemetría detallada por vuelta; no toca session.json,
            // stint_*_laps.json, manifiestos ni exportaciones RAW.
            if (!preg_match('~[\\\\/]laps[\\\\/]lap_\\d+\\.json$~i', $path)) {
                continue;
            }

            $gzipPath = $path . '.gz';

            if (is_file($gzipPath)) {
                $skipped++;
                continue;
            }

            if ($processed >= $limit) {
                break;
            }

            if ($dryRun) {
                $this->line("[DRY RUN] {$path} -> {$gzipPath}");
                $processed++;
                $originalBytes += (int) $fileInfo->getSize();
                continue;
            }

            try {
                $original = file_get_contents($path);

                if ($original === false || $original === '') {
                    throw new \RuntimeException('El archivo está vacío o no se puede leer.');
                }

                $decodedOriginal = json_decode(
                    $original,
                    true,
                    512,
                    JSON_THROW_ON_ERROR
                );

                if (
                    !is_array($decodedOriginal)
                    || !isset($decodedOriginal['candidate_lap'])
                    || !isset($decodedOriginal['samples'])
                    || !is_array($decodedOriginal['samples'])
                    || count($decodedOriginal['samples']) === 0
                ) {
                    throw new \RuntimeException('El JSON no cumple el esquema mínimo de una vuelta.');
                }

                $before = (int) $fileInfo->getSize();
                $writtenPath = $storage->write($path, $original);

                $decodedCompressed = $storage->readJson($path);

                if ($decodedCompressed !== $decodedOriginal) {
                    @unlink($writtenPath);
                    throw new \RuntimeException('El contenido descomprimido no coincide con el original.');
                }

                $after = (int) filesize($writtenPath);

                if (!$keepOriginal && !unlink($path)) {
                    throw new \RuntimeException(
                        'El gzip es válido, pero no se pudo eliminar el original.'
                    );
                }

                $processed++;
                $originalBytes += $before;
                $compressedBytes += $after;

                $this->info(sprintf(
                    '%s | %s -> %s | ahorro %.1f%%',
                    basename($path),
                    number_format($before / 1024 / 1024, 2) . ' MiB',
                    number_format($after / 1024 / 1024, 2) . ' MiB',
                    $before > 0 ? (1 - ($after / $before)) * 100 : 0
                ));
            } catch (Throwable $e) {
                $failed++;
                $this->error("Fallo en {$path}: {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->table(
            ['Resultado', 'Valor'],
            [
                ['Modo', $dryRun ? 'Simulación' : 'Migración'],
                ['Archivos procesados', $processed],
                ['Omitidos (gzip existente)', $skipped],
                ['Errores', $failed],
                ['Tamaño original', number_format($originalBytes / 1024 / 1024, 2) . ' MiB'],
                ['Tamaño gzip', $dryRun ? 'No calculado' : number_format($compressedBytes / 1024 / 1024, 2) . ' MiB'],
                ['Originales conservados', ($dryRun || $keepOriginal) ? 'Sí' : 'No'],
            ]
        );

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
