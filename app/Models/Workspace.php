<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Workspace extends Model
{
    protected $fillable = [

        'type',

        'slug',

        'owner_user_id',

        'team_id',

        'is_active'

    ];

    protected $casts = [

        'is_active' => 'boolean'

    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function owner(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'owner_user_id'
        );
    }

    public function team()
    {
        return $this->belongsTo(
            Team::class,
            'team_id'
        );
    }

    public function seriesEntries()
    {
        return $this->hasMany(
            SeriesEntry::class,
            'workspace_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function isDriver(): bool
    {
        return $this->type === 'driver';
    }

    public function isTeam(): bool
    {
        return $this->type === 'team';
    }

    protected function name(): Attribute
    {
        return Attribute::make(

            get: fn () => match ($this->type) {

                'driver' => $this->owner?->name,

                'team' => $this->team?->name,

                default => 'Workspace'

            }

        );
    }
}
