<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeriesEntryMember extends Model
{
    protected $fillable = [

        'series_entry_id',

        'user_id',

        'role',

        'status',

        'joined_at',

        'left_at'

    ];

    protected $casts = [

        'joined_at' => 'datetime',

        'left_at'   => 'datetime',

    ];

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    /**
     * Inscripción a la que pertenece.
     */
    public function entry(): BelongsTo
    {
        return $this->belongsTo(
            SeriesEntry::class,
            'series_entry_id'
        );
    }

    /**
     * Piloto.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'user_id'
        );
    }
}
