<?php

namespace App\Console\Commands;

use App\Domain\AI\Services\QuestionReviewService;
use App\Domain\Gameplay\Models\Question;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class ReviewQuestionsCommand extends Command
{
    protected $signature = 'ai:questions:review
        {--grade= : Código da série (ex: EF05)}
        {--subject= : Slug da matéria (ex: matematica)}
        {--model=google/gemini-2.5-pro : Modelo da IA revisora}
        {--limit=0 : Limite de questões revisadas}';

    protected $description = 'Revisa questões geradas por IA e marca aprovadas como reviewed.';

    public function handle(QuestionReviewService $reviewService): int
    {
        $model = trim((string) $this->option('model'));
        $limit = max(0, (int) $this->option('limit'));

        $query = Question::query()
            ->with(['skill:id,grade_id,subject_id'])
            ->where('ai_generated', true)
            ->where('status', 'draft')
            ->orderBy('id');

        if ($grade = $this->option('grade')) {
            $gradeCode = strtoupper((string) $grade);
            $query->whereHas('skill.grade', fn (Builder $gradeQuery) => $gradeQuery->where('code', $gradeCode));
        }

        if ($subject = $this->option('subject')) {
            $subjectSlug = strtolower((string) $subject);
            $query->whereHas('skill.subject', fn (Builder $subjectQuery) => $subjectQuery->where('slug', $subjectSlug));
        }

        if ($limit > 0) {
            $query->limit($limit);
        }

        $questions = $query->get();

        if ($questions->isEmpty()) {
            $this->warn('Nenhuma questão elegível para revisão.');

            return self::SUCCESS;
        }

        $approvedCount = 0;
        $rejectedCount = 0;
        $failedCount = 0;

        foreach ($questions as $question) {
            try {
                $recentPrompts = Question::query()
                    ->where('skill_id', $question->skill_id)
                    ->where('id', '!=', $question->id)
                    ->where('ai_generated', true)
                    ->latest('id')
                    ->limit(10)
                    ->pluck('prompt')
                    ->all();

                $decision = $reviewService->reviewQuestion($question, $model, $recentPrompts);

                if ($decision['approved']) {
                    $payload = ['status' => 'reviewed'];

                    if ($decision['suggested_correct_answer']) {
                        $payload['correct_answer'] = $decision['suggested_correct_answer'];
                    }

                    $question->update($payload);
                    $approvedCount++;
                } else {
                    $rejectedCount++;
                }
            } catch (\Throwable $exception) {
                $failedCount++;
                $this->warn("Falha na revisão da questão #{$question->id}: {$exception->getMessage()}");
            }
        }

        $this->info("Revisão concluída. Aprovadas: {$approvedCount}. Reprovadas: {$rejectedCount}. Falhas: {$failedCount}.");

        return self::SUCCESS;
    }
}
