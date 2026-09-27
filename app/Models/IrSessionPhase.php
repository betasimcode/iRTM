<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IrSessionPhase extends Model
{
    protected $table = 'ir_session_phases';

    protected $fillable = [
        'session_id',
        'session_type',
        'session_num'
    ];

    public function session()
    {
        return $this->belongsTo(IrSession::class);
    }

    public function stints()
    {
        return $this->hasMany(Stint::class, 'session_phase', 'session_type');
    }










}