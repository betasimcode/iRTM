<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Jobs\SyncSeriesStandingsJob;
use App\Models\Series;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    Series::query()
        ->whereNotNull('iracing_season_id')
        ->whereHas('standingConfigs', function ($query) {
            $query->where('enabled', true);
        })
        ->whereDoesntHave('standings', function ($query) {
            $query->where('status', 'finalized');
        })
        ->select('id')
        ->chunkById(100, function ($seriesList) {
            foreach ($seriesList as $series) {
                SyncSeriesStandingsJob::dispatch(
                    $series->id,
                    'weekly'
                );
            }
        });
})
    ->weeklyOn(1, '04:00')
    ->name('iracing-series-standings-weekly')
    ->withoutOverlapping();
