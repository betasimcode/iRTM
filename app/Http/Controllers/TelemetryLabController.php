<?php

namespace App\Http\Controllers;

use App\Models\Stint;
use App\Models\StintFile;
use App\Services\Ibt\IbtProcessor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TelemetryLabController extends Controller
{
    public function index(
        Request $request,
        IbtProcessor $processor
    ) {
        $stints = Stint::query()
            ->where('user_id', auth()->id())
            ->whereHas('files', function ($query) {
                $query->where('type', 'ibt');
            })
            ->with([
                'track',
                'files' => function ($query) {
                    $query->where('type', 'ibt');
                },
            ])
            ->latest('id')
            ->get();

        $selectedStint = null;
        $ibtFile = null;
        $header = null;
        $variables = [];
        $records = [];
        $error = null;

        $stintId = $request->integer('stint');

        if ($stintId) {
            $selectedStint = $stints->firstWhere(
                'id',
                $stintId
            );

            if (!$selectedStint) {
                abort(404);
            }

            $ibtFile = $selectedStint->files
                ->firstWhere('type', 'ibt');

            if (!$ibtFile) {
                $error = 'El stint no tiene un archivo IBT asociado.';
            } else {
                $path = Storage::disk('local')->path(
                    $ibtFile->filepath
                );

                if (!is_file($path)) {
                    $error =
                        'El archivo IBT no existe físicamente en storage.';
                } else {
                    try {
                        $header =
                            $processor->readHeader($path);

                        $variables =
                            $processor->readVariables($path);

                        $previewVariables = [
                            'SessionTime',
                            'Lap',
                            'LapCompleted',
                            'LapDist',
                            'LapDistPct',
                            'Speed',
                            'RPM',
                            'Gear',
                            'Throttle',
                            'Brake',
                            'SteeringWheelAngle',
                            'OnPitRoad',
                            'PlayerCarInPitStall',
                            'PlayerCarDriverIncidentCount',
                            'PlayerTrackSurface',
                            'LFrideHeight',
                            'RFrideHeight',
                            'LRrideHeight',
                            'RRrideHeight',
                        ];

                        foreach (
                            $processor->streamRecords(
                                $path,
                                $previewVariables
                            ) as $index => $record
                        ) {
                            $records[] = [
                                'index' => $index,
                                'values' => $record,
                            ];

                            if (count($records) >= 30) {
                                break;
                            }
                        }
                    } catch (\Throwable $e) {
                        $error =
                            $e->getMessage();
                    }
                }
            }
        }

        return view(
            'telemetry.lab',
            compact(
                'stints',
                'selectedStint',
                'ibtFile',
                'header',
                'variables',
                'records',
                'error'
            )
        );
    }
}
