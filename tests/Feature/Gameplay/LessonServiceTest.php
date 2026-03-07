<?php

use App\Domain\Accounts\Models\Student;
use App\Domain\Content\Models\BnccSkill;
use App\Domain\Content\Models\Path;
use App\Domain\Content\Models\PathNode;
use App\Domain\Gameplay\Models\Attempt;
use App\Domain\Gameplay\Models\GemTransaction;
use App\Domain\Gameplay\Models\Lesson;
use App\Domain\Gameplay\Models\LessonRun;
use App\Domain\Gameplay\Models\Mastery;
use App\Domain\Gameplay\Models\Question;
use App\Domain\Gameplay\Models\Streak;
use App\Domain\Gameplay\Models\XpTransaction;
use App\Domain\Gameplay\Services\LessonService;
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
    expect($run->student->fresh()->lives_current)->toBe(9);
});

it('fill_blank answer is case insensitive', function () {
    Queue::fake();
    $run = LessonRun::factory()->create();
    $question = Question::factory()->fillBlank()->create(['correct_answer' => 'Fotossíntese']);
    $service = app(LessonService::class);

    $attempt = $service->answer($run, $question, 'fotossíntese', 3000);

    expect($attempt->correct)->toBeTrue();
});

it('accepts true_false answers using true or falso labels', function () {
    Queue::fake();
    $run = LessonRun::factory()->create();
    $question = Question::factory()->trueFalse()->create([
        'correct_answer' => 'true',
    ]);
    $service = app(LessonService::class);

    $attempt = $service->answer($run, $question, 'Verdadeiro', 3000);

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

it('persists xp streak and mastery immediately after lesson completion', function () {
    $path = Path::factory()->create();
    $skill = BnccSkill::factory()->create([
        'grade_id' => $path->grade_id,
        'subject_id' => $path->subject_id,
    ]);
    $node = PathNode::factory()->forPath($path)->create(['skill_ids' => [$skill->id]]);
    $lesson = Lesson::factory()->forNode($node)->create();
    $run = LessonRun::factory()->create(['lesson_id' => $lesson->id]);
    Attempt::factory()->count(10)->correct()->create(['run_id' => $run->id, 'student_id' => $run->student_id]);

    $service = app(LessonService::class);
    $service->finish($run);

    expect(
        XpTransaction::query()
            ->where('student_id', $run->student_id)
            ->where('reference_type', 'LessonRun')
            ->where('reference_id', $run->id)
            ->exists()
    )->toBeTrue();
    expect(Streak::query()->where('student_id', $run->student_id)->exists())->toBeTrue();
    expect(
        Mastery::query()
            ->where('student_id', $run->student_id)
            ->where('skill_id', $skill->id)
            ->exists()
    )->toBeTrue();
});

it('reduces mastery when student performance is weak for the node skill', function () {
    $path = Path::factory()->create();
    $skill = BnccSkill::factory()->create([
        'grade_id' => $path->grade_id,
        'subject_id' => $path->subject_id,
    ]);
    $node = PathNode::factory()->forPath($path)->create(['skill_ids' => [$skill->id]]);
    $lesson = Lesson::factory()->forNode($node)->create();
    $run = LessonRun::factory()->create(['lesson_id' => $lesson->id]);

    $question = Question::factory()->multipleChoice()->create([
        'skill_id' => $skill->id,
        'correct_answer' => 'A',
    ]);

    Mastery::factory()->create([
        'student_id' => $run->student_id,
        'skill_id' => $skill->id,
        'mastery_score' => 50,
        'interval_days' => 7,
        'consecutive_correct' => 3,
    ]);

    Attempt::factory()->create([
        'run_id' => $run->id,
        'student_id' => $run->student_id,
        'question_id' => $question->id,
        'correct' => true,
    ]);
    Attempt::factory()->count(4)->create([
        'run_id' => $run->id,
        'student_id' => $run->student_id,
        'question_id' => $question->id,
        'correct' => false,
    ]);

    $service = app(LessonService::class);
    $service->finish($run);

    $mastery = Mastery::query()
        ->where('student_id', $run->student_id)
        ->where('skill_id', $skill->id)
        ->firstOrFail();

    expect($mastery->mastery_score)->toBe(35);
    expect($mastery->next_review_at?->lessThanOrEqualTo(now()))->toBeTrue();
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

it('cannot answer when student has no lives', function () {
    Queue::fake();
    $run = LessonRun::factory()->create();
    $run->student->update(['lives_current' => 0, 'lives_max' => 10]);
    $question = Question::factory()->multipleChoice()->create(['correct_answer' => 'A']);
    $service = app(LessonService::class);

    expect(fn () => $service->answer($run, $question, 'A', 3000))
        ->toThrow(RuntimeException::class, 'Você está sem vidas. Compre vidas na loja para continuar.');
});

it('awards neurons when clearing a node for the first time', function () {
    $path = Path::factory()->create();
    $node = PathNode::factory()->forPath($path)->create(['order' => 1, 'published' => true]);
    $lesson = Lesson::factory()->forNode($node)->create();
    $run = LessonRun::factory()->create(['lesson_id' => $lesson->id]);
    Attempt::factory()->count(8)->correct()->create(['run_id' => $run->id, 'student_id' => $run->student_id]);
    Attempt::factory()->count(2)->wrong()->create(['run_id' => $run->id, 'student_id' => $run->student_id]);

    $service = app(LessonService::class);
    $finished = $service->finish($run);

    expect((int) $finished->neurons_earned)->toBe(3);
    expect(
        GemTransaction::query()
            ->where('student_id', $run->student_id)
            ->where('source', 'node_clear_bonus')
            ->where('amount', 3)
            ->exists()
    )->toBeTrue();
});

it('does not award neurons twice for the same node', function () {
    $path = Path::factory()->create();
    $node = PathNode::factory()->forPath($path)->create(['order' => 1, 'published' => true]);
    $lessonA = Lesson::factory()->forNode($node)->create();
    $lessonB = Lesson::factory()->forNode($node)->create();

    $firstRun = LessonRun::factory()->create(['lesson_id' => $lessonA->id]);
    Attempt::factory()->count(8)->correct()->create(['run_id' => $firstRun->id, 'student_id' => $firstRun->student_id]);
    Attempt::factory()->count(2)->wrong()->create(['run_id' => $firstRun->id, 'student_id' => $firstRun->student_id]);

    $service = app(LessonService::class);
    $service->finish($firstRun);

    $secondRun = LessonRun::factory()->create([
        'student_id' => $firstRun->student_id,
        'lesson_id' => $lessonB->id,
    ]);
    Attempt::factory()->count(9)->correct()->create(['run_id' => $secondRun->id, 'student_id' => $secondRun->student_id]);
    Attempt::factory()->count(1)->wrong()->create(['run_id' => $secondRun->id, 'student_id' => $secondRun->student_id]);

    $finishedSecondRun = $service->finish($secondRun);

    expect((int) $finishedSecondRun->neurons_earned)->toBe(0);
    expect(
        GemTransaction::query()
            ->where('student_id', $firstRun->student_id)
            ->where('source', 'node_clear_bonus')
            ->count()
    )->toBe(1);
});
