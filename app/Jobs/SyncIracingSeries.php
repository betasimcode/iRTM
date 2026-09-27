<?php

namespace App\Jobs;

use App\Models\IracingSeries;
use App\Services\IracingService;

class SyncIracingSeries
{
    public function handle(\App\Services\IracingService $iracing)
{
    \Log::info('SYNC START');

    $data = $iracing->getSeries();

    if (!isset($data['data'])) {
        \Log::error('Invalid response', $data);
        return;
    }

    foreach ($data['data'] as $series) {

        \App\Models\IracingSeries::updateOrCreate(
            [
                'iracing_series_id' => $series['series_id']
            ],
            [
                'name' => $series['series_name'] ?? null,
                'category' => $series['category'] ?? null,
                'license' => $series['license_group'] ?? null,
            ]
        );
    }

    \Log::info('Series sync completed');
}
}
