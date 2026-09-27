<?php

namespace App\Http\Controllers\Api\Sync;

use App\Http\Controllers\Controller;
use App\Services\Sync\SeriesSyncService;
use Illuminate\Http\Request;

class SeriesSyncController extends Controller
{
    public function __invoke(
        Request $request,
        SeriesSyncService $service
    ) {

        $data = $request->validate([

            'iracing_series_id' => ['required', 'integer', 'exists:iracing_series,id'],

            'season_year' => ['required', 'integer'],

            'season_number' => ['required', 'integer'],

            'status' => ['required', 'string']

        ]);

        $series = $service->sync($data);

        return response()->json([

            'success' => true,

            'series_id' => $series->id,

            'message' => 'Series synchronized successfully.'

        ]);
    }
}



