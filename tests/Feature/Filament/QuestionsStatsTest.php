<?php

use App\Domain\Content\Models\BnccSkill;
use App\Domain\Content\Models\Grade;
use App\Domain\Content\Models\Subject;
use App\Domain\Gameplay\Models\Question;
use App\Filament\Widgets\QuestionsStats;

it('builds dashboard stats for total, today and grouped by grade and subject', function () {
    $gradeEf03 = Grade::factory()->create([
        'name' => '3º Ano EF',
        'code' => 'EF03',
        'stage' => 'fundamental_1',
        'order' => 1,
    ]);
    $gradeEf04 = Grade::factory()->create([
        'name' => '4º Ano EF',
        'code' => 'EF04',
        'stage' => 'fundamental_1',
        'order' => 2,
    ]);

    $history = Subject::factory()->create(['name' => 'História', 'slug' => 'historia']);
    $math = Subject::factory()->create(['name' => 'Matemática', 'slug' => 'matematica']);

    $skillHistoryEf03 = BnccSkill::factory()->create([
        'grade_id' => $gradeEf03->id,
        'subject_id' => $history->id,
    ]);
    $skillMathEf03 = BnccSkill::factory()->create([
        'grade_id' => $gradeEf03->id,
        'subject_id' => $math->id,
    ]);
    $skillHistoryEf04 = BnccSkill::factory()->create([
        'grade_id' => $gradeEf04->id,
        'subject_id' => $history->id,
    ]);

    Question::query()->create([
        'skill_id' => $skillHistoryEf03->id,
        'type' => 'multiple_choice',
        'difficulty' => 2,
        'prompt' => 'Pergunta 1?',
        'options' => ['A' => 'a', 'B' => 'b', 'C' => 'c', 'D' => 'd'],
        'correct_answer' => 'A',
        'explanation' => 'Explicação',
        'status' => 'draft',
        'ai_generated' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Question::query()->create([
        'skill_id' => $skillHistoryEf03->id,
        'type' => 'multiple_choice',
        'difficulty' => 2,
        'prompt' => 'Pergunta 2?',
        'options' => ['A' => 'a', 'B' => 'b', 'C' => 'c', 'D' => 'd'],
        'correct_answer' => 'B',
        'explanation' => 'Explicação',
        'status' => 'draft',
        'ai_generated' => true,
        'created_at' => today()->subDays(2)->startOfDay(),
        'updated_at' => today()->subDays(2)->startOfDay(),
    ]);

    Question::query()->create([
        'skill_id' => $skillMathEf03->id,
        'type' => 'multiple_choice',
        'difficulty' => 2,
        'prompt' => 'Pergunta 3?',
        'options' => ['A' => 'a', 'B' => 'b', 'C' => 'c', 'D' => 'd'],
        'correct_answer' => 'C',
        'explanation' => 'Explicação',
        'status' => 'draft',
        'ai_generated' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Question::query()->create([
        'skill_id' => $skillHistoryEf04->id,
        'type' => 'multiple_choice',
        'difficulty' => 2,
        'prompt' => 'Pergunta 4?',
        'options' => ['A' => 'a', 'B' => 'b', 'C' => 'c', 'D' => 'd'],
        'correct_answer' => 'D',
        'explanation' => 'Explicação',
        'status' => 'draft',
        'ai_generated' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $widget = new class extends QuestionsStats
    {
        public function stats(): array
        {
            return $this->getStats();
        }
    };

    $statsMap = collect($widget->stats())
        ->mapWithKeys(fn ($stat): array => [(string) $stat->getLabel() => (string) $stat->getValue()]);

    expect($statsMap->get('Total de Questões'))->toBe('4');
    expect($statsMap->get('Geradas Hoje'))->toBe((string) Question::query()->whereDate('created_at', today())->count());
    expect($statsMap->get('História EF03'))->toBe('2');
    expect($statsMap->get('Matemática EF03'))->toBe('1');
    expect($statsMap->get('História EF04'))->toBe('1');
});
