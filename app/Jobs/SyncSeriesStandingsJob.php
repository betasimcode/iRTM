<?php

namespace App\Jobs;

use App\Models\Series;
use App\Services\Iracing\SeriesStandingsSyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncSeriesStandingsJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $seriesId,
        public string $syncType = 'weekly'
    ) {}

    public function handle(
        SeriesStandingsSyncService $service
    ): void {
        $series = Series::query()
            ->with('iracingSeries')
            ->findOrFail($this->seriesId);

        try {
            $service->syncSeason(
                $series,
                $this->syncType
            );
        } catch (Throwable $e) {
            Log::error(
                'Standings job failed.',
                [
                    'series_id' => $this->seriesId,
                    'error' => $e->getMessage(),
                ]
            );

            throw $e;
        }
    }
}
