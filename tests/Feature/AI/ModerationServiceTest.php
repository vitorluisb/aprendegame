<?php

use App\Domain\Accounts\Models\Student;
use App\Domain\AI\Models\TutorMessage;
use App\Domain\AI\Services\ModerationService;

it('blocks messages containing forbidden topics', function () {
    $service = app(ModerationService::class);

    expect($service->isBlocked('Me fala sobre violência'))->toBeTrue();
    expect($service->isBlocked('O que são drogas?'))->toBeTrue();
    expect($service->isBlocked('Como funciona fotossíntese?'))->toBeFalse();
});

it('returns blocked reason for forbidden topics', function () {
    $service = app(ModerationService::class);

    expect($service->blockedReason('Fale sobre suicídio'))->toContain('suicídio');
    expect($service->blockedReason('Pergunta normal sobre matemática'))->toBeNull();
});

it('case insensitive blocking', function () {
    $service = app(ModerationService::class);

    expect($service->isBlocked('VIOLÊNCIA é errada'))->toBeTrue();
    expect($service->isBlocked('Violencia no mundo'))->toBeTrue();
});

it('child student has daily limit of 20 messages', function () {
    $student = Student::factory()->create([
        'birth_date' => now()->subYears(10)->toDateString(),
    ]);
    $service = app(ModerationService::class);

    expect($service->dailyLimitFor($student))->toBe(20);
});

it('teen student has daily limit of 40 messages', function () {
    $student = Student::factory()->create([
        'birth_date' => now()->subYears(15)->toDateString(),
    ]);
    $service = app(ModerationService::class);

    expect($service->dailyLimitFor($student))->toBe(40);
});

it('adult student has daily limit of 100 messages', function () {
    $student = Student::factory()->create([
        'birth_date' => now()->subYears(20)->toDateString(),
    ]);
    $service = app(ModerationService::class);

    expect($service->dailyLimitFor($student))->toBe(100);
});

it('detects when daily limit is reached', function () {
    $student = Student::factory()->create([
        'birth_date' => now()->subYears(10)->toDateString(),
    ]);
    $service = app(ModerationService::class);

    TutorMessage::factory()->count(20)->create([
        'student_id' => $student->id,
        'role' => 'student',
        'blocked' => false,
    ]);

    expect($service->hasReachedDailyLimit($student))->toBeTrue();
});

it('blocked messages do not count toward daily limit', function () {
    $student = Student::factory()->create([
        'birth_date' => now()->subYears(10)->toDateString(),
    ]);
    $service = app(ModerationService::class);

    TutorMessage::factory()->count(20)->create([
        'student_id' => $student->id,
        'role' => 'student',
        'blocked' => true,
    ]);

    expect($service->hasReachedDailyLimit($student))->toBeFalse();
});

it('returns correct remaining messages count', function () {
    $student = Student::factory()->create([
        'birth_date' => now()->subYears(10)->toDateString(),
    ]);
    $service = app(ModerationService::class);

    TutorMessage::factory()->count(5)->create([
        'student_id' => $student->id,
        'role' => 'student',
        'blocked' => false,
    ]);

    expect($service->remainingMessages($student))->toBe(15);
});
