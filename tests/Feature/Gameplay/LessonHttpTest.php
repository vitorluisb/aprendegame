<?php

use App\Domain\Accounts\Models\Student;
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
            ->where('lives_current', 10)
            ->where('lives_max', 10)
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
        ->assertJsonStructure(['correct', 'explanation', 'correct_answer', 'remaining_lives', 'lives_max']);
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
        ->assertJson(['correct' => true, 'remaining_lives' => 10, 'lives_max' => 10]);
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
        ->assertJson(['correct' => false, 'remaining_lives' => 9, 'lives_max' => 10]);
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

it('answer endpoint blocks student without lives', function () {
    $user = User::factory()->create(['role' => 'student']);
    $student = Student::factory()->create(['user_id' => $user->id, 'lives_current' => 0, 'lives_max' => 10]);
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
        ->assertStatus(422)
        ->assertJsonStructure(['message', 'remaining_lives', 'lives_max'])
        ->assertJson(['remaining_lives' => 0, 'lives_max' => 10]);
});

it('student cannot answer another student run', function () {
    $owner = User::factory()->create(['role' => 'student']);
    $ownerStudent = Student::factory()->create(['user_id' => $owner->id]);
    $intruder = User::factory()->create(['role' => 'student']);
    Student::factory()->create(['user_id' => $intruder->id]);

    $lesson = Lesson::factory()->published()->create();
    $question = Question::factory()->multipleChoice()->create(['correct_answer' => 'A']);
    $lesson->questions()->attach($question->id, ['order' => 1]);

    $run = LessonRun::factory()->create([
        'student_id' => $ownerStudent->id,
        'lesson_id' => $lesson->id,
        'finished_at' => null,
    ]);

    $this->actingAs($intruder)
        ->postJson("/runs/{$run->id}/responder", [
            'question_id' => $question->id,
            'answer' => 'A',
            'time_ms' => 1000,
        ])
        ->assertForbidden();
});

it('student cannot finish another student run', function () {
    $owner = User::factory()->create(['role' => 'student']);
    $ownerStudent = Student::factory()->create(['user_id' => $owner->id]);
    $intruder = User::factory()->create(['role' => 'student']);
    Student::factory()->create(['user_id' => $intruder->id]);

    $lesson = Lesson::factory()->published()->create();
    $run = LessonRun::factory()->create([
        'student_id' => $ownerStudent->id,
        'lesson_id' => $lesson->id,
        'finished_at' => null,
    ]);

    $this->actingAs($intruder)
        ->postJson("/runs/{$run->id}/finalizar")
        ->assertForbidden();
});

it('answer endpoint rejects question that is not attached to run lesson', function () {
    $user = User::factory()->create(['role' => 'student']);
    $student = Student::factory()->create(['user_id' => $user->id]);
    $lesson = Lesson::factory()->published()->create();
    $attachedQuestion = Question::factory()->multipleChoice()->create(['correct_answer' => 'A']);
    $foreignQuestion = Question::factory()->multipleChoice()->create(['correct_answer' => 'B']);
    $lesson->questions()->attach($attachedQuestion->id, ['order' => 1]);

    $run = LessonRun::factory()->create([
        'student_id' => $student->id,
        'lesson_id' => $lesson->id,
        'finished_at' => null,
    ]);

    $this->actingAs($user)
        ->postJson("/runs/{$run->id}/responder", [
            'question_id' => $foreignQuestion->id,
            'answer' => 'A',
            'time_ms' => 2000,
        ])
        ->assertStatus(422)
        ->assertJsonStructure(['message', 'remaining_lives', 'lives_max']);
});
