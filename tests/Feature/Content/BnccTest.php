<?php

use App\Models\User;
use App\Domain\Content\Actions\ImportBnccSkills;
use App\Domain\Content\Models\BnccSkill;
use App\Domain\Content\Models\Grade;
use App\Domain\Content\Models\Subject;
use Illuminate\Database\QueryException;

it('bncc skill code must be unique', function () {
    $grade = Grade::factory()->create();
    $subject = Subject::factory()->create();

    BnccSkill::factory()->create(['code' => 'EF06MA01', 'grade_id' => $grade->id, 'subject_id' => $subject->id]);

    expect(fn () => BnccSkill::factory()->create(['code' => 'EF06MA01', 'grade_id' => $grade->id, 'subject_id' => $subject->id]))
        ->toThrow(QueryException::class);
});

it('cannot delete grade with associated skills', function () {
    $grade = Grade::factory()->create();
    $subject = Subject::factory()->create();
    BnccSkill::factory()->count(3)->create(['grade_id' => $grade->id, 'subject_id' => $subject->id]);

    expect(fn () => $grade->delete())->toThrow(QueryException::class);
});

it('cannot delete subject with associated skills', function () {
    $grade = Grade::factory()->create();
    $subject = Subject::factory()->create();
    BnccSkill::factory()->count(2)->create(['grade_id' => $grade->id, 'subject_id' => $subject->id]);

    expect(fn () => $subject->delete())->toThrow(QueryException::class);
});

it('imports bncc skills from csv', function () {
    Grade::factory()->create(['code' => '6EF', 'name' => '6º Ano EF', 'stage' => 'fundamental', 'order' => 6]);
    Grade::factory()->create(['code' => '7EF', 'name' => '7º Ano EF', 'stage' => 'fundamental', 'order' => 7]);
    Subject::factory()->create(['name' => 'Matemática', 'slug' => 'matematica']);
    Subject::factory()->create(['name' => 'Português', 'slug' => 'portugues']);

    $action = app(ImportBnccSkills::class);
    $result = $action->handle(base_path('tests/fixtures/bncc_sample.csv'));

    expect($result['imported'])->toBe(5);
    expect($result['errors'])->toBeEmpty();
    expect(BnccSkill::where('code', 'EF06MA01')->exists())->toBeTrue();
});

it('import returns error for unknown grade', function () {
    Subject::factory()->create(['slug' => 'matematica']);

    $action = app(ImportBnccSkills::class);
    $result = $action->handle(base_path('tests/fixtures/bncc_sample.csv'));

    expect($result['errors'])->not->toBeEmpty();
});

it('filament admin can list bncc skills', function () {
    $admin = User::factory()->create(['role' => 'super_admin']);

    $this->actingAs($admin)->get('/admin/bncc-skills')->assertSuccessful();
});

it('filament admin can access bncc curriculum resources', function (string $route) {
    $admin = User::factory()->create(['role' => 'super_admin']);

    $this->actingAs($admin)->get($route)->assertSuccessful();
})->with([
    '/admin/grades',
    '/admin/grades/create',
    '/admin/subjects',
    '/admin/subjects/create',
    '/admin/bncc-skills',
    '/admin/bncc-skills/create',
]);

it('grade belongs to many skills', function () {
    $grade = Grade::factory()->create();
    $subject = Subject::factory()->create();
    BnccSkill::factory()->count(5)->create(['grade_id' => $grade->id, 'subject_id' => $subject->id]);

    expect($grade->skills)->toHaveCount(5);
});

it('bncc skill belongs to grade and subject', function () {
    $grade = Grade::factory()->create();
    $subject = Subject::factory()->create();
    $skill = BnccSkill::factory()->create(['grade_id' => $grade->id, 'subject_id' => $subject->id]);

    expect($skill->grade->id)->toBe($grade->id);
    expect($skill->subject->id)->toBe($subject->id);
});
