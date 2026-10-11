<?php

namespace App\Services\Stints;

class StintJsonMetricsService
{
    /**
     * Calcula las métricas de ritmo a partir de las vueltas
     * extraídas del JSON procesado del IBT.
     */
    public function calculate(array $laps): array
    {
        $validLapTimes = collect($laps)
            ->filter(fn ($lap) => is_array($lap))
            ->map(fn ($lap) => $lap['lap_time'] ?? null)
            ->filter(fn ($time) => is_numeric($time) && (float) $time > 5)
            ->map(fn ($time) => (float) $time)
            ->values();

        $bestLap = $validLapTimes->min();

        $threshold = $bestLap !== null
            ? $bestLap * 1.07
            : null;

        $competitiveLapTimes = $threshold !== null
            ? $validLapTimes
                ->filter(fn ($time) => $time <= $threshold)
                ->values()
            : collect();

        return [
            'valid_lap_count' => $validLapTimes->count(),
            'competitive_lap_count' => $competitiveLapTimes->count(),
            'best_lap' => $bestLap,
            'threshold_107' => $threshold,
            'avg_lap_107' => $competitiveLapTimes->isNotEmpty()
                ? $competitiveLapTimes->avg()
                : null,
            'valid_duration' => $validLapTimes->sum(),
        ];
    }
}
