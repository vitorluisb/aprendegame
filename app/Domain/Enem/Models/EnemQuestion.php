<?php

namespace App\Domain\Enem\Models;

use Database\Factories\Domain\Enem\Models\EnemQuestionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnemQuestion extends Model
{
    /** @use HasFactory<EnemQuestionFactory> */
    use HasFactory;

    protected $table = 'enem_questions';

    protected $fillable = [
        'area',
        'subject',
        'enem_code',
        'context_text',
        'question_text',
        'option_a',
        'option_b',
        'option_c',
        'option_d',
        'option_e',
        'correct_option',
        'difficulty',
        'year_reference',
        'explanation',
        'status',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'year_reference' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (EnemQuestion $question): void {
            if (! filled($question->enem_code)) {
                $question->enem_code = self::nextCodeForArea((string) $question->area);
            }
        });
    }

    public static function nextCodeForArea(string $area): string
    {
        $prefix = match ($area) {
            'matematica' => 'MAT',
            'humanas' => 'HUM',
            'natureza' => 'NAT',
            default => 'LIN',
        };

        $lastCode = self::query()
            ->where('area', $area)
            ->where('enem_code', 'like', "ENEM-{$prefix}-%")
            ->orderByDesc('id')
            ->value('enem_code');

        $lastNumber = 0;

        if (is_string($lastCode) && preg_match('/(\d{4})$/', $lastCode, $matches) === 1) {
            $lastNumber = (int) $matches[1];
        }

        $nextNumber = str_pad((string) ($lastNumber + 1), 4, '0', STR_PAD_LEFT);

        return "ENEM-{$prefix}-{$nextNumber}";
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
            'E' => $this->option_e,
        ];
    }
}
