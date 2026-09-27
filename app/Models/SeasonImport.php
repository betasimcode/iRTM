<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\IracingSerie;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeasonImport extends Model
{
    protected $fillable = [
        'ir_serie_id',
        'year',
        'season',
        'title',
        'page_start',
        'page_end',
    ];

    public function iracingSeries(): BelongsTo
    {
        return $this->belongsTo(
            IracingSerie::class,
            'ir_serie_id'
        );
    }
}
