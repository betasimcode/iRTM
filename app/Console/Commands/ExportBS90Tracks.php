<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BS90\TracksExporter;

class ExportBS90Tracks extends Command
{
    protected $signature = 'bs90:export-tracks';

    protected $description = 'Export track sectors for BS90';

    public function handle()
    {
        $exporter = new TracksExporter();

        $exporter->build();

        $this->info('BS90.Data.Tracks.js exported successfully.');

        return self::SUCCESS;
    }
}

/*use  -> php artisan bs90:export-tracks */
