<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\Team;

class LapCompleted implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public $driver;
    public $team;
    public $lap;
    public $lapTime;
    public $gap;

    public function __construct($telemetry,$gap,$bestLap)
    {
        $driver = User::find($telemetry->user_id);

        $this->driver = $driver->name ?? "Driver";
        $this->team = $driver->team->name ?? "No Team";

        $this->lap = $telemetry->lap;
        $this->lapTime = round($telemetry->lap_time,3);
        $this->gap = $gap;
    }

    public function broadcastOn()
    {
        return new Channel('livetiming');
    }
}
