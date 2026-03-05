<?php

namespace App\Domain\Gameplay\Services;

use App\Domain\Accounts\Models\Student;
use App\Domain\Gameplay\Models\Attempt;
use App\Domain\Gameplay\Models\Lesson;
use App\Domain\Gameplay\Models\LessonRun;
use App\Domain\Gameplay\Models\Question;
use App\Jobs\AwardXP;
use App\Jobs\UpdateMastery;
use App\Jobs\UpdateQuestionMetrics;
use App\Jobs\UpdateStreak;
use Carbon\Carbon;

class LessonService
{
    public function start(Student $student, Lesson $lesson): LessonRun
    {
        return LessonRun::create([
            'student_id' => $student->id,
            'lesson_id' => $lesson->id,
            'started_at' => now(),
        ]);
    }

    public function answer(LessonRun $run, Question $question, string $answer, int $timeMs): Attempt
    {
        $correct = $this->checkAnswer($question, $answer);

        $attempt = Attempt::create([
            'student_id' => $run->student_id,
            'question_id' => $question->id,
            'run_id' => $run->id,
            'correct' => $correct,
            'time_ms' => $timeMs,
            'given_answer' => $answer,
        ]);

        UpdateQuestionMetrics::dispatch($question->id);

        return $attempt;
    }

    public function finish(LessonRun $run): LessonRun
    {
        $attempts = $run->attempts;
        $correct = $attempts->where('correct', true)->count();
        $total = $attempts->count();
        $score = $total > 0 ? (int) round(($correct / $total) * 100) : 0;
        $xp = $this->calculateXP($score, $run->started_at);

        $run->update([
            'finished_at' => now(),
            'score' => $score,
            'correct_count' => $correct,
            'total_count' => $total,
            'xp_earned' => $xp,
        ]);

        $skillIds = $run->lesson?->node?->skill_ids ?? [];

        AwardXP::dispatch($run->student_id, $xp, 'lesson', $run->id);
        UpdateMastery::dispatch($run->student_id, $skillIds);
        UpdateStreak::dispatch($run->student_id);

        return $run->refresh();
    }

    private function checkAnswer(Question $question, string $answer): bool
    {
        return match ($question->type) {
            'multiple_choice', 'true_false' => $answer === $question->correct_answer,
            'fill_blank' => strtolower(trim($answer)) === strtolower(trim($question->correct_answer)),
            'drag_drop' => $this->checkDragDrop($question, $answer),
            'order_steps' => $this->checkOrderSteps($question, $answer),
            default => false,
        };
    }

    private function checkDragDrop(Question $question, string $answer): bool
    {
        $given = json_decode($answer, true);
        $correct = json_decode($question->correct_answer, true);

        return $given === $correct;
    }

    private function checkOrderSteps(Question $question, string $answer): bool
    {
        $given = json_decode($answer, true);
        $correct = json_decode($question->correct_answer, true);

        return $given === $correct;
    }

    private function calculateXP(int $score, Carbon $startedAt): int
    {
        $base = match (true) {
            $score >= 90 => 30,
            $score >= 70 => 20,
            $score >= 50 => 10,
            default => 5,
        };

        $speedBonus = now()->diffInMinutes($startedAt) < 5 ? 5 : 0;

        return $base + $speedBonus;
    }
}
