<?php

use App\Domain\Accounts\Models\Student;
use App\Domain\Content\Models\BnccSkill;
use App\Domain\Gameplay\Models\ShopItem;
use App\Domain\Gameplay\Services\MasteryService;
use App\Domain\Gameplay\Services\ShopService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

it('getDueReviews result is cached after first call', function () {
    $student = Student::factory()->create();
    $service = app(MasteryService::class);

    Cache::forget("mastery:due:{$student->id}");

    $queries = 0;
    DB::listen(function () use (&$queries) {
        $queries++;
    });

    $service->getDueReviews($student);
    $firstQueryCount = $queries;

    $queries = 0;
    $service->getDueReviews($student);

    // Second call should not hit the database
    expect($queries)->toBe(0);
    expect($firstQueryCount)->toBeGreaterThan(0);
});

it('mastery cache is invalidated after update', function () {
    $student = Student::factory()->create();
    $skill = BnccSkill::factory()->create();
    $service = app(MasteryService::class);

    // Prime the cache
    $service->getDueReviews($student);
    expect(Cache::has("mastery:due:{$student->id}"))->toBeTrue();

    // Update should bust the cache
    $service->update($student, $skill->id, true);
    expect(Cache::has("mastery:due:{$student->id}"))->toBeFalse();
});

it('shop active items are cached', function () {
    ShopItem::factory()->count(3)->create(['active' => true]);

    Cache::forget('shop:active_items');

    $service = app(ShopService::class);
    $service->getActiveItems();

    expect(Cache::has('shop:active_items'))->toBeTrue();
});

it('shop cache returns only active items', function () {
    ShopItem::factory()->count(2)->create(['active' => true]);
    ShopItem::factory()->inactive()->create();

    Cache::forget('shop:active_items');

    $service = app(ShopService::class);
    $items = $service->getActiveItems();

    expect($items->every(fn ($i) => $i->active))->toBeTrue();
    expect($items)->toHaveCount(2);
});

it('shop cache can be invalidated', function () {
    ShopItem::factory()->create(['active' => true]);
    Cache::forget('shop:active_items');

    $service = app(ShopService::class);
    $service->getActiveItems();
    expect(Cache::has('shop:active_items'))->toBeTrue();

    $service->forgetItemsCache();
    expect(Cache::has('shop:active_items'))->toBeFalse();
});
