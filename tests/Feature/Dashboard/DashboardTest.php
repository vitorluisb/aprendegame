<?php

use App\Domain\Accounts\Models\School;
use App\Domain\Accounts\Models\SchoolClass;
use App\Domain\Accounts\Models\Student;
use App\Domain\Content\Models\Grade;
use App\Domain\Content\Models\Path;
use App\Domain\Gameplay\Models\Badge;
use App\Domain\Gameplay\Models\GemTransaction;
use App\Domain\Gameplay\Models\ShopItem;
use App\Domain\Gameplay\Models\StudentBadge;
use App\Domain\Gameplay\Models\StudentItem;
use App\Domain\Gameplay\Models\Streak;
use App\Domain\Gameplay\Models\XpTransaction;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

it('guardian only sees their own children', function () {
    $guardian = User::factory()->create(['role' => 'guardian']);
    $myStudent = Student::factory()->create();
    $otherStudent = Student::factory()->create();

    $guardian->studentsGuarded()->attach($myStudent->id);

    $this->actingAs($guardian)
        ->get('/dashboard')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->has('students', 1)
            ->where('students.0.name', $myStudent->name)
            ->missing('students.1')
        );
});

it('guardian dashboard returns students list', function () {
    $guardian = User::factory()->create(['role' => 'guardian']);
    $student1 = Student::factory()->create();
    $student2 = Student::factory()->create();

    $guardian->studentsGuarded()->attach([$student1->id, $student2->id]);

    $this->actingAs($guardian)
        ->get('/dashboard')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->has('students', 2)
        );
});

it('teacher dashboard shows at-risk students', function () {
    $school = School::factory()->create();
    $teacher = User::factory()->create(['role' => 'teacher', 'school_id' => $school->id]);
    $class = SchoolClass::factory()->create(['school_id' => $school->id]);

    $atRisk = Student::factory()->create(['school_id' => $school->id]);
    Streak::factory()->create([
        'student_id' => $atRisk->id,
        'current' => 5,
        'best' => 5,
        'last_activity_date' => now()->subDays(5),
    ]);
    $class->students()->syncWithoutDetaching([$atRisk->id]);

    $this->actingAs($teacher)
        ->get('/teacher/dashboard')
        ->assertSuccessful()
        ->assertSee('alunos em risco')
        ->assertInertia(fn (Assert $page) => $page
            ->has('at_risk_students', 1)
            ->where('at_risk_students.0.name', $atRisk->name)
        );
});

it('teacher dashboard does not show active students as at-risk', function () {
    $school = School::factory()->create();
    $teacher = User::factory()->create(['role' => 'teacher', 'school_id' => $school->id]);
    $class = SchoolClass::factory()->create(['school_id' => $school->id]);

    $active = Student::factory()->create(['school_id' => $school->id]);
    Streak::factory()->create([
        'student_id' => $active->id,
        'last_activity_date' => today(),
    ]);
    $class->students()->syncWithoutDetaching([$active->id]);

    $response = $this->actingAs($teacher)->get('/teacher/dashboard');

    $response->assertSuccessful();
    // Aluno ativo não deve aparecer na seção de risco
    $response->assertDontSee($active->name);
});

it('student dashboard loads successfully', function () {
    $user = User::factory()->create(['role' => 'student']);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('role', 'student')
            ->has('student')
            ->where('student.name', $user->name)
        );
});

it('student dashboard recommendations are filtered by current grade', function () {
    $user = User::factory()->create(['role' => 'student', 'school_id' => null]);
    $student = Student::factory()->create(['user_id' => $user->id, 'school_id' => null]);

    $matchingPath = Path::factory()->published()->create();
    $otherPath = Path::factory()->published()->create();
    $student->update(['grade_id' => $matchingPath->grade_id]);

    expect($matchingPath->grade_id)->not->toBe($otherPath->grade_id);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('student.recommended_paths.0.id', $matchingPath->id)
            ->missing('student.recommended_paths.1')
        );
});

it('student can access profile page', function () {
    $user = User::factory()->create(['role' => 'student']);
    $student = Student::factory()->create([
        'user_id' => $user->id,
        'name' => 'Aluno Perfil',
    ]);

    XpTransaction::factory()->create([
        'student_id' => $student->id,
        'amount' => 250,
    ]);
    Streak::factory()->create([
        'student_id' => $student->id,
        'current' => 8,
        'best' => 15,
    ]);
    GemTransaction::factory()->create([
        'student_id' => $student->id,
        'amount' => 120,
    ]);

    $badge = Badge::factory()->create([
        'name' => 'Conquista 1',
        'icon' => '🏆',
    ]);
    StudentBadge::factory()->create([
        'student_id' => $student->id,
        'badge_id' => $badge->id,
    ]);

    $item = ShopItem::factory()->create([
        'name' => 'Item 1',
        'type' => 'avatar',
        'image_url' => '/storage/shop-avatars/item-1.png',
    ]);
    StudentItem::factory()->equipped()->create([
        'student_id' => $student->id,
        'item_id' => $item->id,
    ]);

    $this->actingAs($user)
        ->get('/perfil')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Profile/Show')
            ->where('student.name', 'Aluno Perfil')
            ->where('student.total_xp', 250)
            ->where('student.level', 3)
            ->where('student.xp_in_level', 50)
            ->where('student.streak_current', 8)
            ->where('student.streak_best', 15)
            ->where('student.total_gems', 120)
            ->where('student.badges_count', 1)
            ->where('student.inventory_count', 1)
            ->where('student.avatar_url', '/storage/shop-avatars/item-1.png')
            ->where('student.badges.0.badge.name', 'Conquista 1')
            ->where('student.inventory.0.item.name', 'Item 1')
            ->where('student.inventory.0.equipped', true)
        );
});

it('non-student can open profile page without redirect', function () {
    $guardian = User::factory()->create(['role' => 'guardian']);

    $this->actingAs($guardian)
        ->get('/perfil')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Profile/Show')
            ->where('is_student', false)
            ->where('student', null)
        );
});

it('user with student profile can access profile page even with non-student role', function () {
    $user = User::factory()->create([
        'role' => 'guardian',
        'school_id' => null,
    ]);

    Student::factory()->create([
        'user_id' => $user->id,
        'school_id' => null,
        'name' => 'Aluno Vinculado',
    ]);

    $this->actingAs($user)
        ->get('/perfil')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Profile/Show')
            ->where('is_student', true)
            ->where('student.name', 'Aluno Vinculado')
        );
});

it('standalone student without school or guardian can access dashboard', function () {
    $user = User::factory()->create([
        'role' => 'student',
        'school_id' => null,
    ]);

    Student::factory()->create([
        'user_id' => $user->id,
        'school_id' => null,
        'name' => 'Aluno Livre',
    ]);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('role', 'student')
            ->where('student.name', 'Aluno Livre')
        );
});

it('standalone student without school or guardian can access profile', function () {
    $user = User::factory()->create([
        'role' => 'student',
        'school_id' => null,
    ]);

    Student::factory()->create([
        'user_id' => $user->id,
        'school_id' => null,
        'name' => 'Aluno Solo',
    ]);

    $this->actingAs($user)
        ->get('/perfil')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Profile/Show')
            ->where('student.name', 'Aluno Solo')
        );
});

it('student can update current grade from profile', function () {
    $user = User::factory()->create([
        'role' => 'student',
        'school_id' => null,
    ]);
    $student = Student::factory()->create([
        'user_id' => $user->id,
        'school_id' => null,
    ]);
    $grade = Grade::factory()->create();

    $this->actingAs($user)
        ->post('/perfil/serie', ['grade_id' => $grade->id])
        ->assertRedirect();

    expect($student->fresh()->grade_id)->toBe($grade->id);
});

it('student can upload personal avatar from profile', function () {
    Storage::fake('public');

    $user = User::factory()->create([
        'role' => 'student',
        'school_id' => null,
    ]);
    $student = Student::factory()->create([
        'user_id' => $user->id,
        'school_id' => null,
    ]);

    $this->actingAs($user)
        ->post('/perfil/avatar', [
            'avatar' => UploadedFile::fake()->image('profile.png'),
        ])
        ->assertRedirect();

    $avatarUrl = $student->fresh()->avatar_url;

    expect($avatarUrl)->not->toBeNull();
    expect(str_starts_with((string) $avatarUrl, '/media/student-avatars/'))->toBeTrue();
    Storage::disk('public')->assertExists(str_replace('/media/', '', (string) $avatarUrl));
});

it('serves uploaded student avatar through media route', function () {
    Storage::fake('public');

    $user = User::factory()->create([
        'role' => 'student',
        'school_id' => null,
    ]);
    $student = Student::factory()->create([
        'user_id' => $user->id,
        'school_id' => null,
        'avatar_url' => '/media/student-avatars/avatar-test.png',
    ]);

    Storage::disk('public')->put('student-avatars/avatar-test.png', 'fake-image');

    $this->actingAs($user)
        ->get('/media/student-avatars/avatar-test.png')
        ->assertSuccessful();

    expect($student->fresh()->avatar_url)->toBe('/media/student-avatars/avatar-test.png');
});

it('user with student profile can update grade from profile', function () {
    $user = User::factory()->create([
        'role' => 'guardian',
        'school_id' => null,
    ]);
    $student = Student::factory()->create([
        'user_id' => $user->id,
        'school_id' => null,
    ]);
    $grade = Grade::factory()->create();

    $this->actingAs($user)
        ->post('/perfil/serie', ['grade_id' => $grade->id])
        ->assertRedirect();

    expect($student->fresh()->grade_id)->toBe($grade->id);
});
