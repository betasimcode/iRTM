<?php

namespace App\Services\Ibt;

use App\Models\Stint;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class IbtRepairService
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

        $directory = dirname($ibtPath);

        $lapsJsonPath = $directory
            . DIRECTORY_SEPARATOR
            . 'stint_' . $stint->id . '_laps.json';

        $sessionJsonPath = $directory
            . DIRECTORY_SEPARATOR
            . 'session.json';

        $lapTelemetryDirectory = $directory
            . DIRECTORY_SEPARATOR
            . 'laps';

        try {
            $processor = app(IbtProcessor::class);

            $lapsPayload = $processor->writeLapsJson(
                $ibtPath,
                $lapsJsonPath
            );

            $telemetryLaps = $processor->extractLaps($ibtPath);

            $lapTelemetryResult = $processor->writeLapTelemetryJson(
                $ibtPath,
                $lapTelemetryDirectory,
                $telemetryLaps
            );

            Log::info('IBT REPAIR LAP DIAGNOSTICS', [
                'stint_id' => $stint->id,
                'generated_laps' => $lapTelemetryResult['generated_laps'] ?? [],
                'skipped_laps' => $lapTelemetryResult['skipped_laps'] ?? [],
            ]);

            $generatedLaps = array_map(
                'intval',
                $lapTelemetryResult['generated_laps'] ?? []
            );

            $expectedLaps = collect($lapsPayload['laps'] ?? [])
                ->filter(fn ($lap) =>
                    isset($lap['candidate_lap'], $lap['lap_time'])
                    && (int) $lap['candidate_lap'] > 0
                    && is_numeric($lap['lap_time'])
                    && (float) $lap['lap_time'] > 5
                    && !(bool) ($lap['is_pit_lap'] ?? false)
                )
                ->pluck('candidate_lap')
                ->map(fn ($lap) => (int) $lap)
                ->unique()
                ->values();

            $missing = [];

            foreach ($expectedLaps as $lapNumber) {
                $filename = sprintf(
                    'lap_%03d.json',
                    $lapNumber
                );

                $path = $lapTelemetryDirectory
                    . DIRECTORY_SEPARATOR
                    . $filename;

                if (
                    !in_array($lapNumber, $generatedLaps, true)
                    || !is_file($path)
                    || filesize($path) === 0
                ) {
                    $missing[] = $lapNumber;
                    continue;
                }

                $json = json_decode(
                    file_get_contents($path),
                    true
                );

                if (
                    !is_array($json)
                    || (int) ($json['candidate_lap'] ?? 0)
                        !== $lapNumber
                    || empty($json['samples'])
                ) {
                    $missing[] = $lapNumber;
                }
            }

            if ($missing) {
                throw new RuntimeException(
                    'Telemetría incompleta. Vueltas: '
                    . implode(', ', array_unique($missing))
                );
            }

            $processor->writeSessionJson(
                $ibtPath,
                $sessionJsonPath
            );

            if (
                !is_file($lapsJsonPath)
                || !is_file($sessionJsonPath)
            ) {
                throw new RuntimeException(
                    'No se han generado los JSON principales.'
                );
            }

            $validLaps = $expectedLaps->count();

            if ($validLaps === 0) {
                throw new RuntimeException(
                    'El IBT no contiene vueltas cronometradas válidas.'
                );
            }

            $stint->update([
                'status' => 'validated',
            ]);

            Log::info('IBT REPAIR COMPLETED', [
                'stint_id' => $stint->id,
                'valid_laps' => $validLaps,
                'generated_telemetry_laps' => count($generatedLaps),
            ]);

            return [
                'status' => 'ok',
                'stint_id' => $stint->id,
                'valid_laps' => $validLaps,
                'generated_telemetry_laps' => count($generatedLaps),
            ];

        } catch (\Throwable $e) {
            $stint->update([
                'status' => 'pending',
            ]);

            Log::error('IBT REPAIR FAILED', [
                'stint_id' => $stint->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
