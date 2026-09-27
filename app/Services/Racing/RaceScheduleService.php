<?php

namespace App\Services\Racing;

use Carbon\Carbon;
use App\Services\Workspace\TeamWorkspace;
use Illuminate\Support\Collection;

class RaceScheduleService
{
    public function generateEvents(

        Carbon $from,
        Carbon $to,
        string $weekStartDay,
        string $weekStartTime,
        int $intervalMinutes
    ): Collection {

        $events = collect();

        $cursor = $this->getSeriesAnchor(
            $weekStartDay,
            $weekStartTime
        );

        while ($cursor <= $to) {

            if ($cursor >= $from) {

                $events->push(
                    $cursor->copy()
                );
            }

            $cursor->addMinutes(
                $intervalMinutes
            );
        }

        return $events;
    }

    private function getSeriesAnchor(
        string $weekStartDay,
        string $weekStartTime
    ): Carbon {

        $dayMap = [

            'Monday'    => Carbon::MONDAY,
            'Tuesday'   => Carbon::TUESDAY,
            'Wednesday' => Carbon::WEDNESDAY,
            'Thursday'  => Carbon::THURSDAY,
            'Friday'    => Carbon::FRIDAY,
            'Saturday'  => Carbon::SATURDAY,
            'Sunday'    => Carbon::SUNDAY,
        ];

        $anchor = now()

            ->startOfWeek(
                Carbon::MONDAY
            );

        while (
            $anchor->dayOfWeek
            !== $dayMap[$weekStartDay]
        ) {
            $anchor->addDay();
        }

        [$hour, $minute] = explode(
            ':',
            $weekStartTime
        );

        $anchor->setTime(
            (int) $hour,
            (int) $minute
        );

        if ($anchor->gt(now())) {

            $anchor->subWeek();
        }

        return $anchor;
    }

    public function generateTeamEvents(
        Carbon $from,
        Carbon $to
    ): Collection
    {
        $workspace = workspace();

        $events = collect();

        $seriesList = workspace()
            ->series()
            ->with('iracingSeries')
            ->where('status', 'active')
            ->get();

foreach ($seriesList as $series) {

    if (! $series->iracingSeries) {

        continue;
    }

    $raceEvents = $this->generateEvents(

        $from,

        $to,

        $series
            ->iracingSeries
            ->week_start_day,

        $series
            ->iracingSeries
            ->week_start_time,

        $series
            ->iracingSeries
            ->race_interval_minutes
    );

    foreach ($raceEvents as $eventTime) {

        $events->push([

            'series_id' => $series->id,

            'series_name' => $series->name,

            'series_short_name' =>

                $series
                    ->iracingSeries
                    ->short_name,

            'event_time' => $eventTime,

            'registration_time' =>

                $eventTime->copy()

                    ->subMinutes(

                        $series
                            ->iracingSeries
                            ->registration_open_minutes
                    ),

            'minutes_until_registration' =>

                now()->diffInMinutes(

                    $eventTime->copy()

                        ->subMinutes(

                            $series
                                ->iracingSeries
                                ->registration_open_minutes
                        ),

                    false
                ),
        ]);
    }
}

return $events

    ->sortBy('event_time')

    ->values();
    }




}
