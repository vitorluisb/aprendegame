<?php

namespace App\Filament\Resources\AiJobs\Pages;

use App\Domain\AI\Jobs\GenerateQuestionsForSkill;
use App\Domain\AI\Services\AIService;
use App\Domain\Content\Models\BnccSkill;
use App\Filament\Resources\AiJobs\AiJobResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateAiJob extends CreateRecord
{
    protected static string $resource = AiJobResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $gradeId = (int) ($data['grade_id'] ?? 0);
        $subjectId = (int) ($data['subject_id'] ?? 0);
        $skillId = (int) ($data['skill_id'] ?? 0);

        $skillBelongsToSelection = BnccSkill::query()
            ->whereKey($skillId)
            ->where('grade_id', $gradeId)
            ->where('subject_id', $subjectId)
            ->exists();

        if (! $skillBelongsToSelection) {
            throw ValidationException::withMessages([
                'skill_id' => 'A habilidade selecionada não pertence à série e disciplina informadas.',
            ]);
        }

        unset($data['grade_id'], $data['subject_id']);
        $data['status'] = 'pending';
        $data['requested_count'] = max(1, min(50, (int) ($data['requested_count'] ?? 10)));
        $data['model'] = $this->normalizeModel((string) ($data['model'] ?? AIService::DEFAULT_MODEL));

        return $data;
    }

    protected function afterCreate(): void
    {
        if ($this->record->type !== 'generate_questions') {
            return;
        }

        if (! (bool) config('services.ai.enabled', true)) {
            $this->record->update([
                'status' => 'failed',
                'error' => 'IA desativada no ambiente (AI_ENABLED=false).',
                'finished_at' => now(),
            ]);

            return;
        }

        GenerateQuestionsForSkill::dispatch(
            (int) $this->record->skill_id,
            (int) $this->record->requested_count,
            (string) ($this->record->model ?: AIService::DEFAULT_MODEL),
            (int) $this->record->id,
        );
    }

    private function normalizeModel(string $model): string
    {
        $normalizedModel = trim($model);
        $normalizedAlias = strtolower($normalizedModel);

        return match ($normalizedAlias) {
            'google: gemini 3.1 flash lite preview',
            'gemini 3.1 flash lite preview' => AIService::DEFAULT_MODEL,
            default => $normalizedModel,
        };
    }
}
