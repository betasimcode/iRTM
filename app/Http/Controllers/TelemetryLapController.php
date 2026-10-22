<?php

namespace App\Http\Controllers;

use App\Models\Stint;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use App\Services\Telemetry\TelemetryJsonStorage;
use Illuminate\Http\JsonResponse;
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
            $directory = storage_path(
                'app/private/telemetry/' .
                $stintDate->format('Y/m') .
                '/user_' . $stint->user_id .
                '/stint_' . $stint->id
            );

            $jsonStorage = app(TelemetryJsonStorage::class);
            $rawLapNumber = (int) (
                $selectedLap['candidate_lap']
                ?? $selectedLap['lap']
                ?? 0
            );

            if ($rawLapNumber > 0) {
                $lapPath = $directory . '/laps/' . sprintf(
                    'lap_%03d.json',
                    $rawLapNumber
                );

                if ($jsonStorage->exists($lapPath)) {
                    $lapPayload = $jsonStorage->readJson($lapPath);
                    $lapTelemetry = $lapPayload['samples'] ?? [];
                }
            }

            if ($referenceLapNumber !== null) {
                $referencePath = $directory . '/laps/' . sprintf(
                    'lap_%03d.json',
                    $referenceLapNumber
                );

                if ($jsonStorage->exists($referencePath)) {
                    $referencePayload = $jsonStorage->readJson($referencePath);
                    $referenceLapTelemetry = $referencePayload['samples'] ?? [];
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


public function references(Request $request): JsonResponse
{
    $mainStintId = $request->integer('stint');
    $search = trim((string) $request->query('q', ''));

    if ($mainStintId < 1) {
        return response()->json([
            'data' => [],
            'message' => 'Debes indicar el stint principal.',
        ], 422);
    }

    $mainStint = Stint::query()->findOrFail($mainStintId);

    $query = Stint::query()
        ->with('user')
        ->where('id', '!=', $mainStint->id)
        ->where('car_id', $mainStint->car_id)
        ->where('track_id', $mainStint->track_id)
        ->where('visibility', 'public')
        ->where('status', 'validated')
        ->whereNotNull('created_at')
        ->orderByDesc('created_at');

    if ($search !== '') {
        $query->where(function ($builder) use ($search) {
            if (ctype_digit($search)) {
                $builder->orWhere('id', (int) $search);
            }

            $builder->orWhereHas('user', function ($users) use ($search) {
                $users->where('name', 'like', '%' . $search . '%');
            });
        });
    }

    // Se limita el volumen inicial para no leer un catálogo completo
    // de IBT/JSON en cada búsqueda. Aquí solo se leen los JSON de vueltas.
    $stints = $query->limit(50)->get();

    $data = [];

    foreach ($stints as $candidate) {
        $date = $candidate->created_at;

        if (!$date) {
            continue;
        }

        $directory = storage_path(
            'app/private/telemetry/' .
            $date->format('Y/m') .
            '/user_' . $candidate->user_id .
            '/stint_' . $candidate->id
        );

        $lapsPath = $directory . '/stint_' .
            $candidate->id . '_laps.json';

        if (!is_file($lapsPath)) {
            continue;
        }

        $decoded = json_decode(
            file_get_contents($lapsPath),
            true
        );

        if (!is_array($decoded) || !isset($decoded['laps'])
            || !is_array($decoded['laps'])) {
            continue;
        }

        $validLaps = [];

        foreach ($decoded['laps'] as $lap) {
            if (!is_array($lap)) {
                continue;
            }

            $number = (int) (
                $lap['candidate_lap'] ?? $lap['lap'] ?? 0
            );

            $lapTime = $lap['lap_time'] ?? null;

            if (
                $number < 1 ||
                !is_numeric($lapTime) ||
                (float) $lapTime <= 5 ||
                !empty($lap['is_pit_lap'])
            ) {
                continue;
            }

            $validLaps[] = [
                'number' => $number,
                'lap_time' => (float) $lapTime,
                'lap_time_label' => gmdate(
                    'i:s.',
                    (int) $lapTime
                ) . str_pad(
                    (string) (int) round(
                        (((float) $lapTime) - floor((float) $lapTime))
                        * 1000
                    ),
                    3,
                    '0',
                    STR_PAD_LEFT
                ),
            ];
        }

        if (count($validLaps) === 0) {
            continue;
        }

        $data[] = [
            'stint_id' => $candidate->id,
            'driver' => $candidate->user?->name ?? 'Piloto desconocido',
            'date' => $date->format('d/m/Y H:i'),
            'laps' => $validLaps,
        ];
    }

    return response()->json([
        'data' => $data,
        'count' => count($data),
    ]);
}
















}
