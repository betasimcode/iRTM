<?php

namespace App\Models;
use App\Models\Setups;

use Illuminate\Database\Eloquent\Model;

class SetupValue extends Model
{
    protected $fillable = [
        'setup_id',
        'key',
        'value'
    ];

    public function setup()
    {
        return $this->belongsTo(Setup::class);
    }

    public function getParsedValueAttribute()
    {
        return $this->value;
    }

    public function getAlertAttribute()
    {
        // placeholder
        return null;
    }

    public function definition()
{
    return $this->belongsTo(
        SetupItemDefinition::class,
        'key',
        'raw_key'
    );
}


}
