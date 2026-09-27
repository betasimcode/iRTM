<?php

namespace App\Http\Controllers\Api\Sync;

use App\Http\Controllers\Controller;
use App\Services\Sync\RoundsSyncService;
use Illuminate\Http\Request;

class RoundsSyncController extends Controller
{
    public function __invoke(
        Request $request,
        RoundsSyncService $service
    ) {

        $data = $request->validate([

            'iracing_series_id' => [
                'required',
                'integer',
                'exists:iracing_series,id'
            ],

            'season_year' => [
                'required',
                'integer'
            ],

            'season_number' => [
                'required',
                'integer',
                'between:1,4'
            ],

            'rounds' => [
                'required',
                'array'
            ],

            'rounds.*.week' => [
                'required',
                'integer'
            ],

            'rounds.*.week_start' => [
                'required',
                'date'
            ],

            'rounds.*.week_end' => [
                'required',
                'date'
            ],

            'rounds.*.circuit_id' => [
                'nullable',
                'integer'
            ],

            'rounds.*.race_type' => [
                'required',
                'string'
            ],

            'rounds.*.race_length' => [
                'required',
                'integer'
            ]

        ]);

        $seriesId = $service->sync($data);

        return response()->json([

            'success' => true,

            'series_id' => $seriesId,

            'rounds' => count($data['rounds']),

            'message' => 'Rounds synchronized successfully.'

        ]);
    }
}
