<?php

use App\Domain\Accounts\Models\Student;
use App\Domain\Gameplay\Models\Streak;
use App\Domain\Gameplay\Models\XpTransaction;
use App\Domain\Gameplay\Services\StreakService;
use App\Jobs\AwardXP;
use App\Jobs\UpdateStreak;
use App\Models\User;
use Carbon\Carbon;

it('streak increments on consecutive days', function () {
    $student = Student::factory()->create();
    $service = app(StreakService::class);

    Carbon::setTestNow(today());
    $service->update($student);
    expect($student->streak->current)->toBe(1);

    Carbon::setTestNow(today()->addDay());
    $service->update($student);
    expect($student->streak->fresh()->current)->toBe(2);
});

it('streak does not increment twice on same day', function () {
    $student = Student::factory()->create();
    $service = app(StreakService::class);

    Carbon::setTestNow(today());
    $service->update($student);
    $service->update($student);

    expect($student->streak->fresh()->current)->toBe(1);
});

it('streak resets after missing a day without freeze', function () {
    $student = Student::factory()->create();
    Streak::factory()->create([
        'student_id' => $student->id,
        'current' => 10,
        'best' => 10,
        'last_activity_date' => today()->subDays(2),
        'freeze_used_at' => now()->subDays(3), // freeze já usado essa semana
    ]);

    $service = app(StreakService::class);
    $service->update($student);

    expect($student->streak->fresh()->current)->toBe(1);
});

it('streak best is never less than current', function () {
    $student = Student::factory()->create();
    $service = app(StreakService::class);

    Carbon::setTestNow(today());
    $service->update($student);
    Carbon::setTestNow(today()->addDay());
    $service->update($student);
    Carbon::setTestNow(today()->addDays(2));
    $streak = $service->update($student);

    expect($streak->best)->toBeGreaterThan(0);
    expect($streak->best)->toBeGreaterThan($streak->current - 1);
});

it('freeze is not available when freeze_used_at is null', function () {
    $student = Student::factory()->create();
    Streak::factory()->create([
        'student_id' => $student->id,
        'current' => 5,
        'best' => 5,
        'last_activity_date' => today()->subDays(2),
        'freeze_used_at' => null,
    ]);

    $service = app(StreakService::class);
    $service->update($student);

    expect($student->streak->fresh()->current)->toBe(1);
});

it('student total xp is sum of all transactions', function () {
    $student = Student::factory()->create();
    XpTransaction::factory()->count(5)->create([
        'student_id' => $student->id,
        'amount' => 20,
    ]);

    $total = (int) XpTransaction::where('student_id', $student->id)->sum('amount');
    expect($total)->toBe(100);
});

it('award xp job creates xp transaction', function () {
    $student = Student::factory()->create();

    $job = new AwardXP($student->id, 25, 'lesson', 1);
    $job->handle();

    $tx = XpTransaction::where('student_id', $student->id)->first();
    expect($tx)->not->toBeNull();
    expect($tx->amount)->toBe(25);
    expect($tx->reason)->toBe('lesson');
});

it('update streak job creates streak for student', function () {
    $student = Student::factory()->create();

    $job = new UpdateStreak($student->id);
    $job->handle();

    expect($student->streak()->exists())->toBeTrue();
    expect($student->streak->current)->toBe(1);
});

it('filament admin can access gamification resources', function (string $route) {
    $admin = User::factory()->create(['role' => 'super_admin']);

    $this->actingAs($admin)->get($route)->assertSuccessful();
})->with([
    '/admin/badges',
    '/admin/badges/create',
    '/admin/daily-missions',
    '/admin/daily-missions/create',
    '/admin/shop-items',
    '/admin/shop-items/create',
]);
