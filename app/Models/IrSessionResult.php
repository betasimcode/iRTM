<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class IrSessionResult extends Model
{
    protected $table = 'ir_session_results';

    protected $guarded = [];

    public function session()
    {
        return $this->belongsTo(
            IrSession::class,
            'iracing_subsession_id',
            'iracing_subsession_id'
        );
    }

    public function user()
    {
        return $this->belongsTo(
            User::class,
            'iracing_user_id',
            'iracing_user_id'
        );
    }


}