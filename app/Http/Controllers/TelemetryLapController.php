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
                    if ((int) ($lap['lap'] ?? 0) === $requestedLap) {
                        $selectedLap = $lap;
                        break;
                    }
                }
            }

            // Si no se solicita una vuelta concreta, seleccionar
            // la última vuelta disponible en el JSON.
            if ($selectedLap === null && count($laps) > 0) {
                $selectedLap = $laps[array_key_last($laps)];
            }

            $selectedLapNumber = $selectedLap['lap'] ?? null;
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
                    $lapTelemetry = app(IbtProcessor::class)
                        ->extractLapTelemetry(
                            Storage::path($ibtRelativePath),
                            $rawLapNumber
                        );
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
        ]);
    }
}
