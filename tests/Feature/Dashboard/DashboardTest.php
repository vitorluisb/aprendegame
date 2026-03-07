<?php

use App\Domain\Accounts\Models\School;
use App\Domain\Accounts\Models\SchoolClass;
use App\Domain\Accounts\Models\Student;
use App\Domain\Content\Models\Grade;
use App\Domain\Content\Models\Path;
use App\Domain\Content\Models\PathNode;
use App\Domain\Gameplay\Models\Badge;
use App\Domain\Gameplay\Models\GemTransaction;
use App\Domain\Gameplay\Models\Lesson;
use App\Domain\Gameplay\Models\LessonRun;
use App\Domain\Gameplay\Models\ShopItem;
use App\Domain\Gameplay\Models\Streak;
use App\Domain\Gameplay\Models\StudentBadge;
use App\Domain\Gameplay\Models\StudentItem;
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
    $student = Student::factory()->create([
        'user_id' => $user->id,
        'name' => $user->name,
    ]);
    GemTransaction::factory()->create([
        'student_id' => $student->id,
        'amount' => 25,
    ]);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('role', 'student')
            ->has('student')
            ->where('student.name', $user->name)
            ->where('student.total_gems', 25)
        );
});

it('student dashboard refills one life per hour elapsed', function () {
    $user = User::factory()->create(['role' => 'student']);
    $student = Student::factory()->create([
        'user_id' => $user->id,
        'lives_current' => 2,
        'lives_max' => 10,
        'lives_refilled_at' => now()->subHours(2),
    ]);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('student.lives_current', 4)
            ->where('student.lives_max', 10)
        );

    expect($student->fresh()->lives_current)->toBe(4);
});

it('student dashboard shows latest completed lesson activity and xp', function () {
    $path = Path::factory()->published()->create(['title' => 'Trilha de Matemática']);
    $node = PathNode::factory()->forPath($path)->create(['title' => 'Frações']);
    $lesson = Lesson::factory()->forNode($node)->create(['title' => 'Aula de Frações']);
    $user = User::factory()->create(['role' => 'student']);
    $student = Student::factory()->create(['user_id' => $user->id, 'name' => 'Aluno Ativo']);

    LessonRun::factory()->create([
        'student_id' => $student->id,
        'lesson_id' => $lesson->id,
        'finished_at' => now()->subMinute(),
        'xp_earned' => 35,
    ]);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('student.last_activity.path_title', 'Trilha de Matemática')
            ->where('student.last_activity.lesson_title', 'Aula de Frações')
            ->where('student.last_activity.xp_earned', 35)
            ->has('student.last_activity.finished_at')
        );
});

it('student dashboard receives equipped theme customization', function () {
    $user = User::factory()->create(['role' => 'student']);
    $student = Student::factory()->create(['user_id' => $user->id]);
    $themeItem = ShopItem::factory()->create([
        'type' => ShopItem::TYPE_THEME,
        'slug' => 'tema-oceano-teste',
        'metadata' => ['primary' => '#0F172A', 'accent' => '#38BDF8'],
    ]);
    StudentItem::factory()->equipped()->create([
        'student_id' => $student->id,
        'item_id' => $themeItem->id,
    ]);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('gameplay_customization.theme.slug', 'tema-oceano-teste')
            ->where('gameplay_customization.theme.css_vars.--color-game-accent', '#38BDF8')
            ->where('gameplay_customization.theme.css_vars.--color-game-deep', '#0F172A')
        );
});

it('student dashboard receives equipped frame customization', function () {
    $user = User::factory()->create(['role' => 'student']);
    $student = Student::factory()->create(['user_id' => $user->id]);
    $frameItem = ShopItem::factory()->create([
        'type' => ShopItem::TYPE_FRAME,
        'slug' => 'borda-dourada-teste',
        'metadata' => ['color' => '#FFD700', 'style' => 'solid'],
    ]);
    StudentItem::factory()->equipped()->create([
        'student_id' => $student->id,
        'item_id' => $frameItem->id,
    ]);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('gameplay_customization.frame.slug', 'borda-dourada-teste')
            ->where('gameplay_customization.frame.style.borderColor', '#FFD700')
            ->where('gameplay_customization.frame.style.borderStyle', 'solid')
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

it('uploading personal avatar unequips equipped shop avatar', function () {
    Storage::fake('public');

    $user = User::factory()->create([
        'role' => 'student',
        'school_id' => null,
    ]);
    $student = Student::factory()->create([
        'user_id' => $user->id,
        'school_id' => null,
    ]);

    $shopAvatar = ShopItem::factory()->avatar()->create([
        'image_url' => '/storage/shop-avatars/shop-avatar.png',
    ]);
    $studentItem = StudentItem::factory()->equipped()->create([
        'student_id' => $student->id,
        'item_id' => $shopAvatar->id,
    ]);

    $this->actingAs($user)
        ->post('/perfil/avatar', [
            'avatar' => UploadedFile::fake()->image('profile.png'),
        ])
        ->assertRedirect();

    expect($studentItem->fresh()->equipped)->toBeFalse();
    expect(str_starts_with((string) $student->fresh()->avatar_url, '/media/student-avatars/'))->toBeTrue();
});

it('student can switch back to personal avatar without uploading again', function () {
    $user = User::factory()->create([
        'role' => 'student',
        'school_id' => null,
    ]);
    $student = Student::factory()->create([
        'user_id' => $user->id,
        'school_id' => null,
        'avatar_url' => '/media/student-avatars/pessoal.png',
    ]);

    $shopAvatar = ShopItem::factory()->avatar()->create([
        'image_url' => '/storage/shop-avatars/shop-avatar.png',
    ]);
    $studentItem = StudentItem::factory()->equipped()->create([
        'student_id' => $student->id,
        'item_id' => $shopAvatar->id,
    ]);

    $this->actingAs($user)
        ->post('/perfil/avatar/pessoal')
        ->assertRedirect();

    expect($studentItem->fresh()->equipped)->toBeFalse();

    $this->actingAs($user)
        ->get('/perfil')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('student.avatar_url', '/media/student-avatars/pessoal.png')
            ->where('student.avatar_personal_url', '/media/student-avatars/pessoal.png')
            ->where('student.avatar_equipped_url', null)
        );
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
