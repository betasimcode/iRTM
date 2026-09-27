<?php
namespace App\Http\Controllers;

use App\Services\IracingService;
use App\Models\IracingSerie;

class IracingSyncController extends Controller
{
    public function syncSeries(IracingService $iracing)
    {
        try {

            \Log::info('SYNC SERIES START');

            $data = $iracing->getSeries();

            if (!isset($data['data'])) {
                return response()->json([
                    'error' => 'Invalid response',
                    'raw' => $data
                ], 500);
            }

            $count = 0;

            foreach ($data['data'] as $series) {

                IracingSerie::updateOrCreate(
                    [
                        'iracing_series_id' => $series['series_id']
                    ],
                    [
                        'name' => $series['series_name'] ?? null,
                        'category' => $series['category'] ?? null,
                        'license' => $series['license_group'] ?? null,
                    ]
                );

                $count++;
            }

            return response()->json([
                'status' => 'ok',
                'synced' => $count
            ]);

        } catch (\Exception $e) {

            \Log::error('SYNC SERIES ERROR: ' . $e->getMessage());

            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}