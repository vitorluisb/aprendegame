<?php

namespace App\Domain\AI\Services;

use App\Domain\Accounts\Models\Student;
use App\Domain\AI\Models\TutorMessage;

class ModerationService
{
    /** @var string[] */
    private const BLOCKED_TOPICS = [
        'violência',
        'violencia',
        'pornografia',
        'sexo',
        'drogas',
        'armas',
        'automutilação',
        'automutilacao',
        'suicídio',
        'suicidio',
        'ódio',
        'odio',
        'racismo',
        'hack',
        'invasão',
        'invasao',
    ];

    /** Daily message limits per age group */
    private const DAILY_LIMITS = [
        'child' => 20,   // up to 12 years
        'teen' => 40,    // 13–17 years
        'adult' => 100,  // 18+
    ];

    public function isBlocked(string $message): bool
    {
        $normalized = mb_strtolower($message);

        foreach (self::BLOCKED_TOPICS as $topic) {
            if (str_contains($normalized, $topic)) {
                return true;
            }
        }

        return false;
    }

    public function blockedReason(string $message): ?string
    {
        $normalized = mb_strtolower($message);

        foreach (self::BLOCKED_TOPICS as $topic) {
            if (str_contains($normalized, $topic)) {
                return "Tópico não permitido: {$topic}";
            }
        }

        return null;
    }

    public function hasReachedDailyLimit(Student $student): bool
    {
        $limit = $this->dailyLimitFor($student);

        $todayCount = TutorMessage::where('student_id', $student->id)
            ->where('role', 'student')
            ->whereDate('created_at', today())
            ->where('blocked', false)
            ->count();

        return $todayCount >= $limit;
    }

    public function dailyLimitFor(Student $student): int
    {
        $age = $student->birth_date?->age ?? 18;

        return match (true) {
            $age <= 12 => self::DAILY_LIMITS['child'],
            $age <= 17 => self::DAILY_LIMITS['teen'],
            default => self::DAILY_LIMITS['adult'],
        };
    }

    public function remainingMessages(Student $student): int
    {
        $limit = $this->dailyLimitFor($student);

        $used = TutorMessage::where('student_id', $student->id)
            ->where('role', 'student')
            ->whereDate('created_at', today())
            ->where('blocked', false)
            ->count();

        return max(0, $limit - $used);
    }
}
