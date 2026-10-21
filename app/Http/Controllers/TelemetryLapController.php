<?php

namespace App\Http\Controllers;

use App\Models\Stint;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use App\Services\Ibt\IbtProcessor;
use Illuminate\Support\Facades\Storage;

class TelemetryLapController extends Controller
{
    public function index(Request $request): View
    {
        $stints = Stint::query()
            ->with(['track'])
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        $stint = null;
        $sessionData = [];
        $laps = [];
        $selectedLap = null;
        $selectedLapNumber = null;

        $stintId = $request->integer('stint');

        $lapTelemetry = [];

        $referenceLapTelemetry = [];
        $referenceLapNumber = null;
        $referenceLapOptions = [];

        if ($stintId > 0) {
            $stint = Stint::query()
                ->with(['track'])
                ->findOrFail($stintId);

            $stintDate = $stint->created_at;

            if ($stintDate) {
                $directory = storage_path(
                    'app/private/telemetry/' .
                    $stintDate->format('Y/m') .
                    '/user_' . $stint->user_id .
                    '/stint_' . $stint->id
                );

                $sessionPath = $directory . '/session.json';
                $lapsPath = $directory .
                    '/stint_' . $stint->id . '_laps.json';

                if (is_file($sessionPath)) {
                    $decoded = json_decode(
                        file_get_contents($sessionPath),
                        true
                    );

                    if (is_array($decoded)) {
                        $sessionData = $decoded;
                    }
                }

                if (is_file($lapsPath)) {
                    $decoded = json_decode(
                        file_get_contents($lapsPath),
                        true
                    );

                    if (
                        is_array($decoded) &&
                        isset($decoded['laps']) &&
                        is_array($decoded['laps'])
                    ) {
                        $laps = $decoded['laps'];
                    }
                }
            }

        $requestedLap = $request->integer('lap');

        if ($requestedLap > 0) {
            foreach ($laps as $lap) {
                $lapNumber = (int) (
                    $lap['candidate_lap'] ?? $lap['lap'] ?? 0
                );

                if ($lapNumber === $requestedLap) {
                    $selectedLap = $lap;
                    break;
                }
            }
        }

        if ($selectedLap === null && count($laps) > 0) {
            $selectedLap = $laps[array_key_last($laps)];
        }

        $selectedLapNumber = $selectedLap
            ? (int) (
                $selectedLap['candidate_lap']
                ?? $selectedLap['lap']
                ?? 0
            )
            : null;

        // Preparar las vueltas válidas que pueden utilizarse como referencia.
        $referenceLapOptions = array_values(array_filter(
            $laps,
            function (array $lap) use ($selectedLapNumber): bool {
                $number = (int) (
                    $lap['candidate_lap'] ?? $lap['lap'] ?? 0
                );

                $lapTime = $lap['lap_time'] ?? null;

                return $number > 0
                    && $number !== $selectedLapNumber
                    && is_numeric($lapTime)
                    && (float) $lapTime > 5
                    && empty($lap['is_pit_lap']);
            }
        ));

        $requestedReference = $request->integer('reference_lap');

        foreach ($referenceLapOptions as $option) {
            $optionNumber = (int) (
                $option['candidate_lap'] ?? $option['lap'] ?? 0
            );

            if ($optionNumber === $requestedReference) {
                $referenceLapNumber = $optionNumber;
                break;
            }
        }
    }

    if ($stint && $selectedLap) {
        $stintDate = $stint->created_at;

        if ($stintDate) {
            $ibtRelativePath = sprintf(
                'telemetry/%s/%s/user_%s/stint_%s/stint_%s.ibt',
                $stintDate->format('Y'),
                $stintDate->format('m'),
                $stint->user_id,
                $stint->id,
                $stint->id
            );

            if (Storage::exists($ibtRelativePath)) {
                // El IBT usa el número nativo de vuelta.
                $rawLapNumber = (int) (
                    $selectedLap['candidate_lap']
                    ?? $selectedLap['lap']
                    ?? 0
                );

                if ($rawLapNumber > 0) {
                    $ibtPath = Storage::path($ibtRelativePath);
                    $processor = app(IbtProcessor::class);

                    $lapTelemetry = $processor->extractLapTelemetry(
                        $ibtPath,
                        $rawLapNumber
                    );

                    if ($referenceLapNumber !== null) {
                        $referenceLapTelemetry = $processor->extractLapTelemetry(
                            $ibtPath,
                            $referenceLapNumber
                        );
                    }
                }
        }
    }
    }

        return view('telemetry.lap', [
            'stints' => $stints,
            'stint' => $stint,
            'sessionData' => $sessionData,
            'laps' => $laps,
            'selectedLap' => $selectedLap,
            'lapTelemetry' => $lapTelemetry,
            'selectedLapNumber' => $selectedLapNumber,
            'referenceLapTelemetry' => $referenceLapTelemetry,
            'referenceLapNumber' => $referenceLapNumber,
            'referenceLapOptions' => $referenceLapOptions,
        ]);
    }
}
