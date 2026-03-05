<?php

use App\Domain\Accounts\Models\Student;
use App\Domain\Content\Models\BnccSkill;
use App\Domain\Gameplay\Models\Mastery;
use App\Domain\Gameplay\Services\MasteryService;
use App\Jobs\UpdateMastery;

it('mastery score increases on correct answer', function () {
    $student = Student::factory()->create();
    $skill = BnccSkill::factory()->create();
    $service = app(MasteryService::class);

    $mastery = $service->update($student, $skill->id, correct: true);

    expect($mastery->mastery_score)->toBe(10);
    expect($mastery->interval_days)->toBe(1);
    expect($mastery->next_review_at)->not->toBeNull();
});

it('mastery score decreases on wrong answer', function () {
    $student = Student::factory()->create();
    $skill = BnccSkill::factory()->create();
    Mastery::factory()->create([
        'student_id' => $student->id,
        'skill_id' => $skill->id,
        'mastery_score' => 50,
        'interval_days' => 14,
        'consecutive_correct' => 3,
    ]);

    $service = app(MasteryService::class);
    $mastery = $service->update($student, $skill->id, correct: false);

    expect($mastery->mastery_score)->toBe(35);
    expect($mastery->interval_days)->toBe(1);
    expect($mastery->consecutive_correct)->toBe(0);
});

it('interval advances after 2 consecutive correct answers', function () {
    $student = Student::factory()->create();
    $skill = BnccSkill::factory()->create();
    $service = app(MasteryService::class);

    $service->update($student, $skill->id, correct: true); // 1 consecutivo
    $mastery = $service->update($student, $skill->id, correct: true); // 2 consecutivos

    expect($mastery->interval_days)->toBe(3); // 1 → 3
});

it('interval does not advance with only 1 consecutive correct', function () {
    $student = Student::factory()->create();
    $skill = BnccSkill::factory()->create();
    $service = app(MasteryService::class);

    $mastery = $service->update($student, $skill->id, correct: true); // 1 consecutivo

    expect($mastery->interval_days)->toBe(1); // permanece em 1
});

it('mastery score does not exceed 100', function () {
    $student = Student::factory()->create();
    $skill = BnccSkill::factory()->create();
    Mastery::factory()->create([
        'student_id' => $student->id,
        'skill_id' => $skill->id,
        'mastery_score' => 95,
        'interval_days' => 30,
        'consecutive_correct' => 10,
    ]);

    $service = app(MasteryService::class);
    $mastery = $service->update($student, $skill->id, correct: true);

    expect($mastery->mastery_score)->toBe(100);
});

it('mastery score does not go below 0', function () {
    $student = Student::factory()->create();
    $skill = BnccSkill::factory()->create();
    Mastery::factory()->create([
        'student_id' => $student->id,
        'skill_id' => $skill->id,
        'mastery_score' => 5,
        'interval_days' => 1,
        'consecutive_correct' => 0,
    ]);

    $service = app(MasteryService::class);
    $mastery = $service->update($student, $skill->id, correct: false);

    expect($mastery->mastery_score)->toBe(0);
});

it('getDueReviews returns only overdue skills below mastery threshold', function () {
    $student = Student::factory()->create();

    // Skill vencida (deveria ter sido revisada ontem)
    $due = Mastery::factory()->due()->create(['student_id' => $student->id]);

    // Skill não vencida (próxima revisão amanhã)
    Mastery::factory()->create([
        'student_id' => $student->id,
        'next_review_at' => now()->addDay(),
    ]);

    // Skill dominada (score >= 90) — não deve aparecer mesmo vencida
    Mastery::factory()->mastered()->create(['student_id' => $student->id]);

    $service = app(MasteryService::class);
    $reviews = $service->getDueReviews($student);

    expect($reviews)->toHaveCount(1);
    expect($reviews->first()->id)->toBe($due->id);
});

it('update mastery job calls mastery service for each skill', function () {
    $student = Student::factory()->create();
    $skill1 = BnccSkill::factory()->create();
    $skill2 = BnccSkill::factory()->create();

    $job = new UpdateMastery($student->id, [$skill1->id, $skill2->id]);
    $job->handle();

    expect(Mastery::where('student_id', $student->id)->count())->toBe(2);
});
