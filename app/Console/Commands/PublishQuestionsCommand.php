<?php

namespace App\Console\Commands;

use App\Domain\Gameplay\Models\Question;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class PublishQuestionsCommand extends Command
{
    protected $signature = 'ai:questions:publish
        {--grade= : Código da série (ex: EF05)}
        {--subject= : Slug da matéria (ex: matematica)}
        {--limit=0 : Limite de questões publicadas}';

    protected $description = 'Publica questões reviewed geradas por IA.';

    public function handle(): int
    {
        $limit = max(0, (int) $this->option('limit'));

        $query = Question::query()
            ->where('ai_generated', true)
            ->where('status', 'reviewed')
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

        $questionIds = $query->pluck('id');

        if ($questionIds->isEmpty()) {
            $this->warn('Nenhuma questão reviewed para publicar.');

            return self::SUCCESS;
        }

        $publishedCount = Question::query()
            ->whereIn('id', $questionIds)
            ->update(['status' => 'published']);

        $this->info("Publicação concluída. Questões publicadas: {$publishedCount}.");

        return self::SUCCESS;
    }
}
