<?php

use App\Console\Commands\PublishQuestionsCommand;
use App\Console\Commands\ReviewQuestionsCommand;
use App\Domain\AI\Services\QuestionReviewService;
use App\Domain\Content\Models\BnccSkill;
use App\Domain\Content\Models\Grade;
use App\Domain\Content\Models\Subject;
use App\Domain\Gameplay\Models\Question;

it('reviews ai questions and marks approved ones as reviewed', function () {
    $grade = Grade::factory()->create([
        'code' => 'EF05',
        'name' => '5º Ano EF',
        'stage' => 'fundamental_1',
        'order' => 3,
    ]);
    $subject = Subject::factory()->create(['slug' => 'matematica']);
    $skill = BnccSkill::factory()->create([
        'grade_id' => $grade->id,
        'subject_id' => $subject->id,
        'active' => true,
    ]);

    $question = Question::query()->create([
        'skill_id' => $skill->id,
        'type' => 'multiple_choice',
        'difficulty' => 3,
        'prompt' => 'Qual é 2 + 2?',
        'options' => ['A' => '3', 'B' => '4', 'C' => '5', 'D' => '6'],
        'correct_answer' => 'B',
        'explanation' => 'Soma básica.',
        'status' => 'draft',
        'ai_generated' => true,
    ]);

    $mock = \Mockery::mock(QuestionReviewService::class);
    $mock->shouldReceive('reviewQuestion')
        ->once()
        ->andReturn([
            'approved' => true,
            'reason' => 'Questão adequada.',
            'suggested_correct_answer' => 'C',
        ]);
    app()->instance(QuestionReviewService::class, $mock);

    $this->artisan(ReviewQuestionsCommand::class, [
        '--grade' => 'EF05',
        '--subject' => 'matematica',
        '--model' => 'google/gemini-2.5-pro',
    ])->assertSuccessful();

    $question->refresh();

    expect($question->status)->toBe('reviewed');
    expect($question->correct_answer)->toBe('C');
});

it('publishes reviewed ai questions', function () {
    $grade = Grade::factory()->create(['code' => 'EF04']);
    $subject = Subject::factory()->create(['slug' => 'historia']);
    $skill = BnccSkill::factory()->create([
        'grade_id' => $grade->id,
        'subject_id' => $subject->id,
        'active' => true,
    ]);

    $reviewed = Question::query()->create([
        'skill_id' => $skill->id,
        'type' => 'multiple_choice',
        'difficulty' => 2,
        'prompt' => 'Questão revisada',
        'options' => ['A' => 'A', 'B' => 'B', 'C' => 'C', 'D' => 'D'],
        'correct_answer' => 'A',
        'explanation' => 'Ok',
        'status' => 'reviewed',
        'ai_generated' => true,
    ]);

    Question::query()->create([
        'skill_id' => $skill->id,
        'type' => 'multiple_choice',
        'difficulty' => 2,
        'prompt' => 'Questão rascunho',
        'options' => ['A' => 'A', 'B' => 'B', 'C' => 'C', 'D' => 'D'],
        'correct_answer' => 'A',
        'explanation' => 'Ok',
        'status' => 'draft',
        'ai_generated' => true,
    ]);

    $this->artisan(PublishQuestionsCommand::class, [
        '--grade' => 'EF04',
        '--subject' => 'historia',
    ])->assertSuccessful();

    $reviewed->refresh();

    expect($reviewed->status)->toBe('published');
    expect(Question::query()->where('status', 'draft')->count())->toBe(1);
});
