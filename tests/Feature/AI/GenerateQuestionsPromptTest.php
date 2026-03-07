<?php

use App\Domain\AI\Prompts\GenerateQuestionsPrompt;
use App\Domain\Content\Models\BnccSkill;
use App\Domain\Content\Models\Grade;
use App\Domain\Content\Models\Subject;

it('uses easier and shorter generation rules for 3rd to 5th grade fundamental', function () {
    $grade = Grade::factory()->create([
        'name' => '3º Ano EF',
        'code' => 'EF03',
        'stage' => 'fundamental_1',
        'order' => 1,
    ]);
    $subject = Subject::factory()->create(['name' => 'História', 'slug' => 'historia']);
    $skill = BnccSkill::factory()->create([
        'grade_id' => $grade->id,
        'subject_id' => $subject->id,
        'code' => 'EF03HI01',
        'description' => 'Identificar registros da história da comunidade.',
    ]);

    $prompt = GenerateQuestionsPrompt::build($skill, 10);

    expect($prompt)->toContain('Faixa etária: 8–10 anos');
    expect($prompt)->toContain('Dificuldade variada: 70% fácil (1-2), 25% médio (3), 5% desafiador (4)');
    expect($prompt)->toContain('Enunciado curto: até 12 palavras');
    expect($prompt)->toContain('Alternativas curtas: até 5 palavras');
});

it('keeps regular generation rules for 6th to 9th grade fundamental and above', function () {
    $grade = Grade::factory()->create([
        'name' => '6º Ano EF',
        'code' => 'EF06',
        'stage' => 'fundamental_2',
        'order' => 4,
    ]);
    $subject = Subject::factory()->create(['name' => 'História', 'slug' => 'historia']);
    $skill = BnccSkill::factory()->create([
        'grade_id' => $grade->id,
        'subject_id' => $subject->id,
        'code' => 'EF06HI01',
        'description' => 'Analisar diferentes fontes históricas.',
    ]);

    $prompt = GenerateQuestionsPrompt::build($skill, 10);

    expect($prompt)->toContain('Faixa etária: 11–14 anos');
    expect($prompt)->toContain('Dificuldade variada: 30% fácil (1-2), 50% médio (3), 20% difícil (4-5)');
    expect($prompt)->not->toContain('Enunciado curto: até 12 palavras');
    expect($prompt)->toContain('Equilíbrio entre raciocínio, interpretação e aplicação prática');
});
