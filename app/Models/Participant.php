<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Participant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'nik',
        'email',
        'phone_number',
        'department',
        'shop_name',
        'address',
    ];

    public function winners()
    {
        return $this->hasMany(Winner::class);
    }

    public function hasWon(): bool
    {
        return $this->winners()->exists();
    }
}
