<?php

use App\Domain\Accounts\Models\Student;
use App\Domain\Content\Models\BnccSkill;
use App\Domain\Content\Models\Grade;
use App\Domain\Content\Models\Path;
use App\Domain\Content\Models\PathNode;
use App\Domain\Content\Models\Subject;
use App\Domain\Gameplay\Models\Lesson;
use Database\Seeders\Gameplay\StudentNeuronsSeeder;
use Database\Seeders\Gameplay\TrailContentSeeder;

it('seeds 2000 neurons for each student without duplicating seed transaction', function () {
    $students = Student::factory()->count(2)->create();

    $this->seed(StudentNeuronsSeeder::class);
    $this->seed(StudentNeuronsSeeder::class);

    foreach ($students as $student) {
        $freshStudent = $student->fresh();

        expect($freshStudent)->not->toBeNull();
        expect($freshStudent?->totalGems())->toBe(2000);
        expect(
            $freshStudent?->gemTransactions()
                ->where('source', 'seed_neurons')
                ->count()
        )->toBe(1);
    }
});

it('seeds trilhas with published nodes lessons and playable questions', function () {
    $grade = Grade::factory()->create([
        'stage' => 'fundamental_1',
        'order' => 1,
    ]);
    $subject = Subject::factory()->create();
    $path = Path::factory()->published()->create([
        'grade_id' => $grade->id,
        'subject_id' => $subject->id,
    ]);

    BnccSkill::factory()->create([
        'grade_id' => $path->grade_id,
        'subject_id' => $path->subject_id,
    ]);

    $this->seed(TrailContentSeeder::class);

    $nodes = PathNode::query()
        ->where('path_id', $path->id)
        ->where('published', true)
        ->orderBy('order')
        ->get();

    expect($nodes)->toHaveCount(15);

    $lessons = Lesson::query()
        ->whereIn('node_id', $nodes->pluck('id'))
        ->where('published', true)
        ->orderBy('id')
        ->get();

    expect($lessons)->toHaveCount(15);

    $firstLesson = $lessons->first();

    expect($firstLesson)->not->toBeNull();
    expect($firstLesson?->questions()->count())->toBeGreaterThanOrEqual(10);

    $firstQuestion = $firstLesson?->questions()->first();

    expect($firstQuestion)->not->toBeNull();
    expect($firstQuestion?->type)->toBe('multiple_choice');
    expect($firstQuestion?->status)->toBe('published');
    expect($firstQuestion?->options)->toBeArray();
    expect(in_array($firstQuestion?->correct_answer, $firstQuestion?->options ?? [], true))->toBeTrue();
});
