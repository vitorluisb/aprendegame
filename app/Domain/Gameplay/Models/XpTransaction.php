<?php

namespace App\Domain\Gameplay\Models;

use App\Domain\Accounts\Models\Student;
use Database\Factories\Domain\Gameplay\Models\XpTransactionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class XpTransaction extends Model
{
    /** @use HasFactory<XpTransactionFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'student_id',
        'amount',
        'reason',
        'reference_type',
        'reference_id',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
