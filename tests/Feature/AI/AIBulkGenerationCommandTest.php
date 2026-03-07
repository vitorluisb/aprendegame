<?php

use App\Console\Commands\GenerateQuestionsBulkCommand;
use App\Domain\AI\Jobs\GenerateQuestionsForSkill;
use App\Domain\AI\Models\AiJob;
use App\Domain\Content\Models\BnccSkill;
use App\Domain\Content\Models\Grade;
use App\Domain\Content\Models\Subject;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    config()->set('services.ai.enabled', true);
});

it('dispatches bulk generation jobs with batch tracking', function () {
    Queue::fake();

    $grade = Grade::factory()->create(['code' => 'EF06']);
    $subject = Subject::factory()->create(['slug' => 'matematica']);

    BnccSkill::factory()->count(2)->create([
        'grade_id' => $grade->id,
        'subject_id' => $subject->id,
        'active' => true,
    ]);

    $this->artisan(GenerateQuestionsBulkCommand::class, [
        '--grade' => 'EF06',
        '--subject' => 'matematica',
        '--count' => 12,
    ])->assertSuccessful();

    Queue::assertPushed(GenerateQuestionsForSkill::class, 2);

    expect(AiJob::query()->count())->toBe(2);
    expect(AiJob::query()->where('status', 'pending')->count())->toBe(2);
    expect(AiJob::query()->where('requested_count', 12)->count())->toBe(2);
    expect(AiJob::query()->whereNotNull('batch_uuid')->count())->toBe(2);
});

it('returns failure when no skills match bulk generation filters', function () {
    Queue::fake();

    $this->artisan(GenerateQuestionsBulkCommand::class, [
        '--grade' => 'EF99',
    ])->assertFailed();

    Queue::assertNothingPushed();
    expect(AiJob::query()->count())->toBe(0);
});

it('does not dispatch bulk generation when ai is disabled', function () {
    Queue::fake();
    config()->set('services.ai.enabled', false);

    $this->artisan(GenerateQuestionsBulkCommand::class)->assertFailed();

    Queue::assertNothingPushed();
    expect(AiJob::query()->count())->toBe(0);
});
