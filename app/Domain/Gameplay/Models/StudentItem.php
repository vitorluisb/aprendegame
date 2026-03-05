<?php

namespace App\Domain\Gameplay\Models;

use App\Domain\Accounts\Models\Student;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'item_id',
        'equipped',
        'purchased_at',
    ];

    protected function casts(): array
    {
        return [
            'equipped' => 'boolean',
            'purchased_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(ShopItem::class, 'item_id');
    }
}
