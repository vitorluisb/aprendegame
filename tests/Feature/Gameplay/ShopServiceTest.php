<?php

use App\Domain\Accounts\Models\Student;
use App\Domain\Gameplay\Models\GemTransaction;
use App\Domain\Gameplay\Models\ShopItem;
use App\Domain\Gameplay\Models\StudentItem;
use App\Domain\Gameplay\Services\ShopService;
use Illuminate\Support\Facades\DB;

it('student can purchase an item when they have enough gems', function () {
    $student = Student::factory()->create();
    GemTransaction::factory()->create(['student_id' => $student->id, 'amount' => 200]);

    $item = ShopItem::factory()->create(['gem_price' => 100, 'type' => 'theme']);

    $service = app(ShopService::class);
    $studentItem = $service->purchase($student, $item);

    expect($studentItem)->toBeInstanceOf(StudentItem::class);
    expect($studentItem->student_id)->toBe($student->id);
    expect($studentItem->item_id)->toBe($item->id);
    expect($studentItem->equipped)->toBeFalse();
});

it('purchase auto equips avatar item', function () {
    $student = Student::factory()->create();
    GemTransaction::factory()->create(['student_id' => $student->id, 'amount' => 500]);
    $oldAvatar = ShopItem::factory()->avatar()->create();
    StudentItem::factory()->equipped()->create(['student_id' => $student->id, 'item_id' => $oldAvatar->id]);
    $newAvatar = ShopItem::factory()->avatar()->create(['gem_price' => 120]);

    $service = app(ShopService::class);
    $newStudentItem = $service->purchase($student, $newAvatar);

    expect($newStudentItem->equipped)->toBeTrue();
    expect(
        StudentItem::query()
            ->where('student_id', $student->id)
            ->where('item_id', $oldAvatar->id)
            ->first()
            ?->equipped
    )->toBeFalse();
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

it('equip treats legacy aliases as the same item type', function () {
    $student = Student::factory()->create();

    DB::table('shop_items')->insert([
        [
            'name' => 'Borda Legada',
            'type' => 'border',
            'slug' => 'borda-legada',
            'description' => null,
            'image_url' => null,
            'gem_price' => 10,
            'active' => true,
            'metadata' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'name' => 'Moldura Nova',
            'type' => 'frame',
            'slug' => 'moldura-nova',
            'description' => null,
            'image_url' => null,
            'gem_price' => 10,
            'active' => true,
            'metadata' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $legacyItem = ShopItem::query()->where('slug', 'borda-legada')->firstOrFail();
    $newItem = ShopItem::query()->where('slug', 'moldura-nova')->firstOrFail();

    StudentItem::factory()->create(['student_id' => $student->id, 'item_id' => $legacyItem->id, 'equipped' => true]);
    StudentItem::factory()->create(['student_id' => $student->id, 'item_id' => $newItem->id, 'equipped' => false]);

    $service = app(ShopService::class);
    $service->equip($student, $newItem);

    expect($legacyItem->fresh()->type)->toBe('frame');
    expect(StudentItem::query()->where('student_id', $student->id)->where('item_id', $newItem->id)->first()?->equipped)->toBeTrue();
    expect(StudentItem::query()->where('student_id', $student->id)->where('item_id', $legacyItem->id)->first()?->equipped)->toBeFalse();
});

it('buy life consumes neurons and restores one life', function () {
    $student = Student::factory()->create([
        'lives_current' => 2,
        'lives_max' => 5,
    ]);
    GemTransaction::factory()->create(['student_id' => $student->id, 'amount' => 100]);

    $service = app(ShopService::class);
    $updatedStudent = $service->buyLife($student);

    expect($updatedStudent->lives_current)->toBe(3);
    expect($updatedStudent->totalGems())->toBe(60);
});

it('buy life fails with insufficient neurons', function () {
    $student = Student::factory()->create([
        'lives_current' => 2,
        'lives_max' => 5,
    ]);
    GemTransaction::factory()->create(['student_id' => $student->id, 'amount' => 10]);

    $service = app(ShopService::class);

    expect(fn () => $service->buyLife($student))
        ->toThrow(RuntimeException::class, 'Neurons insuficientes para comprar uma vida.');
});

it('buy life fails when already at max lives', function () {
    $student = Student::factory()->create([
        'lives_current' => 5,
        'lives_max' => 5,
    ]);
    GemTransaction::factory()->create(['student_id' => $student->id, 'amount' => 100]);

    $service = app(ShopService::class);

    expect(fn () => $service->buyLife($student))
        ->toThrow(RuntimeException::class, 'Você já está com vidas máximas.');
});
