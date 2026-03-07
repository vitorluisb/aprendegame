<?php

namespace App\Console\Commands;

use App\Domain\AI\Services\AIService;
use App\Domain\Enem\Services\EnemQuestionGenerationService;
use Illuminate\Console\Command;

class GenerateEnemQuestionsCommand extends Command
{
    protected $signature = 'ai:enem:generate
        {--area= : linguagens|humanas|natureza|matematica}
        {--subject= : Nome da disciplina}
        {--count=10 : Quantidade de questões}
        {--difficulty=medium : easy|medium|hard}
        {--year= : Ano de referência}
        {--model='.AIService::DEFAULT_MODEL.' : Modelo de IA}';

    protected $description = 'Gera questões ENEM em tabela separada.';

    public function handle(EnemQuestionGenerationService $service): int
    {
        $area = strtolower((string) $this->option('area'));
        $subject = trim((string) $this->option('subject'));
        $difficulty = strtolower((string) $this->option('difficulty'));
        $count = (int) $this->option('count');
        $year = $this->option('year') ? (int) $this->option('year') : null;
        $model = (string) $this->option('model');

        if (! in_array($area, ['linguagens', 'humanas', 'natureza', 'matematica'], true) || $subject === '') {
            $this->error('Informe --area válido e --subject.');

            return self::FAILURE;
        }

        $questions = $service->generateAndStore($area, $subject, $count, $difficulty, $year, $model);

        $this->info(count($questions).' questões ENEM geradas com sucesso.');

        return self::SUCCESS;
    }
}
