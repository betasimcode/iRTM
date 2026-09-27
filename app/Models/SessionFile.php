<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SessionFile extends Model
{
    protected $fillable = [

        'session_id',

        'user_id',

        'type',

        'filename',

        'filepath',

        'filesize',

        'filehash',
    ];

    // ========================================
    // SESSION
    // ========================================

    public function session()
    {
        return $this->belongsTo(
            IrSession::class,
            'session_id'
        );
    }

    // ========================================
    // USER
    // ========================================

    public function user()
    {
        return $this->belongsTo(
            User::class
        );
    }
}