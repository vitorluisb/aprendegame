<?php

use App\Domain\Accounts\Models\Student;
use App\Domain\Gameplay\Models\GemTransaction;
use App\Domain\Gameplay\Models\ShopItem;
use App\Domain\Gameplay\Models\StudentItem;
use App\Domain\Gameplay\Services\ShopService;

it('student can purchase an item when they have enough gems', function () {
    $student = Student::factory()->create();
    GemTransaction::factory()->create(['student_id' => $student->id, 'amount' => 200]);

    $item = ShopItem::factory()->create(['gem_price' => 100]);

    $service = app(ShopService::class);
    $studentItem = $service->purchase($student, $item);

    expect($studentItem)->toBeInstanceOf(StudentItem::class);
    expect($studentItem->student_id)->toBe($student->id);
    expect($studentItem->item_id)->toBe($item->id);
    expect($studentItem->equipped)->toBeFalse();
});

it('purchase deducts gems from student balance', function () {
    $student = Student::factory()->create();
    GemTransaction::factory()->create(['student_id' => $student->id, 'amount' => 200]);

    $item = ShopItem::factory()->create(['gem_price' => 80]);
    $service = app(ShopService::class);
    $service->purchase($student, $item);

    expect($student->totalGems())->toBe(120);
});

it('purchase fails when student has insufficient gems', function () {
    $student = Student::factory()->create();
    GemTransaction::factory()->create(['student_id' => $student->id, 'amount' => 50]);

    $item = ShopItem::factory()->create(['gem_price' => 100]);
    $service = app(ShopService::class);

    expect(fn () => $service->purchase($student, $item))
        ->toThrow(RuntimeException::class, 'Gemas insuficientes.');
});

it('purchase fails when item is inactive', function () {
    $student = Student::factory()->create();
    GemTransaction::factory()->create(['student_id' => $student->id, 'amount' => 500]);

    $item = ShopItem::factory()->inactive()->create();
    $service = app(ShopService::class);

    expect(fn () => $service->purchase($student, $item))
        ->toThrow(RuntimeException::class, 'Item não está disponível na loja.');
});

it('purchase fails when student already owns the item', function () {
    $student = Student::factory()->create();
    GemTransaction::factory()->create(['student_id' => $student->id, 'amount' => 500]);

    $item = ShopItem::factory()->create(['gem_price' => 50]);
    $service = app(ShopService::class);
    $service->purchase($student, $item);

    // Replenish gems and try again
    GemTransaction::factory()->create(['student_id' => $student->id, 'amount' => 500]);

    expect(fn () => $service->purchase($student, $item))
        ->toThrow(RuntimeException::class, 'Aluno já possui este item.');
});

it('equip marks item as equipped and unequips others of same type', function () {
    $student = Student::factory()->create();

    $item1 = ShopItem::factory()->avatar()->create(['gem_price' => 10]);
    $item2 = ShopItem::factory()->avatar()->create(['gem_price' => 10]);

    StudentItem::factory()->create(['student_id' => $student->id, 'item_id' => $item1->id, 'equipped' => true]);
    StudentItem::factory()->create(['student_id' => $student->id, 'item_id' => $item2->id, 'equipped' => false]);

    $service = app(ShopService::class);
    $service->equip($student, $item2);

    expect(StudentItem::where('student_id', $student->id)->where('item_id', $item2->id)->first()->equipped)->toBeTrue();
    expect(StudentItem::where('student_id', $student->id)->where('item_id', $item1->id)->first()->equipped)->toBeFalse();
});

it('award gems creates a gem transaction', function () {
    $student = Student::factory()->create();
    $service = app(ShopService::class);

    $service->awardGems($student, 50, 'lesson');

    expect($student->totalGems())->toBe(50);
    expect(GemTransaction::where('student_id', $student->id)->count())->toBe(1);
});
