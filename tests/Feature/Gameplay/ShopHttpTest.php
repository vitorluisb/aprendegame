<?php

use App\Domain\Accounts\Models\School;
use App\Domain\Accounts\Models\Student;
use App\Domain\Gameplay\Models\GemTransaction;
use App\Domain\Gameplay\Models\ShopItem;
use App\Domain\Gameplay\Models\StudentItem;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('student can view shop page with items and gems', function () {
    $school = School::factory()->create();
    $user = User::factory()->create([
        'role' => 'student',
        'school_id' => $school->id,
    ]);
    $student = Student::factory()->create([
        'user_id' => $user->id,
        'school_id' => $school->id,
    ]);

    GemTransaction::factory()->create([
        'student_id' => $student->id,
        'amount' => 180,
    ]);
    ShopItem::factory()->create([
        'name' => 'Avatar Azul',
        'type' => 'avatar',
        'gem_price' => 100,
        'active' => true,
    ]);

    $this->actingAs($user)
        ->get('/loja')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Shop/Index')
            ->where('gems_balance', 180)
            ->has('items', 1)
            ->where('items.0.name', 'Avatar Azul')
            ->where('items.0.is_owned', false)
        );
});

it('user with student profile can view shop page even with non-student role', function () {
    $user = User::factory()->create([
        'role' => 'teacher',
        'school_id' => null,
    ]);
    $student = Student::factory()->create([
        'user_id' => $user->id,
        'school_id' => null,
    ]);

    GemTransaction::factory()->create([
        'student_id' => $student->id,
        'amount' => 180,
    ]);
    ShopItem::factory()->create([
        'name' => 'Avatar Azul',
        'type' => 'avatar',
        'gem_price' => 100,
        'active' => true,
    ]);

    $this->actingAs($user)
        ->get('/loja')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Shop/Index')
            ->where('gems_balance', 180)
            ->has('items', 1)
            ->where('items.0.name', 'Avatar Azul')
            ->where('items.0.is_owned', false)
        );
});

it('student can purchase an item from shop page', function () {
    $user = User::factory()->create(['role' => 'student', 'school_id' => null]);
    $student = Student::factory()->create(['user_id' => $user->id, 'school_id' => null]);
    $item = ShopItem::factory()->create(['gem_price' => 90, 'active' => true]);

    GemTransaction::factory()->create([
        'student_id' => $student->id,
        'amount' => 200,
    ]);

    $this->actingAs($user)
        ->post('/loja/comprar', ['item_id' => $item->id])
        ->assertRedirect();

    expect(StudentItem::where('student_id', $student->id)->where('item_id', $item->id)->exists())->toBeTrue();
    expect($student->fresh()->totalGems())->toBe(110);
});

it('user with student profile can purchase an item from shop page', function () {
    $user = User::factory()->create(['role' => 'teacher', 'school_id' => null]);
    $student = Student::factory()->create(['user_id' => $user->id, 'school_id' => null]);
    $item = ShopItem::factory()->create(['gem_price' => 90, 'active' => true]);

    GemTransaction::factory()->create([
        'student_id' => $student->id,
        'amount' => 200,
    ]);

    $this->actingAs($user)
        ->post('/loja/comprar', ['item_id' => $item->id])
        ->assertRedirect();

    expect(StudentItem::where('student_id', $student->id)->where('item_id', $item->id)->exists())->toBeTrue();
    expect($student->fresh()->totalGems())->toBe(110);
});

it('student can equip an owned item from shop page', function () {
    $user = User::factory()->create(['role' => 'student', 'school_id' => null]);
    $student = Student::factory()->create(['user_id' => $user->id, 'school_id' => null]);
    $itemA = ShopItem::factory()->avatar()->create(['gem_price' => 30]);
    $itemB = ShopItem::factory()->avatar()->create(['gem_price' => 30]);

    StudentItem::factory()->equipped()->create(['student_id' => $student->id, 'item_id' => $itemA->id]);
    StudentItem::factory()->create(['student_id' => $student->id, 'item_id' => $itemB->id, 'equipped' => false]);

    $this->actingAs($user)
        ->post('/loja/equipar', ['item_id' => $itemB->id])
        ->assertRedirect();

    expect(StudentItem::where('student_id', $student->id)->where('item_id', $itemB->id)->first()?->equipped)->toBeTrue();
    expect(StudentItem::where('student_id', $student->id)->where('item_id', $itemA->id)->first()?->equipped)->toBeFalse();
});

it('purchase returns validation error when student has insufficient gems', function () {
    $user = User::factory()->create(['role' => 'student', 'school_id' => null]);
    $student = Student::factory()->create(['user_id' => $user->id, 'school_id' => null]);
    $item = ShopItem::factory()->create(['gem_price' => 200, 'active' => true]);

    GemTransaction::factory()->create([
        'student_id' => $student->id,
        'amount' => 20,
    ]);

    $this->actingAs($user)
        ->from('/loja')
        ->post('/loja/comprar', ['item_id' => $item->id])
        ->assertRedirect('/loja')
        ->assertSessionHasErrors('shop');
});

it('non-student is redirected from shop page', function () {
    $guardian = User::factory()->create(['role' => 'guardian']);

    $this->actingAs($guardian)
        ->get('/loja')
        ->assertRedirect('/dashboard');
});

it('student without profile can still access shop page', function () {
    $user = User::factory()->create(['role' => 'student', 'school_id' => null]);

    $this->actingAs($user)
        ->get('/loja')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Shop/Index')
            ->where('gems_balance', 0)
        );
});
