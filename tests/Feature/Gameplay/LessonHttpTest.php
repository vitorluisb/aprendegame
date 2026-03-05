<?php

use App\Domain\Accounts\Models\Student;
use App\Domain\Content\Models\PathNode;
use App\Domain\Gameplay\Models\Lesson;
use App\Domain\Gameplay\Models\LessonRun;
use App\Domain\Gameplay\Models\Question;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('redirects guests from lesson play page', function () {
    $lesson = Lesson::factory()->published()->create();

    $this->get("/aulas/{$lesson->id}/jogar")->assertRedirect('/login');
});

it('student can access lesson play page', function () {
    $user = User::factory()->create(['role' => 'student']);
    Student::factory()->create(['user_id' => $user->id]);
    $lesson = Lesson::factory()->published()->create();

    $this->actingAs($user)
        ->get("/aulas/{$lesson->id}/jogar")
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Lessons/Play')
            ->has('lesson.id')
            ->has('run_id')
            ->has('questions')
        );
});

it('questions do not expose correct_answer to the student', function () {
    $user = User::factory()->create(['role' => 'student']);
    $student = Student::factory()->create(['user_id' => $user->id]);
    $lesson = Lesson::factory()->published()->create();
    $question = Question::factory()->multipleChoice()->create();
    $lesson->questions()->attach($question->id, ['order' => 1]);

    $this->actingAs($user)
        ->get("/aulas/{$lesson->id}/jogar")
        ->assertInertia(fn (Assert $page) => $page
            ->missing('questions.0.correct_answer')
        );
});

it('resuming a lesson reuses the existing incomplete run', function () {
    $user = User::factory()->create(['role' => 'student']);
    $student = Student::factory()->create(['user_id' => $user->id]);
    $lesson = Lesson::factory()->published()->create();

    $existingRun = LessonRun::factory()->create([
        'student_id' => $student->id,
        'lesson_id' => $lesson->id,
        'finished_at' => null,
    ]);

    $this->actingAs($user)
        ->get("/aulas/{$lesson->id}/jogar")
        ->assertInertia(fn (Assert $page) => $page
            ->where('run_id', $existingRun->id)
        );
});

it('student can submit an answer', function () {
    $user = User::factory()->create(['role' => 'student']);
    $student = Student::factory()->create(['user_id' => $user->id]);
    $lesson = Lesson::factory()->published()->create();
    $question = Question::factory()->multipleChoice()->create(['correct_answer' => 'A']);
    $lesson->questions()->attach($question->id, ['order' => 1]);

    $run = LessonRun::factory()->create([
        'student_id' => $student->id,
        'lesson_id' => $lesson->id,
        'finished_at' => null,
    ]);

    $this->actingAs($user)
        ->postJson("/runs/{$run->id}/responder", [
            'question_id' => $question->id,
            'answer' => 'A',
            'time_ms' => 3000,
        ])
        ->assertSuccessful()
        ->assertJsonStructure(['correct', 'explanation', 'correct_answer']);
});

it('correct answer returns correct true', function () {
    $user = User::factory()->create(['role' => 'student']);
    $student = Student::factory()->create(['user_id' => $user->id]);
    $lesson = Lesson::factory()->published()->create();
    $question = Question::factory()->multipleChoice()->create(['correct_answer' => 'A']);
    $lesson->questions()->attach($question->id, ['order' => 1]);

    $run = LessonRun::factory()->create([
        'student_id' => $student->id,
        'lesson_id' => $lesson->id,
        'finished_at' => null,
    ]);

    $this->actingAs($user)
        ->postJson("/runs/{$run->id}/responder", [
            'question_id' => $question->id,
            'answer' => 'A',
            'time_ms' => 2000,
        ])
        ->assertJson(['correct' => true]);
});

it('wrong answer returns correct false', function () {
    $user = User::factory()->create(['role' => 'student']);
    $student = Student::factory()->create(['user_id' => $user->id]);
    $lesson = Lesson::factory()->published()->create();
    $question = Question::factory()->multipleChoice()->create(['correct_answer' => 'A']);
    $lesson->questions()->attach($question->id, ['order' => 1]);

    $run = LessonRun::factory()->create([
        'student_id' => $student->id,
        'lesson_id' => $lesson->id,
        'finished_at' => null,
    ]);

    $this->actingAs($user)
        ->postJson("/runs/{$run->id}/responder", [
            'question_id' => $question->id,
            'answer' => 'B',
            'time_ms' => 2000,
        ])
        ->assertJson(['correct' => false]);
});

it('student can finish a lesson run', function () {
    $user = User::factory()->create(['role' => 'student']);
    $student = Student::factory()->create(['user_id' => $user->id]);
    $lesson = Lesson::factory()->published()->create();

    $run = LessonRun::factory()->create([
        'student_id' => $student->id,
        'lesson_id' => $lesson->id,
        'finished_at' => null,
    ]);

    $this->actingAs($user)
        ->postJson("/runs/{$run->id}/finalizar")
        ->assertSuccessful()
        ->assertJsonStructure(['score', 'xp_earned', 'correct_count', 'total_count']);
});

it('finishing a run marks it as done in database', function () {
    $user = User::factory()->create(['role' => 'student']);
    $student = Student::factory()->create(['user_id' => $user->id]);
    $lesson = Lesson::factory()->published()->create();

    $run = LessonRun::factory()->create([
        'student_id' => $student->id,
        'lesson_id' => $lesson->id,
        'finished_at' => null,
    ]);

    $this->actingAs($user)
        ->postJson("/runs/{$run->id}/finalizar")
        ->assertSuccessful();

    expect($run->fresh()->finished_at)->not->toBeNull();
});

it('answer endpoint validates required fields', function () {
    $user = User::factory()->create(['role' => 'student']);
    $student = Student::factory()->create(['user_id' => $user->id]);
    $lesson = Lesson::factory()->published()->create();

    $run = LessonRun::factory()->create([
        'student_id' => $student->id,
        'lesson_id' => $lesson->id,
        'finished_at' => null,
    ]);

    $this->actingAs($user)
        ->postJson("/runs/{$run->id}/responder", [])
        ->assertUnprocessable();
});
