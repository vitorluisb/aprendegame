<?php

use App\Domain\Accounts\Models\Student;
use App\Domain\Content\Models\Path;
use App\Domain\Content\Models\PathNode;
use App\Domain\Content\Models\StudentPathProgress;
use App\Domain\Content\Services\StudentPathProgressService;
use App\Domain\Gameplay\Models\Lesson;
use App\Domain\Gameplay\Models\LessonRun;

it('summarizes and persists student path progress from lesson runs', function () {
    $student = Student::factory()->create();
    $path = Path::factory()->create(['unlocks_after_path_id' => null]);
    $node1 = PathNode::factory()->forPath($path)->create([
        'order' => 1,
        'published' => true,
        'xp_reward' => 80,
    ]);
    $node2 = PathNode::factory()->forPath($path)->create([
        'order' => 2,
        'published' => true,
        'xp_reward' => 120,
    ]);
    $lesson1 = Lesson::factory()->forNode($node1)->published()->create();
    $lesson2 = Lesson::factory()->forNode($node2)->published()->create();

    LessonRun::factory()->for($student)->for($lesson1)->finished(80)->create([
        'total_count' => 10,
        'correct_count' => 8,
    ]);
    LessonRun::factory()->for($student)->for($lesson2)->finished(50)->create([
        'total_count' => 5,
        'correct_count' => 2,
    ]);

    $service = app(StudentPathProgressService::class);
    $progress = $service->sync($path, $student);

    expect($progress)->toBeInstanceOf(StudentPathProgress::class)
        ->and($progress->status)->toBe('in_progress')
        ->and($progress->xp_total)->toBe(200)
        ->and($progress->xp_earned)->toBe(140)
        ->and($progress->current_node_order)->toBe(2)
        ->and($progress->attempts_count)->toBe(15)
        ->and($progress->accuracy_percent)->toBe(66.67)
        ->and($progress->stars)->toBe(1);
});

it('marks progress as locked when prerequisite path is not completed', function () {
    $student = Student::factory()->create();
    $requiredPath = Path::factory()->create();
    $path = Path::factory()->create(['unlocks_after_path_id' => $requiredPath->id]);
    $node = PathNode::factory()->forPath($path)->create([
        'order' => 1,
        'published' => true,
    ]);
    Lesson::factory()->forNode($node)->published()->create();

    $service = app(StudentPathProgressService::class);
    $progress = $service->sync($path, $student);

    expect($progress->status)->toBe('locked');
});
