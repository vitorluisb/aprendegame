<?php

use App\Domain\Accounts\Models\Student;
use App\Domain\Content\Models\BnccSkill;
use App\Domain\Content\Models\Path;
use App\Domain\Content\Models\PathNode;
use App\Domain\Gameplay\Models\Attempt;
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

it('lesson play fills up to interaction_count questions when mission has only a few attached', function () {
    $user = User::factory()->create(['role' => 'student']);
    Student::factory()->create(['user_id' => $user->id]);

    $path = Path::factory()->create();
    $skill = BnccSkill::factory()->create([
        'grade_id' => $path->grade_id,
        'subject_id' => $path->subject_id,
    ]);
    $node = PathNode::factory()->forPath($path)->create([
        'published' => true,
        'skill_ids' => [$skill->id],
    ]);

    $lesson = Lesson::factory()->forNode($node)->published()->create([
        'interaction_count' => 10,
    ]);

    $attached = Question::factory()->count(4)->create(['skill_id' => $skill->id]);
    foreach ($attached as $index => $question) {
        $lesson->questions()->attach($question->id, ['order' => $index + 1]);
    }

    Question::factory()->count(6)->create(['skill_id' => $skill->id]);

    $this->actingAs($user)
        ->get("/aulas/{$lesson->id}/jogar")
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->has('questions', 10)
        );

    expect($lesson->questions()->count())->toBe(10);
});

it('new lesson runs prioritize unseen questions for the same skill set', function () {
    $user = User::factory()->create(['role' => 'student']);
    $student = Student::factory()->create(['user_id' => $user->id]);

    $path = Path::factory()->create();
    $skill = BnccSkill::factory()->create([
        'grade_id' => $path->grade_id,
        'subject_id' => $path->subject_id,
    ]);
    $node = PathNode::factory()->forPath($path)->create([
        'published' => true,
        'skill_ids' => [$skill->id],
    ]);

    $lesson = Lesson::factory()->forNode($node)->published()->create([
        'interaction_count' => 5,
    ]);

    $questions = Question::factory()->count(15)->create([
        'skill_id' => $skill->id,
        'status' => 'published',
    ]);

    foreach ($questions as $index => $question) {
        $lesson->questions()->attach($question->id, ['order' => $index + 1]);
    }

    $firstRun = LessonRun::factory()->create([
        'student_id' => $student->id,
        'lesson_id' => $lesson->id,
        'finished_at' => now(),
    ]);

    $recentQuestionIds = $questions->take(5)->pluck('id')->values();
    foreach ($recentQuestionIds as $questionId) {
        Attempt::factory()->create([
            'run_id' => $firstRun->id,
            'student_id' => $student->id,
            'question_id' => $questionId,
            'correct' => true,
        ]);
    }

    $response = $this->actingAs($user)
        ->get("/aulas/{$lesson->id}/jogar")
        ->assertSuccessful();

    $returnedIds = collect($response->inertiaProps('questions'))
        ->pluck('id');

    expect($returnedIds->count())->toBe(5);
    expect($returnedIds->intersect($recentQuestionIds)->isEmpty())->toBeTrue();
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

it('accepts multiple choice answer sent as option text when correct answer is a key', function () {
    $user = User::factory()->create(['role' => 'student']);
    $student = Student::factory()->create(['user_id' => $user->id]);
    $lesson = Lesson::factory()->published()->create();
    $question = Question::factory()->create([
        'type' => 'multiple_choice',
        'options' => [
            ['key' => 'A', 'text' => 'Resposta certa'],
            ['key' => 'B', 'text' => 'Resposta errada'],
            ['key' => 'C', 'text' => 'Outra errada'],
            ['key' => 'D', 'text' => 'Última errada'],
        ],
        'correct_answer' => 'A',
    ]);
    $lesson->questions()->attach($question->id, ['order' => 1]);

    $run = LessonRun::factory()->create([
        'student_id' => $student->id,
        'lesson_id' => $lesson->id,
        'finished_at' => null,
    ]);

    $this->actingAs($user)
        ->postJson("/runs/{$run->id}/responder", [
            'question_id' => $question->id,
            'answer' => 'Resposta certa',
            'time_ms' => 2000,
        ])
        ->assertSuccessful()
        ->assertJson(['correct' => true, 'remaining_lives' => 10, 'lives_max' => 10]);
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
