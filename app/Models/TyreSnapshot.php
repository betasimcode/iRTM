<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $stint_id
 * @property int|null $lap_number
 * @property string|null $tyre_compound
 * @property float|null $wear_fl
 * @property float|null $wear_fr
 * @property float|null $wear_rl
 * @property float|null $wear_rr
 * @property float|null $degradation_per_lap
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $tyre_set_number
 * @property-read \App\Models\Stint $stint
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TyreSnapshot newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TyreSnapshot newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TyreSnapshot query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TyreSnapshot whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TyreSnapshot whereDegradationPerLap($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TyreSnapshot whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TyreSnapshot whereLapNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TyreSnapshot whereStintId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TyreSnapshot whereTyreCompound($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TyreSnapshot whereTyreSetNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TyreSnapshot whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TyreSnapshot whereWearFl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TyreSnapshot whereWearFr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TyreSnapshot whereWearRl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TyreSnapshot whereWearRr($value)
 * @mixin \Eloquent
 */
class TyreSnapshot extends Model
{
    protected $fillable = [
        'stint_id',
        'lap_number',
        'tyre_compound',
        'wear_fl',
        'wear_fr',
        'wear_rl',
        'wear_rr',
        'degradation_per_lap',
        'tyre_set_number',
    ];

public function stint()
{
    return $this->belongsTo(Stint::class);
}








}
