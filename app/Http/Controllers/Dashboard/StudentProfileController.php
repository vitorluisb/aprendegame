<?php

namespace App\Http\Controllers\Dashboard;

use App\Domain\Content\Models\Grade;
use App\Domain\Gameplay\Models\ShopItem;
use App\Domain\Gameplay\Models\StudentBadge;
use App\Domain\Gameplay\Models\StudentItem;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateStudentAvatarRequest;
use App\Http\Requests\UpdateStudentGradeRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StudentProfileController extends Controller
{
    public function show(): Response|RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();

        $hasStudentProfile = $user->studentProfile()->withoutGlobalScopes()->exists();
        $isStudentContext = $user->role === 'student' || $hasStudentProfile;

        if (! $isStudentContext) {
            return Inertia::render('Profile/Show', [
                'is_student' => false,
                'student' => null,
                'grade_options' => [],
            ]);
        }

        $student = $user->ensureStudentProfile()->load('streak');

        $totalXp = $student->totalXp();
        $level = (int) floor($totalXp / 100) + 1;

        $badges = StudentBadge::query()
            ->where('student_id', $student->id)
            ->with('badge:id,name,icon,description,condition_type')
            ->orderByDesc('earned_at')
            ->limit(24)
            ->get()
            ->map(fn (StudentBadge $studentBadge): array => [
                'id' => $studentBadge->id,
                'earned_at' => $studentBadge->earned_at?->format('Y-m-d H:i:s'),
                'badge' => [
                    'name' => $studentBadge->badge?->name,
                    'description' => $studentBadge->badge?->description,
                    'icon' => $studentBadge->badge?->icon,
                    'condition_type' => $studentBadge->badge?->condition_type,
                ],
            ]);

        $inventoryCollection = StudentItem::query()
            ->where('student_id', $student->id)
            ->with('item:id,name,type,slug,image_url,gem_price')
            ->orderByDesc('equipped')
            ->orderByDesc('purchased_at')
            ->limit(60)
            ->get();

        $equippedAvatarUrl = $inventoryCollection
            ->first(fn (StudentItem $studentItem): bool => $studentItem->equipped
                && in_array(ShopItem::normalizeType((string) $studentItem->item?->type), ShopItem::rawTypeCandidates(ShopItem::TYPE_AVATAR), true)
                && filled($studentItem->item?->image_url))
            ?->item?->image_url;
        $equippedAvatarUrl = ShopItem::normalizeAvatarImageUrl($equippedAvatarUrl);

        $personalAvatarUrl = $this->normalizeStudentAvatarUrl($student->avatar_url);
        $displayAvatarUrl = $equippedAvatarUrl ?: $personalAvatarUrl;

        $inventory = $inventoryCollection
            ->map(fn (StudentItem $studentItem): array => [
                'id' => $studentItem->id,
                'equipped' => $studentItem->equipped,
                'purchased_at' => $studentItem->purchased_at?->format('Y-m-d H:i:s'),
                'item' => [
                    'name' => $studentItem->item?->name,
                    'type' => $studentItem->item?->type,
                    'slug' => $studentItem->item?->slug,
                    'image_url' => ShopItem::normalizeType((string) $studentItem->item?->type) === ShopItem::TYPE_AVATAR
                        ? ShopItem::normalizeAvatarImageUrl($studentItem->item?->image_url)
                        : $studentItem->item?->image_url,
                    'gem_price' => $studentItem->item?->gem_price,
                ],
            ]);

        return Inertia::render('Profile/Show', [
            'is_student' => true,
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'avatar_url' => $displayAvatarUrl,
                'avatar_personal_url' => $personalAvatarUrl,
                'avatar_equipped_url' => $equippedAvatarUrl,
                'grade_id' => $student->grade_id,
                'total_xp' => $totalXp,
                'level' => $level,
                'xp_in_level' => $totalXp % 100,
                'streak_current' => $student->streak?->current ?? 0,
                'streak_best' => $student->streak?->best ?? 0,
                'total_gems' => $student->totalGems(),
                'badges_count' => $badges->count(),
                'badges' => $badges,
                'inventory_count' => $inventory->count(),
                'inventory' => $inventory,
            ],
            'grade_options' => Grade::query()
                ->orderBy('order')
                ->get(['id', 'name', 'code', 'stage'])
                ->map(fn (Grade $grade): array => [
                    'id' => $grade->id,
                    'name' => $grade->name,
                    'code' => $grade->code,
                    'stage' => $grade->stage,
                ]),
        ]);
    }

    public function updateGrade(UpdateStudentGradeRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();

        $isStudentContext = $user->role === 'student'
            || $user->studentProfile()->withoutGlobalScopes()->exists();

        if (! $isStudentContext) {
            return redirect('/dashboard');
        }

        $student = $user->ensureStudentProfile();
        $student->update([
            'grade_id' => $request->input('grade_id'),
        ]);

        return back();
    }

    public function updateAvatar(UpdateStudentAvatarRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();
        $student = $user->ensureStudentProfile();

        $oldPath = $this->extractStudentAvatarStoragePath($student->avatar_url);
        if ($oldPath) {
            Storage::disk('public')->delete($oldPath);
        }

        $path = $request->file('avatar')->store('student-avatars', 'public');
        $publicUrl = '/media/'.$path;

        $student->update(['avatar_url' => $publicUrl]);
        $user->update(['avatar_url' => $publicUrl]);
        $this->unequipShopAvatars($student->id);

        return back();
    }

    public function usePersonalAvatar(): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();
        $student = $user->ensureStudentProfile();

        $this->unequipShopAvatars($student->id);

        return back();
    }

    public function avatar(string $filename): BinaryFileResponse
    {
        $path = 'student-avatars/'.$filename;

        abort_unless(Storage::disk('public')->exists($path), 404);

        return response()->file(
            Storage::disk('public')->path($path),
            ['Cache-Control' => 'public, max-age=86400']
        );
    }

    private function normalizeStudentAvatarUrl(?string $avatarUrl): ?string
    {
        if (! $avatarUrl) {
            return null;
        }

        if (str_starts_with($avatarUrl, '/storage/student-avatars/')) {
            return '/media/student-avatars/'.basename($avatarUrl);
        }

        return $avatarUrl;
    }

    private function extractStudentAvatarStoragePath(?string $avatarUrl): ?string
    {
        if (! $avatarUrl) {
            return null;
        }

        if (str_starts_with($avatarUrl, '/storage/student-avatars/')) {
            return str_replace('/storage/', '', $avatarUrl);
        }

        if (str_starts_with($avatarUrl, '/media/student-avatars/')) {
            return str_replace('/media/', '', $avatarUrl);
        }

        if (str_starts_with($avatarUrl, 'student-avatars/')) {
            return $avatarUrl;
        }

        return null;
    }

    private function unequipShopAvatars(int $studentId): void
    {
        StudentItem::query()
            ->where('student_id', $studentId)
            ->where('equipped', true)
            ->whereHas('item', fn ($query) => $query->whereIn('type', ShopItem::rawTypeCandidates(ShopItem::TYPE_AVATAR)))
            ->update(['equipped' => false]);
    }
}
