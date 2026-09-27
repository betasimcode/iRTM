<?php

namespace App\Services\Stints;

use App\Models\Stint;

class StintQueryService
{
    // =====================================================
    // BASE QUERY
    // =====================================================

    public static function base()
    {
        return Stint::query()

            ->valid()

            ->with([
                'user.team',
                'track',
                'session',
            ])

            ->withCount('laps')

            ->withAvg('laps', 'lap_time');
    }

    // =====================================================
    // USER
    // =====================================================

    public static function forUser($userId)
    {
        return self::base()

            ->where(
                'user_id',
                $userId
            );
    }

    // =====================================================
    // SERIES
    // =====================================================

    public static function forSeries($seriesId)
    {
        return self::base()

            ->where(
                'series_id',
                $seriesId
            );
    }

    // =====================================================
    // SESSION
    // =====================================================

    public static function forSession($subsessionId)
    {
        return self::base()

            ->where(
                'iracing_subsession_id',
                $subsessionId
            );
    }

    // =====================================================
    // TEAM
    // =====================================================

    public static function forTeam($teamId)
    {
        return self::base()

            ->whereHas(

                'user',

                fn($q) =>

                    $q->where(
                        'team_id',
                        $teamId
                    )
            );
    }
}