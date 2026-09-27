<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\SetupValue;

class Setup extends Model
{
    protected $fillable = [
        'stint_id',
        'user_id',
        'team_id',
        'car_id',
        'track_id',
        'name',
        'description',
        'visibility',
        'setup_file_path',
        'setup_file_hash',
        'setup_file_size',
        'setup_name',
        'setup_type',
        'setup_weather',
        'data'
    ];

    protected $casts = [
        'data' => 'array'
    ];

    public function car()
    {
        return $this->belongsTo(
            Car::class,
            'car_id'
        );
    }

    public function track()
    {
        return $this->belongsTo(
            Track::class,
            'track_id'
        );
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function stints()
    {
        return $this->hasMany(Stint::class);

    }public function values()
    {
        return $this->hasMany(SetupValue::class);
    }

        // Setup.php
    public function stint()
    {
        return $this->belongsTo(Stint::class);
    }

}
