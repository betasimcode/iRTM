<?php

namespace App\Models;

use App\Models\SeriesEntryMember;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SeriesEntry extends Model
{
    protected $fillable = [

        'workspace_id',

        'series_id',

        'competition_car_id',

        'status',

        'joined_at',

        'left_at',

        'data_policy',

        'created_by'

    ];

    protected $casts = [

        'joined_at' => 'datetime',

        'left_at' => 'datetime',

    ];

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    /**
     * Workspace inscrito.
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(
            Workspace::class,
            'workspace_id'
        );
    }

    /**
     * Serie oficial.
     */
    public function series(): BelongsTo
    {
        return $this->belongsTo(
            Series::class,
            'series_id'
        );
    }

    /**
     * Vehículo utilizado.
     */
    public function competitionCar(): BelongsTo
    {
        return $this->belongsTo(
            Car::class,
            'competition_car_id'
        );
    }

    /**
     * Usuario que creó la inscripción.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    /**
     * Miembros inscritos.
     */
    public function members(): HasMany
    {
        return $this->hasMany(
            SeriesEntryMember::class,
            'series_entry_id'
        );
    }
}
