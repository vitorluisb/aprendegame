<?php

namespace App\Domain\QuizMestre\Services;

use App\Domain\Accounts\Models\Student;
use App\Domain\Gameplay\Models\GemTransaction;
use App\Domain\Gameplay\Models\XpTransaction;
use App\Domain\Gameplay\Services\LeagueService;
use App\Domain\QuizMestre\Models\GkQuestion;
use App\Domain\QuizMestre\Models\GkSession;
use App\Domain\QuizMestre\Models\GkSessionAnswer;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class QuizMestreService
{
    public const MAX_ROUNDS = 30;

    public function startGameSession(User $user): GkSession
    {
        return GkSession::query()->create([
            'user_id' => $user->id,
            'score' => 0,
            'current_round' => 1,
            'correct_answers' => 0,
            'started_at' => now(),
            'status' => 'active',
            'reward_xp' => 0,
            'reward_gems' => 0,
        ]);
    }

    /**
     * @return array{id:int,question_text:string,round:int,difficulty:string,category:string,options:array<int,array{key:string,text:string}>}|null
     */
    public function loadNextQuestion(GkSession $session): ?array
    {
        if ($session->status !== 'active' || $session->current_round > self::MAX_ROUNDS) {
            return null;
        }

        $difficulty = $this->difficultyForRound($session->current_round);

        $recentQuestionIds = GkSessionAnswer::query()
            ->join('gk_sessions', 'gk_sessions.id', '=', 'gk_session_answers.session_id')
            ->where('gk_sessions.user_id', $session->user_id)
            ->where('gk_session_answers.created_at', '>=', now()->subDays(7))
            ->orderByDesc('gk_session_answers.id')
            ->limit(60)
            ->pluck('gk_session_answers.question_id');

        $alreadyInSession = $session->answers()->pluck('question_id');

        $question = GkQuestion::query()
            ->with('category:id,name')
            ->where('is_active', true)
            ->where('difficulty', $difficulty)
            ->whereNotIn('id', $recentQuestionIds)
            ->whereNotIn('id', $alreadyInSession)
            ->inRandomOrder()
            ->first();

        if (! $question) {
            $question = GkQuestion::query()
                ->with('category:id,name')
                ->where('is_active', true)
                ->whereNotIn('id', $recentQuestionIds)
                ->whereNotIn('id', $alreadyInSession)
                ->inRandomOrder()
                ->first();
        }

        if (! $question) {
            $question = GkQuestion::query()
                ->with('category:id,name')
                ->where('is_active', true)
                ->where('difficulty', $difficulty)
                ->whereNotIn('id', $alreadyInSession)
                ->inRandomOrder()
                ->first();
        }

        if (! $question) {
            $question = GkQuestion::query()
                ->with('category:id,name')
                ->where('is_active', true)
                ->whereNotIn('id', $alreadyInSession)
                ->inRandomOrder()
                ->first();
        }

        if (! $question) {
            return null;
        }

        $options = collect($question->optionsMap())
            ->map(fn (string $text, string $key): array => ['key' => $key, 'text' => $text])
            ->shuffle()
            ->values()
            ->all();

        return [
            'id' => $question->id,
            'question_text' => $question->question_text,
            'round' => $session->current_round,
            'difficulty' => $difficulty,
            'category' => (string) ($question->category?->name ?? 'Geral'),
            'options' => $options,
        ];
    }

    /**
     * @return array{is_correct:bool,correct_option:string,correct_text:string,explanation:string|null,status:string,score:int,current_round:int,reward_xp:int,reward_gems:int}
     */
    public function submitAnswer(GkSession $session, int $questionId, string $selectedOption, int $responseTimeMs = 0): array
    {
        if ($session->status !== 'active') {
            throw new RuntimeException('Sessão finalizada.');
        }

        /** @var GkQuestion $question */
        $question = GkQuestion::query()->findOrFail($questionId);
        $selectedOption = strtoupper(trim($selectedOption));

        if (! in_array($selectedOption, ['A', 'B', 'C', 'D'], true)) {
            throw new RuntimeException('Alternativa inválida.');
        }

        $isCorrect = $selectedOption === $question->correct_option;

        GkSessionAnswer::query()->create([
            'session_id' => $session->id,
            'question_id' => $question->id,
            'selected_option' => $selectedOption,
            'is_correct' => $isCorrect,
            'response_time_ms' => max(0, $responseTimeMs),
        ]);

        if (! $isCorrect) {
            $session->update([
                'status' => 'lost',
                'finished_at' => now(),
            ]);

            $this->finishGameSession($session->refresh());

            return [
                'is_correct' => false,
                'correct_option' => $question->correct_option,
                'correct_text' => (string) Arr::get($question->optionsMap(), $question->correct_option),
                'explanation' => $question->explanation,
                'status' => 'lost',
                'score' => $session->score,
                'current_round' => $session->current_round,
                'reward_xp' => $session->reward_xp,
                'reward_gems' => $session->reward_gems,
            ];
        }

        $newCorrectAnswers = $session->correct_answers + 1;
        $newScore = $session->score + $this->scoreForRound($session->current_round);
        $newRewardXp = $session->reward_xp + $this->xpForRound($session->current_round) + $this->streakBonusXp($newCorrectAnswers);
        $newRewardGems = $session->reward_gems;

        $newRound = $session->current_round + 1;
        $newStatus = $newRound > self::MAX_ROUNDS ? 'completed' : 'active';

        if ($newStatus === 'completed') {
            $newRewardGems += 8;
        }

        $session->update([
            'score' => $newScore,
            'current_round' => min($newRound, self::MAX_ROUNDS),
            'correct_answers' => $newCorrectAnswers,
            'reward_xp' => $newRewardXp,
            'reward_gems' => $newRewardGems,
            'status' => $newStatus,
            'finished_at' => $newStatus === 'completed' ? now() : null,
        ]);

        if ($newStatus === 'completed') {
            $this->finishGameSession($session->refresh());
        }

        $fresh = $session->refresh();

        return [
            'is_correct' => true,
            'correct_option' => $question->correct_option,
            'correct_text' => (string) Arr::get($question->optionsMap(), $question->correct_option),
            'explanation' => $question->explanation,
            'status' => $fresh->status,
            'score' => $fresh->score,
            'current_round' => $fresh->current_round,
            'reward_xp' => $fresh->reward_xp,
            'reward_gems' => $fresh->reward_gems,
        ];
    }

    public function finishGameSession(GkSession $session): GkSession
    {
        if ($session->finished_at === null) {
            $session->update(['finished_at' => now()]);
            $session->refresh();
        }

        if ($session->reward_xp <= 0 && $session->reward_gems <= 0) {
            return $session;
        }

        $alreadyRewarded = XpTransaction::query()
            ->where('reference_type', GkSession::class)
            ->where('reference_id', $session->id)
            ->exists();

        if ($alreadyRewarded) {
            return $session;
        }

        DB::transaction(function () use ($session): void {
            /** @var User $user */
            $user = $session->user()->firstOrFail();
            /** @var Student $student */
            $student = $user->ensureStudentProfile();

            if ($session->reward_xp > 0) {
                XpTransaction::query()->create([
                    'student_id' => $student->id,
                    'amount' => $session->reward_xp,
                    'reason' => 'quiz_mestre',
                    'reference_type' => GkSession::class,
                    'reference_id' => $session->id,
                    'created_at' => now(),
                ]);

                app(LeagueService::class)->addXP($student, $session->reward_xp);
            }

            if ($session->reward_gems > 0) {
                GemTransaction::query()->create([
                    'student_id' => $student->id,
                    'amount' => $session->reward_gems,
                    'source' => 'quiz_mestre',
                    'sourceable_type' => GkSession::class,
                    'sourceable_id' => $session->id,
                    'created_at' => now(),
                ]);
            }
        });

        return $session;
    }

    public function calculateScore(GkSession $session): int
    {
        return $session->score;
    }

    public function difficultyForRound(int $round): string
    {
        return match (true) {
            $round <= 15 => 'easy',
            $round <= 25 => 'medium',
            default => 'hard',
        };
    }

    private function scoreForRound(int $round): int
    {
        return match ($this->difficultyForRound($round)) {
            'easy' => 100,
            'medium' => 150,
            'hard' => 220,
        };
    }

    private function xpForRound(int $round): int
    {
        return match ($this->difficultyForRound($round)) {
            'easy' => 6,
            'medium' => 10,
            'hard' => 14,
        };
    }

    private function streakBonusXp(int $correctAnswers): int
    {
        return $correctAnswers > 0 && $correctAnswers % 3 === 0 ? 3 : 0;
    }
}
