<?php

namespace App\Services\Ibt;

use App\Models\Stint;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class IbtRawRepairService
{
    public function repair(Stint $stint): array
    {
        $ibt = $stint->files()
            ->where('type', 'ibt')
            ->first();

        if (!$ibt) {
            throw new RuntimeException(
                'No existe un IBT registrado para este stint.'
            );
        }

        $ibtPath = Storage::path($ibt->filepath);

        if (!is_file($ibtPath)) {
            throw new RuntimeException(
                'No se encuentra el IBT en: ' . $ibtPath
            );
        }

        $rawDirectory = dirname($ibtPath)
            . DIRECTORY_SEPARATOR
            . 'raw';

        $jsonPath = $rawDirectory
            . DIRECTORY_SEPARATOR
            . 'stint_' . $stint->id . '_laps.json';

        $manifestPath = $rawDirectory
            . DIRECTORY_SEPARATOR
            . 'manifest.json';

        try {
            $processor = app(IbtProcessor::class);
            $result = $processor->writeRawLapTelemetryJson(
                $ibtPath,
                $jsonPath
            );

            $manifest = [
                'status' => 'completed',
                'mode' => 'raw',
                'format_version' => 2,
                'stint_id' => $stint->id,
                'source_file' => basename($ibtPath),
                'source_bytes' => filesize($ibtPath),
                'source_sha256' => hash_file('sha256', $ibtPath),
                'generated_at' => now()->toIso8601String(),
                'output_file' => basename($jsonPath),
                'records' => $result['records'],
                'laps' => $result['laps'],
                'output_bytes' => $result['bytes'],
                'output_sha256' => $result['sha256'],
                'validation' => [
                    'finish_crossings_required' => false,
                    'complete_lap_required' => false,
                    'lap_validation_performed' => false,
                ],
            ];

            $manifestJson = json_encode(
                $manifest,
                JSON_PRETTY_PRINT
                | JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
                | JSON_THROW_ON_ERROR
            );

            if (file_put_contents(
                $manifestPath,
                $manifestJson . PHP_EOL,
                LOCK_EX
            ) === false) {
                throw new RuntimeException(
                    'No se pudo escribir el manifiesto RAW.'
                );
            }

            Log::info('IBT RAW EXPORT COMPLETED', [
                'stint_id' => $stint->id,
                'records' => $result['records'],
                'laps' => $result['laps'],
                'json_path' => $jsonPath,
                'manifest_path' => $manifestPath,
            ]);

            return [
                'status' => 'ok',
                'mode' => 'raw',
                'stint_id' => $stint->id,
                'records' => $result['records'],
                'laps' => $result['laps'],
                'json_path' => $jsonPath,
                'manifest_path' => $manifestPath,
            ];
        } catch (\Throwable $e) {
            Log::error('IBT RAW EXPORT FAILED', [
                'stint_id' => $stint->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
