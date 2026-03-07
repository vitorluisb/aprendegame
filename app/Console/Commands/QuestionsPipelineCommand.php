<?php

namespace App\Console\Commands;

use App\Domain\AI\Models\AiJob;
use App\Domain\AI\Services\AIService;
use Illuminate\Console\Command;

class QuestionsPipelineCommand extends Command
{
    protected $signature = 'ai:questions:pipeline
        {--grades=EF03,EF04,EF05 : Códigos de séries separados por vírgula}
        {--subject= : Slug da matéria (ex: matematica)}
        {--count=30 : Quantidade por habilidade}
        {--generate-model='.AIService::DEFAULT_MODEL.' : Modelo para geração}
        {--review-model=google/gemini-2.5-pro : Modelo para revisão}
        {--limit=0 : Limite de habilidades por série na geração}
        {--wait=1200 : Tempo máximo de espera por série (segundos)}';

    protected $description = 'Executa pipeline manual de geração, revisão e publicação de questões por série.';

    public function handle(): int
    {
        $grades = collect(explode(',', (string) $this->option('grades')))
            ->map(fn (string $grade): string => strtoupper(trim($grade)))
            ->filter(fn (string $grade): bool => $grade !== '')
            ->values();

        if ($grades->isEmpty()) {
            $this->error('Nenhuma série válida informada em --grades.');

            return self::FAILURE;
        }

        foreach ($grades as $grade) {
            $this->info("Pipeline da série {$grade} iniciado.");
            $startTime = now();

            $bulkExitCode = $this->call('ai:questions:bulk', array_filter([
                '--grade' => $grade,
                '--subject' => $this->option('subject'),
                '--count' => (int) $this->option('count'),
                '--model' => (string) $this->option('generate-model'),
                '--limit' => (int) $this->option('limit'),
            ], fn ($value): bool => $value !== null && $value !== ''));

            if ($bulkExitCode !== self::SUCCESS) {
                $this->warn("Geração falhou para {$grade}. Próxima série.");

                continue;
            }

            if (! $this->waitForGeneration($grade, $startTime->toDateTimeString(), (int) $this->option('wait'))) {
                $this->warn("Timeout aguardando término da geração para {$grade}. Próxima série.");

                continue;
            }

            $this->call('ai:questions:review', array_filter([
                '--grade' => $grade,
                '--subject' => $this->option('subject'),
                '--model' => (string) $this->option('review-model'),
            ], fn ($value): bool => $value !== null && $value !== ''));

            $this->call('ai:questions:publish', array_filter([
                '--grade' => $grade,
                '--subject' => $this->option('subject'),
            ], fn ($value): bool => $value !== null && $value !== ''));
        }

        $this->info('Pipeline finalizado.');

        return self::SUCCESS;
    }

    private function waitForGeneration(string $grade, string $startedAt, int $maxWaitSeconds): bool
    {
        $timeoutAt = now()->addSeconds(max(5, $maxWaitSeconds));

        while (now()->lt($timeoutAt)) {
            $pendingCount = AiJob::query()
                ->where('type', 'generate_questions')
                ->where('config->grade', $grade)
                ->where('created_at', '>=', $startedAt)
                ->whereIn('status', ['pending', 'processing'])
                ->count();

            if ($pendingCount === 0) {
                return true;
            }

            sleep(5);
        }

        return false;
    }
}
