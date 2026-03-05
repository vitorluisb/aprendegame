<?php

use App\Domain\Content\Models\BnccSkill;
use App\Domain\Content\Models\Grade;
use App\Domain\Content\Models\Subject;
use App\Domain\Gameplay\Models\Lesson;
use App\Domain\Gameplay\Models\Question;
use Illuminate\Database\QueryException;

it('question requires valid type', function () {
    $grade = Grade::factory()->create();
    $subject = Subject::factory()->create();
    $skill = BnccSkill::factory()->create(['grade_id' => $grade->id, 'subject_id' => $subject->id]);

    expect(fn () => Question::factory()->create(['skill_id' => $skill->id, 'type' => 'invalid_type']))
        ->toThrow(QueryException::class);
});

it('published question soft deletes', function () {
    $question = Question::factory()->published()->create();

    $question->delete();

    expect(Question::withTrashed()->find($question->id))->not->toBeNull();
    expect(Question::find($question->id))->toBeNull();
});

it('question in published lesson cannot be force deleted', function () {
    $lesson = Lesson::factory()->published()->create();
    $question = Question::factory()->published()->create();
    $lesson->questions()->attach($question->id, ['order' => 1]);

    expect(fn () => $question->forceDelete())->toThrow(QueryException::class);
});

it('options json is saved and retrieved correctly', function () {
    $question = Question::factory()->multipleChoice()->create();

    $options = $question->fresh()->options;

    expect($options)->toBeArray();
    expect($options)->toHaveCount(4);
    expect($options[0])->toHaveKey('key');
    expect($options[0])->toHaveKey('text');
});

it('lesson has many questions via pivot', function () {
    $lesson = Lesson::factory()->create();
    $q1 = Question::factory()->create();
    $q2 = Question::factory()->create();

    $lesson->questions()->attach([
        $q1->id => ['order' => 1],
        $q2->id => ['order' => 2],
    ]);

    expect($lesson->questions)->toHaveCount(2);
});

it('question cannot be attached to same lesson twice', function () {
    $lesson = Lesson::factory()->create();
    $question = Question::factory()->create();

    $lesson->questions()->attach($question->id, ['order' => 1]);

    expect(fn () => $lesson->questions()->attach($question->id, ['order' => 2]))
        ->toThrow(QueryException::class);
});

it('difficulty must be between 1 and 5', function () {
    $grade = Grade::factory()->create();
    $subject = Subject::factory()->create();
    $skill = BnccSkill::factory()->create(['grade_id' => $grade->id, 'subject_id' => $subject->id]);

    // valid boundary
    expect(Question::factory()->create(['skill_id' => $skill->id, 'difficulty' => 5]))->toBeInstanceOf(Question::class);
    expect(Question::factory()->create(['skill_id' => $skill->id, 'difficulty' => 1]))->toBeInstanceOf(Question::class);
});

it('fill_blank question has no options', function () {
    $question = Question::factory()->fillBlank()->create();

    expect($question->options)->toBeNull();
    expect($question->correct_answer)->not->toBeEmpty();
});
