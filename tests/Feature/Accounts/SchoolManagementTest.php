<?php

use App\Domain\Accounts\Models\School;
use App\Domain\Accounts\Models\SchoolClass;
use App\Domain\Accounts\Models\Student;
use App\Models\User;

it('student from different school cannot be added to class', function () {
    $schoolA = School::factory()->create();
    $schoolB = School::factory()->create();

    $class = SchoolClass::factory()->create(['school_id' => $schoolA->id]);
    $student = Student::factory()->create(['school_id' => $schoolB->id]);

    $user = User::factory()->create(['role' => 'teacher', 'school_id' => $schoolA->id]);

    $this->actingAs($user)
        ->post("/classes/{$class->id}/students", ['student_id' => $student->id])
        ->assertForbidden();
});

it('teacher can add student from same school to class', function () {
    $school = School::factory()->create();

    $class = SchoolClass::factory()->create(['school_id' => $school->id]);
    $student = Student::factory()->create(['school_id' => $school->id]);

    $user = User::factory()->create(['role' => 'teacher', 'school_id' => $school->id]);

    $this->actingAs($user)
        ->post("/classes/{$class->id}/students", ['student_id' => $student->id])
        ->assertRedirect();

    expect($class->students()->where('student_id', $student->id)->exists())->toBeTrue();
});

it('teacher cannot view class from another school', function () {
    $schoolA = School::factory()->create();
    $schoolB = School::factory()->create();

    $classB = SchoolClass::factory()->create(['school_id' => $schoolB->id]);
    $teacher = User::factory()->create(['role' => 'teacher', 'school_id' => $schoolA->id]);

    $this->actingAs($teacher)
        ->get("/classes/{$classB->id}")
        ->assertForbidden();
});

it('teacher can view class from own school', function () {
    $school = School::factory()->create();

    $class = SchoolClass::factory()->create(['school_id' => $school->id]);
    $teacher = User::factory()->create(['role' => 'teacher', 'school_id' => $school->id]);

    $this->actingAs($teacher)
        ->get("/classes/{$class->id}")
        ->assertSuccessful();
});

it('assignment due_at must be in the future', function () {
    $school = School::factory()->create();
    $class = SchoolClass::factory()->create(['school_id' => $school->id]);
    $teacher = User::factory()->create(['role' => 'teacher', 'school_id' => $school->id]);

    $this->actingAs($teacher)
        ->post('/assignments', [
            'class_id' => $class->id,
            'type' => 'nodes',
            'title' => 'Tarefa de teste',
            'due_at' => now()->subDay()->toDateTimeString(),
        ])
        ->assertSessionHasErrors('due_at');
});

it('assignment is created with valid data', function () {
    $school = School::factory()->create();
    $class = SchoolClass::factory()->create(['school_id' => $school->id]);
    $teacher = User::factory()->create(['role' => 'teacher', 'school_id' => $school->id]);

    $this->actingAs($teacher)
        ->post('/assignments', [
            'class_id' => $class->id,
            'type' => 'lesson',
            'title' => 'Revisão de matemática',
            'due_at' => now()->addDays(7)->toDateTimeString(),
        ])
        ->assertRedirect();

    expect(\App\Domain\Accounts\Models\Assignment::where('class_id', $class->id)->exists())->toBeTrue();
});

it('filament admin can access school management resources', function (string $route) {
    $admin = User::factory()->create(['role' => 'super_admin']);

    $this->actingAs($admin)
        ->get($route)
        ->assertSuccessful();
})->with([
    '/admin/schools',
    '/admin/schools/create',
    '/admin/school-classes',
    '/admin/school-classes/create',
    '/admin/students',
    '/admin/students/create',
]);
