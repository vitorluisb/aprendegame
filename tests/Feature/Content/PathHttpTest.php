<?php

use App\Domain\Content\Models\Path;
use App\Domain\Content\Models\PathNode;
use App\Domain\Accounts\Models\Student;
use App\Domain\Gameplay\Models\Lesson;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('redirects guests from /trilhas', function () {
    $this->get('/trilhas')->assertRedirect('/login');
});

it('student can access trilhas index', function () {
    $user = User::factory()->create(['role' => 'student']);

    $this->actingAs($user)
        ->get('/trilhas')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page->component('Paths/Index')->has('paths'));
});

it('trilhas index only shows published paths', function () {
    $user = User::factory()->create(['role' => 'student']);

    Path::factory()->published()->create();
    Path::factory()->create(); // unpublished

    $this->actingAs($user)
        ->get('/trilhas')
        ->assertInertia(fn (Assert $page) => $page->has('paths', 1));
});

it('trilhas index returns path with grade and subject info', function () {
    $user = User::factory()->create(['role' => 'student']);
    Path::factory()->published()->create();

    $this->actingAs($user)
        ->get('/trilhas')
        ->assertInertia(fn (Assert $page) => $page
            ->has('paths.0.id')
            ->has('paths.0.title')
            ->has('paths.0.path_type')
            ->has('paths.0.grade')
            ->has('paths.0.subject')
            ->has('paths.0.node_count')
        );
});

it('trilhas index groups enem paths correctly', function () {
    $user = User::factory()->create(['role' => 'student']);
    Path::factory()->published()->enem()->create();
    Path::factory()->published()->create(); // regular

    $this->actingAs($user)
        ->get('/trilhas')
        ->assertInertia(fn (Assert $page) => $page->has('paths', 2));
});

it('student can view a published path', function () {
    $user = User::factory()->create(['role' => 'student']);
    $path = Path::factory()->published()->create();

    $this->actingAs($user)
        ->get("/trilhas/{$path->id}")
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Paths/Show')
            ->has('path.id')
            ->has('nodes')
        );
});

it('path show only includes published nodes', function () {
    $user = User::factory()->create(['role' => 'student']);
    $path = Path::factory()->published()->create();
    PathNode::factory()->forPath($path)->create(['order' => 1, 'published' => true]);
    PathNode::factory()->forPath($path)->create(['order' => 2, 'published' => false]);

    $this->actingAs($user)
        ->get("/trilhas/{$path->id}")
        ->assertInertia(fn (Assert $page) => $page->has('nodes', 1));
});

it('path show includes lessons for each node', function () {
    $user = User::factory()->create(['role' => 'student']);
    $path = Path::factory()->published()->create();
    $node = PathNode::factory()->forPath($path)->create(['order' => 1, 'published' => true]);
    Lesson::factory()->published()->forNode($node)->create();

    $this->actingAs($user)
        ->get("/trilhas/{$path->id}")
        ->assertInertia(fn (Assert $page) => $page
            ->has('nodes.0.lessons', 1)
            ->has('nodes.0.lessons.0.id')
            ->has('nodes.0.lessons.0.title')
        );
});

it('trilhas are filtered by student current grade when set', function () {
    $user = User::factory()->create(['role' => 'student', 'school_id' => null]);

    $matchingPath = Path::factory()->published()->create();
    $otherPath = Path::factory()->published()->create();

    Student::factory()->create([
        'user_id' => $user->id,
        'school_id' => null,
        'grade_id' => $matchingPath->grade_id,
    ]);

    expect($matchingPath->grade_id)->not->toBe($otherPath->grade_id);

    $this->actingAs($user)
        ->get('/trilhas')
        ->assertInertia(fn (Assert $page) => $page
            ->has('paths', 1)
            ->where('paths.0.id', $matchingPath->id)
            ->where('grade_filter', $matchingPath->grade_id)
        );
});

it('trilhas are filtered by grade when user has student profile even with non-student role', function () {
    $user = User::factory()->create(['role' => 'guardian', 'school_id' => null]);

    $matchingPath = Path::factory()->published()->create();
    $otherPath = Path::factory()->published()->create();

    Student::factory()->create([
        'user_id' => $user->id,
        'school_id' => null,
        'grade_id' => $matchingPath->grade_id,
    ]);

    expect($matchingPath->grade_id)->not->toBe($otherPath->grade_id);

    $this->actingAs($user)
        ->get('/trilhas')
        ->assertInertia(fn (Assert $page) => $page
            ->has('paths', 1)
            ->where('paths.0.id', $matchingPath->id)
            ->where('grade_filter', $matchingPath->grade_id)
        );
});
