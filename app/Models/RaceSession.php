<?php

namespace App\Models;

use App\Models\SeriesRound;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $round_id
 * @property int $car_id
 * @property int $circuit_id
 * @property numeric $lap_time
 * @property numeric $fuel_used
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $tyre_choice
 * @property string|null $session_type
 * @property int|null $laps_done
 * @property string|null $scheduled_at
 * @property numeric|null $fuel_start
 * @property numeric|null $fuel_end
 * @property numeric|null $track_temp
 * @property numeric|null $air_temp
 * @property string|null $weather
 * @property int|null $incidents
 * @property string|null $notes
 * @property-read \App\Models\Car $car
 * @property-read \App\Models\Circuit $circuit
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RaceSession newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RaceSession newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RaceSession query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RaceSession whereAirTemp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RaceSession whereCarId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RaceSession whereCircuitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RaceSession whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RaceSession whereFuelEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RaceSession whereFuelStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RaceSession whereFuelUsed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RaceSession whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RaceSession whereIncidents($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RaceSession whereLapTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RaceSession whereLapsDone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RaceSession whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RaceSession whereRoundId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RaceSession whereScheduledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RaceSession whereSessionType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RaceSession whereTrackTemp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RaceSession whereTyreChoice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RaceSession whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RaceSession whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RaceSession whereWeather($value)
 * @mixin \Eloquent
 */
class RaceSession extends Model
{
    protected $fillable = [
        'car_id',
        'circuit_id',
        'scheduled_at',
        'laps_done'
    ];

    protected $dates = ['scheduled_at'];

    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    public function circuit()
    {
        return $this->belongsTo(Circuit::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function round()
    {
        return $this->belongsTo(Round::class);
    }


}
