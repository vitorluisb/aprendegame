<?php

namespace App\Domain\Gameplay\Services;

use App\Domain\Accounts\Models\Student;
use App\Domain\Content\Models\PathNode;
use App\Domain\Gameplay\Models\Attempt;
use App\Domain\Gameplay\Models\GemTransaction;
use App\Domain\Gameplay\Models\Lesson;
use App\Domain\Gameplay\Models\LessonRun;
use App\Domain\Gameplay\Models\Question;
use App\Domain\Gameplay\Models\XpTransaction;
use App\Jobs\UpdateQuestionMetrics;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class LessonService
{
    private const NODE_CLEAR_BONUS_GEMS = 8;

    public function __construct(
        private readonly MasteryService $masteryService,
        private readonly StreakService $streakService,
    ) {}

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
        $student = Student::withoutGlobalScopes()->find($run->student_id);
        if (! $student) {
            throw new RuntimeException('Aluno não encontrado para esta sessão.');
        }

        $student->refillLivesIfDue();
        $student = $student->fresh();

        if (! $student->hasLives()) {
            throw new RuntimeException('Você está sem vidas. Compre vidas na loja para continuar.');
        }

        $correct = $this->checkAnswer($question, $answer);

        $attempt = Attempt::create([
            'student_id' => $run->student_id,
            'question_id' => $question->id,
            'run_id' => $run->id,
            'correct' => $correct,
            'time_ms' => $timeMs,
            'given_answer' => $answer,
        ]);

        if (! $correct) {
            $student->loseLife();
        }

        UpdateQuestionMetrics::dispatch($question->id);

        return $attempt;
    }

    public function finish(LessonRun $run): LessonRun
    {
        return DB::transaction(function () use ($run) {
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

            XpTransaction::create([
                'student_id' => $run->student_id,
                'amount' => $xp,
                'reason' => 'lesson',
                'reference_type' => 'LessonRun',
                'reference_id' => $run->id,
                'created_at' => now(),
            ]);

            $neuronsEarned = $this->awardNodeClearBonusIfEligible($run, $score);

            $student = Student::withoutGlobalScopes()->find($run->student_id);
            $skillIds = $run->lesson?->node?->skill_ids ?? [];

            if ($student) {
                foreach ($skillIds as $skillId) {
                    $this->masteryService->update($student, (int) $skillId, correct: true);
                }

                $this->streakService->update($student);
            }

            return tap($run->refresh(), function (LessonRun $finishedRun) use ($neuronsEarned) {
                $finishedRun->setAttribute('neurons_earned', $neuronsEarned);
            });
        });
    }

    private function awardNodeClearBonusIfEligible(LessonRun $run, int $score): int
    {
        if ($score < 70) {
            return 0;
        }

        $node = $run->lesson?->node;

        if (! $node instanceof PathNode) {
            return 0;
        }

        $alreadyAwarded = GemTransaction::query()
            ->where('student_id', $run->student_id)
            ->where('source', 'node_clear_bonus')
            ->where('sourceable_type', PathNode::class)
            ->where('sourceable_id', $node->id)
            ->exists();

        if ($alreadyAwarded) {
            return 0;
        }

        $lessonIds = Lesson::query()
            ->where('node_id', $node->id)
            ->pluck('id');

        if ($lessonIds->isEmpty()) {
            return 0;
        }

        $alreadyCompletedNode = LessonRun::query()
            ->where('student_id', $run->student_id)
            ->whereIn('lesson_id', $lessonIds)
            ->where('score', '>=', 70)
            ->where('id', '!=', $run->id)
            ->exists();

        if ($alreadyCompletedNode) {
            return 0;
        }

        GemTransaction::create([
            'student_id' => $run->student_id,
            'amount' => self::NODE_CLEAR_BONUS_GEMS,
            'source' => 'node_clear_bonus',
            'sourceable_type' => PathNode::class,
            'sourceable_id' => $node->id,
            'created_at' => now(),
        ]);

        return self::NODE_CLEAR_BONUS_GEMS;
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
