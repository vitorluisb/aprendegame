<?php

namespace App\Http\Controllers\Gameplay;

use App\Domain\Accounts\Enums\UserRole;
use App\Domain\Gameplay\Models\ShopItem;
use App\Domain\Gameplay\Models\StudentItem;
use App\Domain\Gameplay\Services\ShopService;
use App\Http\Controllers\Controller;
use App\Http\Requests\EquipShopItemRequest;
use App\Http\Requests\PurchaseShopItemRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class ShopController extends Controller
{
    public function __construct(private readonly ShopService $shopService) {}

    public function index(): Response|RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();
        $isStudentContext = $user->role === UserRole::Student->value
            || $user->studentProfile()->withoutGlobalScopes()->exists();

        if (! $isStudentContext) {
            return redirect('/dashboard');
        }

        $student = $user->ensureStudentProfile();
        $student->refillLivesIfDue();
        $student = $student->fresh();
        $activeItems = $this->shopService->getActiveItems();

        $inventory = StudentItem::query()
            ->where('student_id', $student->id)
            ->with('item:id,name,type,slug,image_url,gem_price')
            ->get();

        $ownedItemIds = $inventory->pluck('item_id')->all();
        $equippedByType = $inventory
            ->where('equipped', true)
            ->keyBy(fn (StudentItem $studentItem): string => ShopItem::normalizeType((string) $studentItem->item?->type));

        $items = $activeItems->map(function (ShopItem $item) use ($ownedItemIds, $equippedByType): array {
            $normalizedType = ShopItem::normalizeType((string) $item->type);
            $isOwned = in_array($item->id, $ownedItemIds, true);
            $isEquipped = $isOwned && ($equippedByType->get($normalizedType)?->item_id === $item->id);

            return [
                'id' => $item->id,
                'name' => $item->name,
                'type' => $normalizedType,
                'slug' => $item->slug,
                'description' => $item->description,
                'image_url' => $item->image_url,
                'gem_price' => $item->gem_price,
                'is_owned' => $isOwned,
                'is_equipped' => $isEquipped,
            ];
        })->values();

        return Inertia::render('Shop/Index', [
            'gems_balance' => $student->totalGems(),
            'lives_current' => $student->lives_current,
            'lives_max' => $student->lives_max,
            'life_cost' => \App\Domain\Accounts\Models\Student::LIFE_PURCHASE_COST,
            'items' => $items,
        ]);
    }

    public function purchase(PurchaseShopItemRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();
        $student = $user->ensureStudentProfile();

        $item = ShopItem::query()->findOrFail($request->integer('item_id'));

        try {
            $this->shopService->purchase($student, $item);
        } catch (RuntimeException $exception) {
            return back()->withErrors(['shop' => $exception->getMessage()]);
        }

        return back();
    }

    public function equip(EquipShopItemRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();
        $student = $user->ensureStudentProfile();

        $item = ShopItem::query()->findOrFail($request->integer('item_id'));

        $this->shopService->equip($student, $item);

        return back();
    }

    public function buyLife(): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();
        $student = $user->ensureStudentProfile();

        try {
            $this->shopService->buyLife($student);
        } catch (RuntimeException $exception) {
            return back()->withErrors(['shop' => $exception->getMessage()]);
        }

        return back();
    }
}
