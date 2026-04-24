<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Winner extends Model
{
    protected $fillable = [
        'participant_id',
        'prize_id',
        'drawn_at',
    ];

    protected $casts = [
        'drawn_at' => 'datetime',
    ];

    public function participant()
    {
        return $this->belongsTo(Participant::class)->withTrashed();
    }

    public function prize()
    {
        return $this->belongsTo(Prize::class);
    }
}
