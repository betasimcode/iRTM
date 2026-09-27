<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Track;

class IrSession extends Model
{
    protected $table = 'ir_sessions';

    protected $fillable = [
        'name',
        'iracing_subsession_id',
        'serie_id'
    ];

    public function phases()
    {
        return $this->hasMany(IrSessionPhase::class, 'session_id');
    }
    
    public function stints()
    {
        return $this->hasMany(
            \App\Models\Stint::class,
            'iracing_subsession_id',
            'iracing_subsession_id'
        );
    }

    public function results()
    {
        return $this->hasMany(
            IrSessionResult::class,
            'iracing_subsession_id',
            'iracing_subsession_id'
        );
    }

    public function series()
    {
        return $this->belongsTo(\App\Models\IracingSerie::class, 'serie_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function files()
    {
        return $this->hasMany(
            SessionFile::class,
            'session_id'
        );
    }











}