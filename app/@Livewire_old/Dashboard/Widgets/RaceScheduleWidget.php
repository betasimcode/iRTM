<?php


use Carbon\Carbon;
use App\Services\Racing\RaceScheduleService;

class RaceScheduleWidget extends Component
{
    public $events;

    public function mount(
        RaceScheduleService $scheduleService
    ) {

        $this->events = $scheduleService
            ->generateTeamEvents(

                auth()->user()->team_id,

                now(),

                now()->addHours(12)
            );
    }


}