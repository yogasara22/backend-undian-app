<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prize extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'value',
        'image_url',
        'qty',
    ];

    protected $casts = [
        'qty' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function winners()
    {
        return $this->hasMany(Winner::class);
    }

    public function isAvailable(): bool
    {
        return $this->getRemainingQtyAttribute() > 0;
    }

    public function getRemainingQtyAttribute(): int
    {
        // Jika winners_count sudah di-load menggunakan withCount('winners')
        if ($this->relationLoaded('winners') || array_key_exists('winners_count', $this->getAttributes())) {
            $winnersCount = $this->winners_count ?? $this->winners->count();
        } else {
            $winnersCount = $this->winners()->count();
        }
        
        return max(0, $this->qty - $winnersCount);
    }
}
