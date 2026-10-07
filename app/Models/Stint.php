<?php

namespace App\Models;
use App\Models\User;
use App\Models\IrSession;
use App\Models\IrLap;
use App\Models\Setup;
use App\Models\IrTelemetryTyre;
use Illuminate\Database\Eloquent\Model;

class Stint extends Model {

    protected $table = 'ir_stints'; // Forzamos la tabla nueva
    protected $guarded = [];

    public function user() { return $this->belongsTo(User::class); }

   // Stint model
   public function laps()
   {
       return $this->hasMany(\App\Models\IrLap::class, 'ir_stint_id');
   }

    public function session()
    {
        return $this->belongsTo(
            IrSession::class,
            'iracing_subsession_id',
            'iracing_subsession_id'
        );
    }

    public function files()
    {
        return $this->hasMany(
            StintFile::class,
            'stint_id'
        );
    }

    public function scopeValid($query)
    {
        return $query->has('laps', '>=', 1);
    }

    public function getDurationSecondsAttribute()
    {
        return $this->laps()->sum('lap_time');
    }

    public function getSeriesAttribute()
    {
        return $this->session?->series;
    }

    public function getFuelConsumedAttribute()
    {
        return $this->laps->sum('fuel_consumed');
    }

    public function getLapsCountAttribute()
    {
        return $this->laps->count();
    }

    public function getAvgLapAttribute()
    {
        if (!$this->relationLoaded('laps')) {
            $this->load('laps');
        }

        $laps = $this->laps
            ->pluck('lap_time')
            ->filter(fn($l) => $l > 0 && $l < 200)
            ->values();

        if ($laps->count() < 3) {
            return null;
        }

        $best = $laps->min();
        $threshold = $best * 1.07;

        $valid = $laps->filter(fn($l) => $l <= $threshold)->values();

        if ($valid->count() < 3) {
            return $valid->avg();
        }

        // 🔥 TOP 30%
        $sorted = $valid->sort()->values();
        $top = $sorted->take(max(3, ceil($sorted->count() * 0.95)));

        return $top->avg();
    }

    public function getAvgFuelAttribute()
    {
        return $this->laps
            ->where('fuel_consumed', '>', 0)
            ->avg('fuel_consumed');
    }

    public function tyres()
    {
        return $this->hasMany(IrTelemetryTyre::class, 'stint_id');
    }

  public function track()
    {
        return $this->belongsTo(Track::class, 'track_id', 'iracing_track_id');
    }

    public function setup()
    {
        return $this->belongsTo(Setup::class);
    }

    public function series()
    {
        return $this->belongsTo(Series::class);
    }

    public function getDisplayCarAttribute()
    {
        if ($this->series && $this->series->teamCar) {
            return $this->series->teamCar;
        }

        return $this->car ?? null;
    }

    public function getDisplayImageAttribute()
    {
        if ($this->series && $this->series->teamCar?->image_path) {
            return asset('storage/'.$this->series->teamCar->image_path);
        }

        return asset('storage/'.$this->car->image_path);
    }

    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    public function tyreTelemetry()
    {
        return $this->hasMany(IrTelemetryTyre::class);
    }
}
