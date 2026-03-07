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
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class LessonService
{
    private const NODE_CLEAR_BONUS_GEMS = 3;

    public function __construct(
        private readonly MasteryService $masteryService,
        private readonly StreakService $streakService,
        private readonly LeagueService $leagueService,
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
            $attempts = $run->attempts()->with('question:id,skill_id')->get();
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
            $nodeSkillIds = collect($run->lesson?->node?->skill_ids ?? [])
                ->filter(fn (mixed $skillId): bool => is_numeric($skillId))
                ->map(fn (mixed $skillId): int => (int) $skillId)
                ->values();
            $masterySignalsBySkill = $this->buildMasterySignalsBySkill($attempts);
            $skillIds = $nodeSkillIds
                ->merge(array_keys($masterySignalsBySkill))
                ->unique()
                ->values();

            if ($student) {
                if ($xp > 0) {
                    $this->leagueService->addXP($student, $xp);
                }

                foreach ($skillIds as $skillId) {
                    $signal = $masterySignalsBySkill[(int) $skillId] ?? null;
                    $isCorrectForSkill = is_array($signal)
                        ? ((int) $signal['correct'] / max(1, (int) $signal['total'])) >= 0.6
                        : $score >= 70;

                    $this->masteryService->update($student, (int) $skillId, correct: $isCorrectForSkill);
                }

                $this->streakService->update($student);
            }

            return tap($run->refresh(), function (LessonRun $finishedRun) use ($neuronsEarned) {
                $finishedRun->setAttribute('neurons_earned', $neuronsEarned);
            });
        });
    }

    /**
     * @param  Collection<int, Attempt>  $attempts
     * @return array<int, array{correct:int,total:int}>
     */
    private function buildMasterySignalsBySkill(Collection $attempts): array
    {
        $signals = [];

        foreach ($attempts as $attempt) {
            $skillId = (int) ($attempt->question?->skill_id ?? 0);

            if ($skillId <= 0) {
                continue;
            }

            $signals[$skillId] ??= ['correct' => 0, 'total' => 0];
            $signals[$skillId]['total']++;

            if ((bool) $attempt->correct) {
                $signals[$skillId]['correct']++;
            }
        }

        return $signals;
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
            'multiple_choice', 'true_false' => $this->checkChoiceAnswer($question, $answer),
            'fill_blank' => strtolower(trim($answer)) === strtolower(trim($question->correct_answer)),
            'drag_drop' => $this->checkDragDrop($question, $answer),
            'order_steps' => $this->checkOrderSteps($question, $answer),
            default => false,
        };
    }

    private function checkChoiceAnswer(Question $question, string $answer): bool
    {
        $optionEntries = $this->normalizeOptionEntries($question->options);
        $givenKey = $this->resolveAnswerKey($answer, $optionEntries, $question->type === 'true_false');
        $correctKey = $this->resolveAnswerKey((string) $question->correct_answer, $optionEntries, $question->type === 'true_false');

        if ($givenKey !== null && $correctKey !== null) {
            return $givenKey === $correctKey;
        }

        return strtolower(trim($answer)) === strtolower(trim((string) $question->correct_answer));
    }

    /**
     * @return array<int, array{key: string, text: string}>
     */
    private function normalizeOptionEntries(mixed $rawOptions): array
    {
        if (! is_array($rawOptions)) {
            return [];
        }

        $entries = [];
        $isAssoc = ! array_is_list($rawOptions);

        if ($isAssoc) {
            foreach ($rawOptions as $key => $text) {
                $entries[] = [
                    'key' => strtoupper(trim((string) $key)),
                    'text' => trim((string) $text),
                ];
            }

            return $entries;
        }

        foreach ($rawOptions as $index => $option) {
            if (is_array($option)) {
                $key = strtoupper(trim((string) ($option['key'] ?? '')));
                $text = trim((string) ($option['text'] ?? ''));
            } else {
                $key = chr(65 + (int) $index);
                $text = trim((string) $option);
            }

            if ($key === '') {
                $key = chr(65 + (int) $index);
            }

            $entries[] = ['key' => $key, 'text' => $text];
        }

        return $entries;
    }

    /**
     * @param  array<int, array{key: string, text: string}>  $optionEntries
     */
    private function resolveAnswerKey(string $value, array $optionEntries, bool $isTrueFalse): ?string
    {
        $normalizedValue = strtolower(trim($value));

        if ($normalizedValue === '') {
            return null;
        }

        foreach ($optionEntries as $entry) {
            if (strtolower($entry['key']) === $normalizedValue) {
                return $entry['key'];
            }
        }

        foreach ($optionEntries as $entry) {
            if (strtolower(trim($entry['text'])) === $normalizedValue) {
                return $entry['key'];
            }
        }

        if ($isTrueFalse) {
            if (in_array($normalizedValue, ['verdadeiro', 'true', 'v'], true)) {
                return 'TRUE';
            }

            if (in_array($normalizedValue, ['falso', 'false', 'f'], true)) {
                return 'FALSE';
            }
        }

        return null;
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
            $score >= 90 => 15,
            $score >= 70 => 10,
            $score >= 50 => 6,
            default => 2,
        };

        $speedBonus = now()->diffInMinutes($startedAt) < 4 ? 1 : 0;

        return $base + $speedBonus;
    }
}
