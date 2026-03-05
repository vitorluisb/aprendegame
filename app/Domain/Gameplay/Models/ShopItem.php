<?php

namespace App\Domain\Gameplay\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShopItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'slug',
        'description',
        'image_url',
        'gem_price',
        'active',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'gem_price' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function studentItems(): HasMany
    {
        return $this->hasMany(StudentItem::class, 'item_id');
    }
}
