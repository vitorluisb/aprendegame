<?php

namespace App\Domain\QuizMestre\Models;

use Database\Factories\Domain\QuizMestre\Models\GkQuestionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GkQuestion extends Model
{
    /** @use HasFactory<GkQuestionFactory> */
    use HasFactory;

    protected $table = 'gk_questions';

    protected $fillable = [
        'category_id',
        'question_text',
        'option_a',
        'option_b',
        'option_c',
        'option_d',
        'correct_option',
        'explanation',
        'difficulty',
        'age_group',
        'is_active',
        'source_reference',
        'metadata_json',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'metadata_json' => 'array',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(GkCategory::class, 'category_id');
    }

    /**
     * @return array<string, string>
     */
    public function optionsMap(): array
    {
        return [
            'A' => $this->option_a,
            'B' => $this->option_b,
            'C' => $this->option_c,
            'D' => $this->option_d,
        ];
    }
}
