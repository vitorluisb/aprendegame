<?php

namespace App\Domain\QuizMestre\Models;

use Database\Factories\Domain\QuizMestre\Models\GkCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GkCategory extends Model
{
    /** @use HasFactory<GkCategoryFactory> */
    use HasFactory;

    protected $table = 'gk_categories';

    protected $fillable = [
        'name',
        'description',
        'icon',
    ];

    public function questions(): HasMany
    {
        return $this->hasMany(GkQuestion::class, 'category_id');
    }
}
