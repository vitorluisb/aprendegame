<?php

use App\Domain\Accounts\Models\School;
use App\Domain\Accounts\Models\Student;
use App\Domain\Content\Models\Grade;
use App\Domain\Gameplay\Models\ShopItem;
use App\Domain\Gameplay\Models\StudentItem;
use App\Domain\Gameplay\Services\LeagueService;
use App\Models\User;
use Illuminate\Support\Facades\Redis;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Redis::flushdb();
});

it('student can view weekly ranking page', function () {
    $school = School::factory()->create();
    $user = User::factory()->create([
        'role' => 'student',
        'school_id' => $school->id,
    ]);
    $me = Student::factory()->create([
        'user_id' => $user->id,
        'school_id' => $school->id,
        'name' => 'Aluno Logado',
    ]);
    $otherTop = Student::factory()->create(['school_id' => $school->id, 'name' => 'Top 1']);
    $otherLow = Student::factory()->create(['school_id' => $school->id, 'name' => 'Top 3']);

    $leagueService = app(LeagueService::class);
    $leagueService->addXP($otherTop, 300);
    $leagueService->addXP($me, 200);
    $leagueService->addXP($otherLow, 100);

    $this->actingAs($user)
        ->get('/ranking')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('League/Index')
            ->where('scope', 'school')
            ->where('my_position.rank', 2)
            ->where('my_position.weekly_xp', 200)
            ->has('entries', 3)
            ->where('entries.1.student.name', 'Aluno Logado')
            ->where('entries.1.is_me', true)
        );
});

it('non-student is redirected from ranking page', function () {
    $guardian = User::factory()->create(['role' => 'guardian']);

    $this->actingAs($guardian)
        ->get('/ranking')
        ->assertRedirect('/dashboard');
});

it('standalone student can view global ranking page', function () {
    $user = User::factory()->create([
        'role' => 'student',
        'school_id' => null,
    ]);
    $student = Student::factory()->create([
        'user_id' => $user->id,
        'school_id' => null,
    ]);
    app(LeagueService::class)->addXP($student, 90);

    $this->actingAs($user)
        ->get('/ranking')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('League/Index')
            ->where('scope', 'global')
            ->where('my_position.weekly_xp', 90)
        );
});

it('user with student profile can view weekly ranking page even with non-student role', function () {
    $user = User::factory()->create([
        'role' => 'teacher',
        'school_id' => null,
    ]);
    $student = Student::factory()->create([
        'user_id' => $user->id,
        'school_id' => null,
    ]);
    app(LeagueService::class)->addXP($student, 90);

    $this->actingAs($user)
        ->get('/ranking')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('League/Index')
            ->where('scope', 'global')
            ->where('my_position.weekly_xp', 90)
        );
});

it('student with zero weekly xp starts in bronze league', function () {
    $user = User::factory()->create([
        'role' => 'student',
        'school_id' => null,
    ]);

    Student::factory()->create([
        'user_id' => $user->id,
        'school_id' => null,
    ]);

    $this->actingAs($user)
        ->get('/ranking')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('League/Index')
            ->where('my_position.weekly_xp', 0)
            ->where('my_position.league', 'bronze')
        );
});

it('ranking is filtered by student grade when grade is defined', function () {
    $school = School::factory()->create();
    $gradeA = Grade::factory()->create();
    $gradeB = Grade::factory()->create();

    $user = User::factory()->create([
        'role' => 'student',
        'school_id' => $school->id,
    ]);
    $me = Student::factory()->create([
        'user_id' => $user->id,
        'school_id' => $school->id,
        'grade_id' => $gradeA->id,
        'name' => 'Aluno Série A',
    ]);
    $sameGrade = Student::factory()->create([
        'school_id' => $school->id,
        'grade_id' => $gradeA->id,
        'name' => 'Outro Série A',
    ]);
    $otherGrade = Student::factory()->create([
        'school_id' => $school->id,
        'grade_id' => $gradeB->id,
        'name' => 'Outro Série B',
    ]);

    $leagueService = app(LeagueService::class);
    $leagueService->addXP($sameGrade, 300);
    $leagueService->addXP($otherGrade, 500);
    $leagueService->addXP($me, 200);

    $this->actingAs($user)
        ->get('/ranking')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('grade_filter_name', $gradeA->name)
            ->has('entries', 2)
            ->where('entries.0.student.name', 'Outro Série A')
            ->where('entries.1.student.name', 'Aluno Série A')
        );
});

it('ranking uses equipped shop avatar when available', function () {
    $user = User::factory()->create([
        'role' => 'student',
        'school_id' => null,
    ]);
    $student = Student::factory()->create([
        'user_id' => $user->id,
        'school_id' => null,
        'name' => 'Avatar Gamer',
    ]);
    $avatarItem = ShopItem::factory()->avatar()->create([
        'image_url' => '/storage/shop-avatars/avatar-gamer.png',
    ]);
    StudentItem::factory()->equipped()->create([
        'student_id' => $student->id,
        'item_id' => $avatarItem->id,
    ]);
    app(LeagueService::class)->addXP($student, 70);

    $this->actingAs($user)
        ->get('/ranking')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('entries.0.student.name', 'Avatar Gamer')
            ->where('entries.0.student.avatar_url', '/media/shop-avatars/avatar-gamer.png')
        );
});
