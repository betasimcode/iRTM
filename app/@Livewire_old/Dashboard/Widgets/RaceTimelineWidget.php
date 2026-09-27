<?php

use App\Models\Series;
use App\Services\Racing\RaceScheduleService;

class RaceTimelineWidget extends Component
{
    public array $timeline = [];
    public array $timeSlots = [];
    public int $hoursVisible = 6;
    public int $slotWidth = 100;
    public float $nowPosition = 0;
    public int $currentSlot = 0;
    public array $eventIndex = [];
    public ?array $selectedEvent = null;


    public function mount(
        RaceScheduleService $scheduleService
    ) {

        $start = now()

            ->startOfHour();


            $this->currentSlot = floor(

                $start->diffInMinutes(
                    now()
                ) / 15
            );


        $end = $start

            ->copy()

            ->addHours(
                $this->hoursVisible
            );

        $events = $scheduleService
            ->generateTeamEvents(

                auth()->user()->team_id,

                $start,

                $end
            );
            for (
                $i = 0;
                $i < ($this->hoursVisible * 4);
                $i++
            ) {

                $this->timeSlots[] =
            
                    $start
            
                        ->copy()
            
                        ->addMinutes(
                            $i * 15
                        );
            }

        $seriesList = Series::query()

            ->with('iracingSeries')

            ->where(
                'team_id',
                auth()->user()->team_id
            )

            ->where(
                'status',
                'active'
            )

            ->get();

        foreach ($seriesList as $series) {

            $seriesEvents = collect($events)

                ->where(
                    'series_id',
                    $series->id
                );

            $markers = [];

            foreach ($seriesEvents as $event) {

                $slot = floor(

                    $start->diffInMinutes(
                
                        $event['event_time']
                
                    ) / 15
                );
                
                if (
                    $slot < 0 ||
                    $slot >= ($this->hoursVisible * 4)
                ) {
                    continue;
                }
                
                $key = $series->id . '_' . $event['event_time']->timestamp;

                    $this->eventIndex[$key] = [

                        'series_id' => $series->id,

                        'series_name' =>
                            $series->iracingSeries->name,

                        'series_short_name' =>
                            $series->iracingSeries->short_name,

                        'logo' =>
                            $series->iracingSeries->logo_path,

                        'event_time' =>
                            $event['event_time']->toDateTimeString(),

                        'registration_time' =>
                            $event['registration_time']->toDateTimeString(),
                    ];

                $markers[] = [

                    'key' => $key,

                    'slot' => $slot,
                
                    'time' =>
                
                        $event['event_time']
                            ->format('H:i'),
                
                    'event_time' =>
                
                        $event['event_time']
                            ->toDateTimeString(),
                
                    'registration_time' =>
                
                        $event['registration_time']
                            ->toDateTimeString(),
                
                    'series_id' =>
                
                        $series->id,
                
                    'series_name' =>
                
                        $series->iracingSeries->name,
                
                    'series_short_name' =>
                
                        $series->iracingSeries->short_name,
                
                    'logo' =>
                
                        $series->iracingSeries->logo_path
                ];
            }

            $this->timeline[] = [

                'series_id' => $series->id,

                'name' =>

                    $series
                        ->iracingSeries
                        ->short_name,

                'logo' =>

                    $series
                        ->iracingSeries
                        ->logo_path,

                'markers' => $markers
            ];
        }
    }




}