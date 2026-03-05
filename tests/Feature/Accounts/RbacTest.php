<?php

use App\Domain\Accounts\Models\School;
use App\Domain\Accounts\Models\Student;
use App\Domain\Accounts\Services\PermissionCache;
use App\Models\User;

it('student cannot access teacher routes', function () {
    $student = User::factory()->create(['role' => 'student']);

    $this->actingAs($student)
        ->get('/teacher/classes')
        ->assertForbidden();
});

it('school scope prevents cross-school data access', function () {
    $schoolA = School::factory()->create();
    $schoolB = School::factory()->create();

    $userA = User::factory()->create([
        'school_id' => $schoolA->id,
        'role' => 'teacher',
    ]);

    $studentB = Student::factory()->create(['school_id' => $schoolB->id]);

    $this->actingAs($userA);

    expect(Student::find($studentB->id))->toBeNull();
});

it('super_admin bypasses school scope', function () {
    $admin = User::factory()->create(['role' => 'super_admin', 'school_id' => null]);
    $schoolB = School::factory()->create();
    $student = Student::factory()->create(['school_id' => $schoolB->id]);

    $this->actingAs($admin);

    expect(Student::find($student->id))->not->toBeNull();
});

it('permission cache is invalidated on role change', function () {
    $user = User::factory()->create(['role' => 'student']);

    PermissionCache::get($user);
    $user->update(['role' => 'teacher']);
    PermissionCache::flush($user);

    $permissions = PermissionCache::get($user);

    expect($permissions['content.view'])->toBeTrue();
});
