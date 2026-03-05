<?php

use App\Domain\Accounts\Models\Student;
use App\Domain\Content\Models\Grade;
use App\Domain\Content\Models\Path;
use App\Domain\Content\Models\PathNode;
use App\Domain\Content\Models\Subject;
use App\Domain\Content\Services\PathProgressService;
use Illuminate\Database\QueryException;

it('only one path per grade and subject', function () {
    $grade = Grade::factory()->create();
    $subject = Subject::factory()->create();

    Path::factory()->create(['grade_id' => $grade->id, 'subject_id' => $subject->id]);

    expect(fn () => Path::factory()->create(['grade_id' => $grade->id, 'subject_id' => $subject->id]))
        ->toThrow(QueryException::class);
});

it('first node is always unlocked', function () {
    $path = Path::factory()->create();
    $node = PathNode::factory()->forPath($path)->create(['order' => 1]);
    $student = Student::factory()->create();

    $service = app(PathProgressService::class);

    expect($service->getNodeStatus($node, $student))->toBe('unlocked');
});

it('second node is locked without completing first', function () {
    $path = Path::factory()->create();
    PathNode::factory()->forPath($path)->create(['order' => 1]);
    $node2 = PathNode::factory()->forPath($path)->create(['order' => 2]);
    $student = Student::factory()->create();

    $service = app(PathProgressService::class);

    expect($service->getNodeStatus($node2, $student))->toBe('locked');
});

it('cascade delete destroys nodes when path deleted', function () {
    $path = Path::factory()->create();
    PathNode::factory()->forPath($path)->create(['order' => 1]);
    PathNode::factory()->forPath($path)->create(['order' => 2]);
    PathNode::factory()->forPath($path)->create(['order' => 3]);

    $pathId = $path->id;
    $path->delete();

    expect(PathNode::where('path_id', $pathId)->count())->toBe(0);
});

it('path nodes are ordered by order column', function () {
    $path = Path::factory()->create();
    PathNode::factory()->forPath($path)->create(['order' => 3]);
    PathNode::factory()->forPath($path)->create(['order' => 1]);
    PathNode::factory()->forPath($path)->create(['order' => 2]);

    $orders = $path->nodes->pluck('order')->toArray();

    expect($orders)->toBe([1, 2, 3]);
});

it('path node cannot have duplicate order within same path', function () {
    $path = Path::factory()->create();
    PathNode::factory()->forPath($path)->create(['order' => 1]);

    expect(fn () => PathNode::factory()->forPath($path)->create(['order' => 1]))
        ->toThrow(QueryException::class);
});

it('skill_ids is cast to array', function () {
    $path = Path::factory()->create();
    $node = PathNode::factory()->forPath($path)->create(['order' => 1, 'skill_ids' => [1, 2, 3]]);

    expect($node->fresh()->skill_ids)->toBe([1, 2, 3]);
});
