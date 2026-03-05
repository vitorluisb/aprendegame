<?php

use App\Domain\Accounts\Models\Student;
use App\Domain\Gameplay\Models\Attempt;
use App\Domain\Gameplay\Models\Lesson;
use App\Domain\Gameplay\Models\LessonRun;
use App\Domain\Gameplay\Models\Question;
use App\Domain\Gameplay\Services\LessonService;
use App\Jobs\AwardXP;
use App\Jobs\UpdateMastery;
use App\Jobs\UpdateStreak;
use Illuminate\Support\Facades\Queue;

it('starts a lesson run for student', function () {
    $student = Student::factory()->create();
    $lesson = Lesson::factory()->create();
    $service = app(LessonService::class);

    $run = $service->start($student, $lesson);

    expect($run)->toBeInstanceOf(LessonRun::class);
    expect($run->student_id)->toBe($student->id);
    expect($run->lesson_id)->toBe($lesson->id);
    expect($run->started_at)->not->toBeNull();
    expect($run->finished_at)->toBeNull();
});

it('records attempt with correct answer', function () {
    Queue::fake();
    $run = LessonRun::factory()->create();
    $question = Question::factory()->multipleChoice()->create(['correct_answer' => 'A']);
    $service = app(LessonService::class);

    $attempt = $service->answer($run, $question, 'A', 3000);

    expect($attempt->correct)->toBeTrue();
    expect($attempt->given_answer)->toBe('A');
    expect($attempt->time_ms)->toBe(3000);
});

it('records attempt with wrong answer', function () {
    Queue::fake();
    $run = LessonRun::factory()->create();
    $question = Question::factory()->multipleChoice()->create(['correct_answer' => 'A']);
    $service = app(LessonService::class);

    $attempt = $service->answer($run, $question, 'B', 5000);

    expect($attempt->correct)->toBeFalse();
});

it('fill_blank answer is case insensitive', function () {
    Queue::fake();
    $run = LessonRun::factory()->create();
    $question = Question::factory()->fillBlank()->create(['correct_answer' => 'Fotossíntese']);
    $service = app(LessonService::class);

    $attempt = $service->answer($run, $question, 'fotossíntese', 3000);

    expect($attempt->correct)->toBeTrue();
});

it('calculates score correctly', function () {
    Queue::fake();
    $run = LessonRun::factory()->create();
    Attempt::factory()->count(8)->correct()->create(['run_id' => $run->id, 'student_id' => $run->student_id]);
    Attempt::factory()->count(2)->wrong()->create(['run_id' => $run->id, 'student_id' => $run->student_id]);

    $service = app(LessonService::class);
    $finished = $service->finish($run);

    expect($finished->score)->toBe(80);
    expect($finished->correct_count)->toBe(8);
    expect($finished->total_count)->toBe(10);
    expect($finished->finished_at)->not->toBeNull();
});

it('awards xp after lesson completion', function () {
    Queue::fake();
    $run = LessonRun::factory()->create();
    Attempt::factory()->count(10)->correct()->create(['run_id' => $run->id, 'student_id' => $run->student_id]);

    $service = app(LessonService::class);
    $service->finish($run);

    Queue::assertPushed(AwardXP::class);
    Queue::assertPushed(UpdateMastery::class);
    Queue::assertPushed(UpdateStreak::class);
});

it('xp earned is greater than zero', function () {
    Queue::fake();
    $run = LessonRun::factory()->create();
    Attempt::factory()->count(10)->correct()->create(['run_id' => $run->id, 'student_id' => $run->student_id]);

    $service = app(LessonService::class);
    $finished = $service->finish($run);

    expect($finished->xp_earned)->toBeGreaterThan(0);
});

it('score is zero when no attempts', function () {
    Queue::fake();
    $run = LessonRun::factory()->create();
    $service = app(LessonService::class);

    $finished = $service->finish($run);

    expect($finished->score)->toBe(0);
    expect($finished->xp_earned)->toBeGreaterThan(0);
});
