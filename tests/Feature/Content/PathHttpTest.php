<?php

use App\Domain\Accounts\Models\Student;
use App\Domain\Content\Models\Path;
use App\Domain\Content\Models\PathNode;
use App\Domain\Content\Models\StudentPathProgress;
use App\Domain\Gameplay\Models\Lesson;
use App\Domain\Gameplay\Models\LessonRun;
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

it('student cannot view an unpublished path directly', function () {
    $user = User::factory()->create(['role' => 'student']);
    $path = Path::factory()->create(['published' => false]);

    $this->actingAs($user)
        ->get("/trilhas/{$path->id}")
        ->assertNotFound();
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

it('path show returns gamified node payload for visual map', function () {
    $user = User::factory()->create(['role' => 'student', 'school_id' => null]);
    $path = Path::factory()->published()->create();
    $node = PathNode::factory()->forPath($path)->create(['order' => 1, 'published' => true]);
    Lesson::factory()->published()->forNode($node)->create();

    $this->actingAs($user)
        ->get("/trilhas/{$path->id}")
        ->assertInertia(fn (Assert $page) => $page
            ->has('path.total_xp')
            ->has('path.earned_xp')
            ->has('path.current_node_order')
            ->has('nodes.0.status')
            ->has('nodes.0.progress_questions')
            ->has('nodes.0.question_target')
            ->has('nodes.0.xp_total')
            ->has('nodes.0.xp_earned')
            ->has('nodes.0.stars')
            ->has('nodes.0.primary_lesson_id')
        );
});

it('path show marks node as completed and unlocks next node after approved run', function () {
    $user = User::factory()->create(['role' => 'student', 'school_id' => null]);
    $student = Student::factory()->create(['user_id' => $user->id, 'school_id' => null]);
    $path = Path::factory()->published()->create();
    $node1 = PathNode::factory()->forPath($path)->create(['order' => 1, 'published' => true, 'node_type' => 'lesson']);
    $node2 = PathNode::factory()->forPath($path)->create(['order' => 2, 'published' => true, 'node_type' => 'lesson']);
    $lesson1 = Lesson::factory()->published()->forNode($node1)->create();
    Lesson::factory()->published()->forNode($node2)->create();

    LessonRun::factory()->for($student)->for($lesson1)->finished(85)->create([
        'total_count' => 10,
        'xp_earned' => 80,
    ]);

    $this->actingAs($user)
        ->get("/trilhas/{$path->id}")
        ->assertInertia(fn (Assert $page) => $page
            ->where('nodes.0.status', 'completed')
            ->where('nodes.0.progress_questions', 10)
            ->where('nodes.1.status', 'unlocked')
            ->where('path.current_node_order', 2)
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

it('path show keeps all nodes locked when path prerequisite is not completed', function () {
    $user = User::factory()->create(['role' => 'student', 'school_id' => null]);
    Student::factory()->create(['user_id' => $user->id, 'school_id' => null]);

    $requiredPath = Path::factory()->published()->create();
    $lockedPath = Path::factory()->published()->create(['unlocks_after_path_id' => $requiredPath->id]);
    $node = PathNode::factory()->forPath($lockedPath)->create(['order' => 1, 'published' => true]);
    Lesson::factory()->published()->forNode($node)->create();

    $this->actingAs($user)
        ->get("/trilhas/{$lockedPath->id}")
        ->assertInertia(fn (Assert $page) => $page
            ->where('nodes.0.status', 'locked')
            ->where('path.current_node_order', 1)
        );
});

it('path show unlocks nodes when prerequisite path progress is completed', function () {
    $user = User::factory()->create(['role' => 'student', 'school_id' => null]);
    $student = Student::factory()->create(['user_id' => $user->id, 'school_id' => null]);

    $requiredPath = Path::factory()->published()->create();
    StudentPathProgress::query()->create([
        'student_id' => $student->id,
        'path_id' => $requiredPath->id,
        'status' => 'completed',
        'current_node_order' => 1,
        'xp_earned' => 100,
        'xp_total' => 100,
        'stars' => 3,
        'accuracy_percent' => 100,
        'attempts_count' => 10,
    ]);

    $path = Path::factory()->published()->create(['unlocks_after_path_id' => $requiredPath->id]);
    $node = PathNode::factory()->forPath($path)->create(['order' => 1, 'published' => true]);
    Lesson::factory()->published()->forNode($node)->create();

    $this->actingAs($user)
        ->get("/trilhas/{$path->id}")
        ->assertInertia(fn (Assert $page) => $page
            ->where('nodes.0.status', 'unlocked')
        );
});
