<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use App\Models\Series;
use App\Models\SeriesRound;
use App\Models\User;
use App\Services\Racing\RaceScheduleService;

class RaceScheduleController extends Controller
{
    public function index(
        RaceScheduleService $scheduleService
    ): View {

        $hoursVisible = request()
                ->integer(
                    'range',
                    24
                );

                if (

                    ! in_array(

                        $hoursVisible,

                        [6, 12, 24, 48]

                    )

                ) {

                    $hoursVisible = 24;

                }

            $timeMode = request('time', 'utc');

            $userTimezone = auth()->user()->timezone ?? 'UTC';

            if (! in_array($timeMode, ['utc', 'local'])) {
            $timeMode = 'utc';
            }

            $displayTimezone =
                $timeMode === 'local'
                    ? $userTimezone
                    : 'UTC';

        $slotWidth = 100;

        $start = now()
            ->startOfHour();

        $end = $start
            ->copy()
            ->addHours(
                $hoursVisible
            );

        $currentSlot = floor(

            $start->diffInMinutes(
                now()
            ) / 15
        );

        $events = [];

        $workspace = workspace();

        $events = [];

        if ($workspace->isTeam()) {
            $events = $scheduleService->generateTeamEvents(
                $workspace->team()->id,
                $start,
                $end
            );
        }

        $timeSlots = [];

        for (
            $i = 0;
            $i < ($hoursVisible * 4);
            $i++
        ) {

            $timeSlots[] =

                $start
                    ->copy()
                    ->addMinutes(
                        $i * 15
                    );

        }


        $timeline = [];

        $seriesList = Series::query()

            ->with([
                'iracingSeries',
                'rounds.track'
            ])

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

                $currentRound = $series->rounds

                ->first(function ($round) use ($event) {

                    return $event['event_time']->between(

                        $round->week_start,

                        $round->week_end

                    );

                });

                $slot = floor(

                    $start->diffInMinutes(

                        $event['event_time'],

                        false
                    ) / 15
                );



                if (
                    $slot < 0 ||
                    $slot >= ($hoursVisible * 4)
                ) {
                    continue;
                }

                $key =

                    $series->id
                    . '_'
                    . $event['event_time']->timestamp;

                    $registrationOpen =

                        now()->gte(
                            $event['registration_time']
                        )

                        &&

                        now()->lt(
                            $event['event_time']
                        );

                    $raceLive =

                        now()->between(

                            $event['event_time'],

                            $event['event_time']
                                ->copy()
                                ->addMinutes(15)
                        );

                    $registrationStatus =

                        now()->gte(
                            $event['event_time']
                        )

                            ? 'CLOSED'

                            : (

                                $registrationOpen

                                    ? 'OPEN'

                                    : 'PENDING'
                            );

                    $nextEvents = $seriesEvents

                        ->filter(function ($e) use ($event) {

                            return $e['event_time']

                                ->gt($event['event_time']);

                        })

                        ->take(3)

                        ->values();

                $markers[] = [

                    'key' => $key,

                    'slot' => $slot,

                    'time' => $event['event_time']
                            ->format('H:i'),

                    'event_time' => $event['event_time']
                            ->toDateTimeString(),

                    'registration_time' => $event['registration_time']
                            ->toDateTimeString(),

                    'event_time_local' => $timeMode === 'local'
                        ? $event['event_time']->copy()->timezone($userTimezone)->format('H:i')
                        : $event['event_time']->format('H:i'),

                    'series_id' => $series->id,

                    'series_name' => $series->iracingSeries->name,

                    'series_short_name' => $series->iracingSeries->short_name,

                    'track_logo' => $currentRound?->track?->logo,

                    'track_name' => $currentRound?->track?->name,

                    'variant' => $currentRound?->track?->variant,

                    'logo' => $series->iracingSeries->logo_path,

                    'registration_open_minutes' => $series
                            ->iracingSeries
                            ->registration_open_minutes,

                    'race_interval_minutes' => $series
                            ->iracingSeries
                            ->race_interval_minutes,

                            'registration_open' => $registrationOpen,

                    'race_live' => $raceLive,

                    'registration_status' => $registrationStatus,

                    'registration_timestamp' => $event['registration_time']->timestamp,

                    'event_timestamp' => $event['event_time']->timestamp,

                    'next_events' => $nextEvents
                    ->map(function ($e) use ($series, $timeMode, $userTimezone) {

                        $round = $series->rounds

                            ->first(function ($round) use ($e) {

                                return $e['event_time']

                                    ->between(

                                        $round->week_start,

                                        $round->week_end

                                    );

                            });

                        return [

                           'time' => $timeMode === 'local'
                                ? $e['event_time']->copy()->timezone($userTimezone)->format('H:i')
                                : $e['event_time']->format('H:i'),

                            'track' =>

                                $round?->track?->name

                        ];

                    })
                        ->toArray(),

                    'race_status' => $raceLive
                            ? 'LIVE'
                            : (
                                now()->lt($event['event_time'])
                                    ? 'WAITING'
                                    : 'FINISH'
                            ),

                ];
            }

            $timeline[] = [

                'series_id' => $series->id,

                'name' => $series
                        ->iracingSeries
                        ->short_name,

                'track_name' => $currentRound?->track?->name,

                'logo' => $series
                        ->iracingSeries
                        ->logo_path,

                'markers' => $markers
            ];
        }

        $showSeriesHeader = true;

        return view(
            'modules.race-timeline-module',
            compact(
                'timeline',
                'timeSlots',
                'currentSlot',
                'slotWidth',
                'hoursVisible',
                'timeMode',
                'userTimezone',
                'displayTimezone',
                'showSeriesHeader'
            )
        );
    }
}
