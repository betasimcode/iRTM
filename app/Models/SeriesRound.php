<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * @property int $id
 * @property int $series_id
 * @property int $week
 * @property \Illuminate\Support\Carbon|null $week_start
 * @property \Illuminate\Support\Carbon|null $week_end
 * @property int $circuit_id
 * @property string $race_type
 * @property int $race_length
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Circuit $circuit
 * @property-read mixed $status
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RaceSession> $raceSessions
 * @property-read int|null $race_sessions_count
 * @property-read \App\Models\Series $series
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeriesRound newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeriesRound newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeriesRound query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeriesRound whereCircuitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeriesRound whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeriesRound whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeriesRound whereRaceLength($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeriesRound whereRaceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeriesRound whereSeriesId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeriesRound whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeriesRound whereWeek($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeriesRound whereWeekEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeriesRound whereWeekStart($value)
 * @mixin \Eloquent
 */
class SeriesRound extends Model
{
    protected $fillable = [
        'series_id',
        'week',
        'week_start',
        'week_end',
        'circuit_id',
        'race_type',
        'race_length'
    ];

    protected $casts = [
    'week_start' => 'datetime',
    'week_end' => 'datetime',
    ];

    public function series()
    {
        return $this->belongsTo(Series::class);
    }

   public function track()
    {
        return $this->belongsTo(Track::class, 'circuit_id');
    }

    public function raceSessions()
    {
        return $this->hasMany(\App\Models\RaceSession::class, 'round_id');
    }

    public function getStatusAttribute()
    {
        $now = Carbon::now();

        if (! $this->week_start || ! $this->week_end) {
            return 'future';
        }

        $start = Carbon::parse($this->week_start);
        $end = Carbon::parse($this->week_end);

        if ($now->lt($start)) {
            return 'future';
        }

        if ($now->gte($start) && $now->lt($end)) {
            return 'active';
        }

        return 'past';
    }





}
