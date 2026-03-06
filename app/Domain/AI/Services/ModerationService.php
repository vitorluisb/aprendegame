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

    private const DAILY_LIMIT = 15;

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
        return self::DAILY_LIMIT;
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
