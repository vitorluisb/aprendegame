<?php

use App\Domain\Accounts\Models\Student;
use App\Domain\Content\Models\Path;
use App\Domain\Content\Models\StudentPathProgress;
use App\Domain\Content\Services\PathUnlockService;

it('unlocks path without prerequisite', function () {
    $student = Student::factory()->create();
    $path = Path::factory()->create(['unlocks_after_path_id' => null]);

    $service = app(PathUnlockService::class);

    expect($service->isUnlockedForStudent($path, $student))->toBeTrue();
});

it('keeps path locked when prerequisite is not completed', function () {
    $student = Student::factory()->create();
    $requiredPath = Path::factory()->create();
    $path = Path::factory()->create(['unlocks_after_path_id' => $requiredPath->id]);

    $service = app(PathUnlockService::class);

    expect($service->isUnlockedForStudent($path, $student))->toBeFalse();
});

it('unlocks path when prerequisite has completed aggregated progress', function () {
    $student = Student::factory()->create();
    $requiredPath = Path::factory()->create();
    $path = Path::factory()->create(['unlocks_after_path_id' => $requiredPath->id]);

    StudentPathProgress::query()->create([
        'student_id' => $student->id,
        'path_id' => $requiredPath->id,
        'status' => 'completed',
        'current_node_order' => 1,
        'xp_earned' => 100,
        'xp_total' => 100,
        'stars' => 3,
        'accuracy_percent' => 90,
        'attempts_count' => 10,
    ]);

    $service = app(PathUnlockService::class);

    expect($service->isUnlockedForStudent($path, $student))->toBeTrue();
});
