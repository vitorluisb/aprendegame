<?php

namespace App\Console\Commands;

use App\Domain\AI\Jobs\GenerateQuestionsForSkill;
use App\Domain\AI\Models\AiJob;
use App\Domain\Content\Models\BnccSkill;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class GenerateQuestionsBulkCommand extends Command
{
    protected $signature = 'ai:questions:bulk
        {--skill=* : IDs ou códigos BNCC específicos}
        {--subject= : Slug da matéria (ex: matematica)}
        {--grade= : Código da série (ex: EF06)}
        {--count=10 : Quantidade por habilidade (1-50)}
        {--model=claude-sonnet-4-6 : Modelo de IA}
        {--limit=0 : Limite de habilidades processadas}';

    protected $description = 'Despacha geração de questões em massa por habilidade BNCC com rastreio por lote.';

    public function handle(): int
    {
        $count = (int) $this->option('count');
        $count = max(1, min(50, $count));
        $model = (string) $this->option('model');
        $limit = max(0, (int) $this->option('limit'));
        $skills = $this->option('skill');
        $batchUuid = (string) Str::uuid();

        $query = BnccSkill::query()
            ->with(['grade:id,code,name', 'subject:id,slug,name'])
            ->where('active', true)
            ->orderBy('id');

        if (! empty($skills)) {
            $skillIds = collect($skills)
                ->filter(fn (string $skill): bool => is_numeric($skill))
                ->map(fn (string $skill): int => (int) $skill)
                ->values();

            $skillCodes = collect($skills)
                ->filter(fn (string $skill): bool => ! is_numeric($skill))
                ->map(fn (string $skill): string => strtoupper(trim($skill)))
                ->values();

            $query->where(function (Builder $skillQuery) use ($skillIds, $skillCodes): void {
                if ($skillIds->isNotEmpty()) {
                    $skillQuery->orWhereIn('id', $skillIds);
                }

                if ($skillCodes->isNotEmpty()) {
                    $skillQuery->orWhereIn('code', $skillCodes);
                }
            });
        }

        if ($grade = $this->option('grade')) {
            $gradeCode = strtoupper((string) $grade);
            $query->whereHas('grade', fn (Builder $gradeQuery) => $gradeQuery->where('code', $gradeCode));
        }

        if ($subject = $this->option('subject')) {
            $subjectSlug = strtolower((string) $subject);
            $query->whereHas('subject', fn (Builder $subjectQuery) => $subjectQuery->where('slug', $subjectSlug));
        }

        if ($limit > 0) {
            $query->limit($limit);
        }

        $totalSkills = (clone $query)->count();

        if ($totalSkills === 0) {
            $this->warn('Nenhuma habilidade BNCC encontrada com os filtros informados.');

            return self::FAILURE;
        }

        $this->info("Iniciando lote {$batchUuid} para {$totalSkills} habilidades ({$count} questões/habilidade)...");

        $dispatched = 0;

        $query->chunkById(200, function ($chunk) use (&$dispatched, $count, $model, $batchUuid): void {
            foreach ($chunk as $skill) {
                $aiJob = AiJob::query()->create([
                    'batch_uuid' => $batchUuid,
                    'type' => 'generate_questions',
                    'skill_id' => $skill->id,
                    'status' => 'pending',
                    'model' => $model,
                    'requested_count' => $count,
                    'config' => [
                        'skill_code' => $skill->code,
                        'grade' => $skill->grade?->code,
                        'subject' => $skill->subject?->slug,
                    ],
                ]);

                GenerateQuestionsForSkill::dispatch($skill->id, $count, $model, $aiJob->id);
                $dispatched++;
            }
        });

        $this->info("Lote {$batchUuid} despachado com sucesso. Jobs criados: {$dispatched}.");

        return self::SUCCESS;
    }
}
