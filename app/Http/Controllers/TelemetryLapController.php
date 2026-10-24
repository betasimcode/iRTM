<?php

namespace App\Http\Controllers;

use App\Models\Stint;
use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
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
        $referenceLapTimeLabel = null;
        $referenceLapOptions = [];
        $referenceStintId = $request->integer('reference_stint') ?: null;

        if ($stintId > 0) {
            $stint = Stint::query()
                ->with(['track', 'carDefinition'])
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

        // La selección puede apuntar al mismo stint o a otro compatible.
        $requestedReference = $request->integer('reference_lap');
        $referenceStint = null;
        $referenceLaps = [];

        if ($referenceStintId !== null) {
            if ($referenceStintId === $stint->id) {
                $referenceStint = $stint;
                $referenceLaps = $laps;
            } else {
                $referenceStint = $this->applyReferenceCarCompatibility(
                    Stint::query()->whereKey($referenceStintId),
                    $stint
                )
                    ->where('track_id', $stint->track_id)
                    ->where('visibility', 'public')
                    ->where('status', 'validated')
                    ->whereNotNull('created_at')
                    ->first();

                if ($referenceStint) {
                    $referenceDate = $referenceStint->created_at;
                    $referenceDirectory = storage_path(
                        'app/private/telemetry/' .
                        $referenceDate->format('Y/m') .
                        '/user_' . $referenceStint->user_id .
                        '/stint_' . $referenceStint->id
                    );
                    $referenceLapsPath = $referenceDirectory .
                        '/stint_' . $referenceStint->id . '_laps.json';

                    if (is_file($referenceLapsPath)) {
                        $referenceDecoded = json_decode(
                            file_get_contents($referenceLapsPath),
                            true
                        );
                        if (is_array($referenceDecoded)
                            && isset($referenceDecoded['laps'])
                            && is_array($referenceDecoded['laps'])) {
                            $referenceLaps = $referenceDecoded['laps'];
                        }
                    }
                }
            }
        }

        // Si no se envía reference_stint, conservar compatibilidad con enlaces
        // antiguos que solo contienen reference_lap: buscar primero en el stint actual.
        if ($referenceStintId === null && $requestedReference > 0) {
            $referenceStint = $stint;
            $referenceLaps = $laps;
            $referenceStintId = $stint->id;
        }

        foreach ($referenceLaps as $option) {
            if (!is_array($option)) {
                continue;
            }

            $optionNumber = (int) (
                $option['candidate_lap'] ?? $option['lap'] ?? 0
            );
            $lapTime = $option['lap_time'] ?? null;

            if (
                $optionNumber === $requestedReference &&
                $optionNumber > 0 &&
                ($referenceStintId !== $stint->id || $optionNumber !== $selectedLapNumber) &&
                is_numeric($lapTime) &&
                (float) $lapTime > 5 &&
                empty($option['is_pit_lap'])
            ) {
                $referenceLapNumber = $optionNumber;

                $referenceTimeMs = (int) round((float) $lapTime * 1000);
                $referenceMinutes = intdiv($referenceTimeMs, 60000);
                $referenceSeconds = intdiv($referenceTimeMs % 60000, 1000);
                $referenceMilliseconds = $referenceTimeMs % 1000;

                $referenceLapTimeLabel = sprintf(
                    '%d:%02d.%03d',
                    $referenceMinutes,
                    $referenceSeconds,
                    $referenceMilliseconds
                );

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

            if ($referenceLapNumber !== null && $referenceStintId !== null) {
                $referenceStint = $referenceStintId === $stint->id
                    ? $stint
                    : $this->applyReferenceCarCompatibility(
                        Stint::query()->whereKey($referenceStintId),
                        $stint
                    )
                        ->where('track_id', $stint->track_id)
                        ->where('visibility', 'public')
                        ->where('status', 'validated')
                        ->whereNotNull('created_at')
                        ->first();

                if ($referenceStint && $referenceStint->created_at) {
                    $referenceDirectory = storage_path(
                        'app/private/telemetry/' .
                        $referenceStint->created_at->format('Y/m') .
                        '/user_' . $referenceStint->user_id .
                        '/stint_' . $referenceStint->id
                    );

                    $referencePath = $referenceDirectory . '/laps/' . sprintf(
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
            'referenceLapTimeLabel' => $referenceLapTimeLabel,
            'referenceStintId' => $referenceStintId,
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

    $query = $this->applyReferenceCarCompatibility(
        Stint::query()
            ->with('user')
            ->where('id', '!=', $mainStint->id),
        $mainStint
    )
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

    // El stint principal también es una fuente de referencias: se excluye
    // únicamente la vuelta que se está analizando, no el stint completo.
    $mainDate = $mainStint->created_at;
    if ($mainDate) {
        $mainDirectory = storage_path(
            'app/private/telemetry/' .
            $mainDate->format('Y/m') .
            '/user_' . $mainStint->user_id .
            '/stint_' . $mainStint->id
        );
        $mainLapsPath = $mainDirectory . '/stint_' .
            $mainStint->id . '_laps.json';

        if (is_file($mainLapsPath)) {
            $mainDecoded = json_decode(file_get_contents($mainLapsPath), true);
            if (is_array($mainDecoded) && isset($mainDecoded['laps'])
                && is_array($mainDecoded['laps'])) {
                $mainLaps = [];
                $selectedLapNumber = $request->integer('lap');

                foreach ($mainDecoded['laps'] as $lap) {
                    if (!is_array($lap)) {
                        continue;
                    }
                    $number = (int) ($lap['candidate_lap'] ?? $lap['lap'] ?? 0);
                    $lapTime = $lap['lap_time'] ?? null;

                    if (
                        $number < 1 ||
                        $number === $selectedLapNumber ||
                        !is_numeric($lapTime) ||
                        (float) $lapTime <= 5 ||
                        !empty($lap['is_pit_lap'])
                    ) {
                        continue;
                    }

                    $mainLaps[] = [
                        'number' => $number,
                        'lap_time' => (float) $lapTime,
                        'lap_time_label' => gmdate('i:s.', (int) $lapTime)
                            . str_pad(
                                (string) (int) round(
                                    (((float) $lapTime) - floor((float) $lapTime)) * 1000
                                ),
                                3,
                                '0',
                                STR_PAD_LEFT
                            ),
                    ];
                }

                $searchMatchesMain = $search === ''
                    || (ctype_digit($search) && (int) $search === $mainStint->id)
                    || stripos((string) ($mainStint->user?->name ?? ''), $search) !== false;

                if ($mainLaps && $searchMatchesMain) {
                    $data[] = [
                        'stint_id' => $mainStint->id,
                        'driver' => $mainStint->user?->name ?? 'Piloto actual',
                        'date' => $mainDate->format('d/m/Y H:i'),
                        'is_current' => true,
                        'laps' => $mainLaps,
                    ];
                }
            }
        }
    }

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
            'is_current' => false,
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

















private function applyReferenceCarCompatibility(
    Builder $query,
    Stint $mainStint
): Builder {
    // IMPORTANTE: ir_stints.car_id almacena el ID de iRacing,
    // mientras que cars.id es la PK interna de iRTM.
    $iracingCarId = $mainStint->car_id;

    $mainCategory = $iracingCarId !== null
        ? trim((string) Car::query()
            ->where('iracing_car_id', $iracingCarId)
            ->value('category'))
        : '';

    if ($mainCategory !== '') {
        // Resolver los IDs de iRacing de todos los modelos de la misma clase.
        // No usar whereHas('car'): la relación belongsTo estándar compara
        // ir_stints.car_id con cars.id, que son identificadores distintos.
        $compatibleIracingCarIds = Car::query()
            ->select('iracing_car_id')
            ->whereNotNull('iracing_car_id')
            ->whereRaw(
                'UPPER(TRIM(category)) = ?',
                [strtoupper($mainCategory)]
            );

        return $query->whereIn('ir_stints.car_id', $compatibleIracingCarIds);
    }

    // Fallback conservador si el coche principal no tiene categoría.
    if ($iracingCarId !== null) {
        return $query->where('ir_stints.car_id', $iracingCarId);
    }

    return $query->whereRaw('1 = 0');
}

}
