<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StintFile extends Model
{
    protected $table = 'stint_files';

    protected $guarded = [];

    public function stint()
    {
        return $this->belongsTo(
            Stint::class,
            'stint_id'
        );
    }

    public function user()
    {
        return $this->belongsTo(
            User::class,
            'user_id'
        );
    }
}
