<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessIbtJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public $path;
    public $stintId;

    public function __construct($path, $stintId)
    {
        $this->path = $path;
        $this->stintId = $stintId;
    }

    public function handle()
    {
        $content = \Storage::get($this->path);

        // TODO: aquí metes TODO tu parseo actual
    }
}
