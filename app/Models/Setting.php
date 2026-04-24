<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $primaryKey = 'params_key';
    protected $keyType    = 'string';
    public    $incrementing = false;

    protected $fillable = [
        'params_key',
        'params_value',
    ];

    protected $casts = [
        'params_value' => 'array',
    ];
}
