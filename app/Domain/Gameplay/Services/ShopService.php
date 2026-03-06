<?php

namespace App\Domain\Gameplay\Services;

use App\Domain\Accounts\Models\Student;
use App\Domain\Gameplay\Models\GemTransaction;
use App\Domain\Gameplay\Models\ShopItem;
use App\Domain\Gameplay\Models\StudentItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ShopService
{
    public function purchase(Student $student, ShopItem $item): StudentItem
    {
        if (! $item->active) {
            throw new RuntimeException('Item não está disponível na loja.');
        }

        $alreadyOwns = StudentItem::where('student_id', $student->id)
            ->where('item_id', $item->id)
            ->exists();

        if ($alreadyOwns) {
            throw new RuntimeException('Aluno já possui este item.');
        }

        $balance = $student->totalGems();

        if ($balance < $item->gem_price) {
            throw new RuntimeException('Gemas insuficientes.');
        }

        return DB::transaction(function () use ($student, $item) {
            GemTransaction::create([
                'student_id' => $student->id,
                'amount' => -$item->gem_price,
                'source' => 'purchase',
                'sourceable_type' => ShopItem::class,
                'sourceable_id' => $item->id,
                'created_at' => now(),
            ]);

            $studentItem = StudentItem::create([
                'student_id' => $student->id,
                'item_id' => $item->id,
                'equipped' => false,
                'purchased_at' => now(),
            ]);

            if ($item->type === ShopItem::TYPE_AVATAR) {
                StudentItem::where('student_id', $student->id)
                    ->whereHas('item', fn ($query) => $query->whereIn('type', ShopItem::rawTypeCandidates(ShopItem::TYPE_AVATAR)))
                    ->where('id', '!=', $studentItem->id)
                    ->update(['equipped' => false]);

                $studentItem->update(['equipped' => true]);
            }

            return $studentItem->refresh();
        });
    }

    public function equip(Student $student, ShopItem $item): StudentItem
    {
        $studentItem = StudentItem::where('student_id', $student->id)
            ->where('item_id', $item->id)
            ->firstOrFail();

        // Unequip other items of the same type
        StudentItem::where('student_id', $student->id)
            ->whereHas('item', fn ($q) => $q->whereIn('type', ShopItem::rawTypeCandidates((string) $item->type)))
            ->where('id', '!=', $studentItem->id)
            ->update(['equipped' => false]);

        $studentItem->update(['equipped' => true]);

        return $studentItem->refresh();
    }

    public function getActiveItems(): Collection
    {
        return Cache::remember('shop:active_items', now()->addHour(), function () {
            return ShopItem::where('active', true)->orderBy('gem_price')->get();
        });
    }

    public function forgetItemsCache(): void
    {
        Cache::forget('shop:active_items');
    }

    public function awardGems(Student $student, int $amount, string $source): GemTransaction
    {
        return GemTransaction::create([
            'student_id' => $student->id,
            'amount' => $amount,
            'source' => $source,
            'created_at' => now(),
        ]);
    }

    public function buyLife(Student $student): Student
    {
        if ($student->isAtMaxLives()) {
            throw new RuntimeException('Você já está com vidas máximas.');
        }

        if ($student->totalGems() < Student::LIFE_PURCHASE_COST) {
            throw new RuntimeException('Neurons insuficientes para comprar uma vida.');
        }

        return DB::transaction(function () use ($student): Student {
            GemTransaction::create([
                'student_id' => $student->id,
                'amount' => -Student::LIFE_PURCHASE_COST,
                'source' => 'buy_life',
                'created_at' => now(),
            ]);

            $student->recoverLife();

            return $student->refresh();
        });
    }
}
