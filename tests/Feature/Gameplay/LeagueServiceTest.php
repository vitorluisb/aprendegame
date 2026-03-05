<?php

use App\Domain\Accounts\Models\School;
use App\Domain\Accounts\Models\Student;
use App\Domain\Gameplay\Models\LeagueSnapshot;
use App\Domain\Gameplay\Services\LeagueService;
use Illuminate\Support\Facades\Redis;

beforeEach(function () {
    // Limpa o banco Redis entre testes para evitar acúmulo de dados
    Redis::flushdb();
});

it('redis leaderboard returns correct order', function () {
    $school = School::factory()->create();
    $students = Student::factory()->count(3)->create(['school_id' => $school->id]);
    $service = app(LeagueService::class);

    $service->addXP($students[0], 100);
    $service->addXP($students[1], 200);
    $service->addXP($students[2], 150);

    $leaderboard = $service->getLeaderboard($school->id);

    // phpredis retorna chaves como inteiros quando os membros são numéricos
    expect((int) array_key_first($leaderboard))->toBe($students[1]->id);
});

it('leaderboard respects limit parameter', function () {
    $school = School::factory()->create();
    Student::factory()->count(5)->create(['school_id' => $school->id])->each(function ($student) use ($school) {
        app(LeagueService::class)->addXP($student, fake()->numberBetween(10, 100));
    });

    $leaderboard = app(LeagueService::class)->getLeaderboard($school->id, 3);

    expect(count($leaderboard))->toBe(3);
});

it('weekly snapshot persists data to mysql', function () {
    $school = School::factory()->create();
    $student = Student::factory()->create(['school_id' => $school->id]);
    $service = app(LeagueService::class);

    $service->addXP($student, 500);
    $service->snapshotAndReset();

    expect(LeagueSnapshot::where('student_id', $student->id)->exists())->toBeTrue();
});

it('snapshot stores correct xp and rank', function () {
    $school = School::factory()->create();
    $s1 = Student::factory()->create(['school_id' => $school->id]);
    $s2 = Student::factory()->create(['school_id' => $school->id]);
    $service = app(LeagueService::class);

    $service->addXP($s1, 300);
    $service->addXP($s2, 100);
    $service->snapshotAndReset();

    $snap = LeagueSnapshot::where('student_id', $s1->id)->first();
    expect($snap->weekly_xp)->toBe(300);
    expect($snap->rank_position)->toBe(1);
});

it('redis key is cleared after snapshot', function () {
    $school = School::factory()->create();
    $student = Student::factory()->create(['school_id' => $school->id]);
    $service = app(LeagueService::class);

    $service->addXP($student, 100);
    $service->snapshotAndReset();

    $leaderboard = $service->getLeaderboard($school->id);
    expect($leaderboard)->toBeEmpty();
});

it('top ranked student gets platinum league', function () {
    $school = School::factory()->create();
    $students = Student::factory()->count(5)->create(['school_id' => $school->id]);
    $service = app(LeagueService::class);

    foreach ($students as $i => $student) {
        $service->addXP($student, (5 - $i) * 100);
    }

    $service->snapshotAndReset();

    $top = LeagueSnapshot::where('student_id', $students[0]->id)->first();
    expect($top->league)->toBe('platinum');
    expect($top->rank_position)->toBe(1);
});

it('addXP accumulates when called multiple times', function () {
    $school = School::factory()->create();
    $student = Student::factory()->create(['school_id' => $school->id]);
    $service = app(LeagueService::class);

    $service->addXP($student, 50);
    $service->addXP($student, 30);

    $leaderboard = $service->getLeaderboard($school->id);
    expect((int) $leaderboard[$student->id])->toBe(80);
});
