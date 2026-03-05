<?php

use App\Domain\Accounts\Models\School;
use App\Domain\Accounts\Models\Student;
use App\Models\User;

it('creates school with unique slug', function () {
    School::factory()->create(['slug' => 'escola-a']);

    expect(School::query()->where('slug', 'escola-a')->exists())->toBeTrue();
});

it('soft deletes school without destroying users', function () {
    $school = School::factory()->create();
    User::factory()->count(3)->create(['school_id' => $school->id]);

    $school->delete();

    expect(School::withTrashed()->find($school->id))->not->toBeNull();
    expect(User::query()->where('school_id', $school->id)->count())->toBe(3);
});

it('student can exist without user account', function () {
    $student = Student::factory()->create(['user_id' => null]);

    expect($student->user_id)->toBeNull();
    expect($student->exists)->toBeTrue();
});

it('student_guardians records consent', function () {
    $guardian = User::factory()->create(['role' => 'guardian']);
    $student = Student::factory()->create();

    $guardian->guardiansOf()->attach($student->id, [
        'relationship' => 'parent',
        'consent_given' => true,
        'consent_given_at' => now(),
    ]);

    expect($student->guardians()->where('guardian_user_id', $guardian->id)->exists())->toBeTrue();
});
