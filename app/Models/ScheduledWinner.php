<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduledWinner extends Model
{
    protected $fillable = [
        'nik',
        'name',
        'prize_id',
        'priority',
        'is_used',
    ];

    protected $casts = [
        'is_used'  => 'boolean',
        'priority' => 'integer',
    ];

    public function prize()
    {
        return $this->belongsTo(Prize::class);
    }
}
