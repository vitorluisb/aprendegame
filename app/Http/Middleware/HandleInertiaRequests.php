<?php

namespace App\Http\Middleware;

use App\Domain\Accounts\Enums\UserRole;
use App\Domain\Gameplay\Models\ShopItem;
use App\Domain\Gameplay\Models\StudentItem;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user()?->only(['id', 'name', 'email', 'role', 'school_id']),
            ],
            'gameplay_customization' => fn (): ?array => $this->resolveGameplayCustomization($request),
        ];
    }

    private function resolveGameplayCustomization(Request $request): ?array
    {
        /** @var User|null $user */
        $user = $request->user();

        if (! $user) {
            return null;
        }

        $isStudentContext = $user->role === UserRole::Student->value
            || $user->studentProfile()->withoutGlobalScopes()->exists();

        if (! $isStudentContext) {
            return null;
        }

        $student = $user->studentProfile()->withoutGlobalScopes()->first();

        if (! $student) {
            return null;
        }

        $equippedItemsByType = StudentItem::query()
            ->where('student_id', $student->id)
            ->where('equipped', true)
            ->with('item:id,type,slug,name,metadata')
            ->get()
            ->keyBy(fn (StudentItem $studentItem): string => ShopItem::normalizeType((string) $studentItem->item?->type));

        $themeItem = $equippedItemsByType->get(ShopItem::TYPE_THEME)?->item;
        $frameItem = $equippedItemsByType->get(ShopItem::TYPE_FRAME)?->item;

        return [
            'theme' => $this->mapTheme($themeItem?->slug, $themeItem?->metadata),
            'frame' => $this->mapFrame($frameItem?->slug, $frameItem?->metadata),
        ];
    }

    /** @param array<string, mixed>|null $metadata */
    private function mapTheme(?string $slug, ?array $metadata): ?array
    {
        if (! $slug || ! $metadata) {
            return null;
        }

        $primary = isset($metadata['primary']) ? (string) $metadata['primary'] : null;
        $accent = isset($metadata['accent']) ? (string) $metadata['accent'] : null;

        if (! $primary || ! $accent) {
            return null;
        }

        $accent2 = isset($metadata['accent_2']) ? (string) $metadata['accent_2'] : "color-mix(in srgb, {$accent} 72%, white)";
        $accent3 = isset($metadata['accent_3']) ? (string) $metadata['accent_3'] : "color-mix(in srgb, {$accent} 60%, {$primary})";

        return [
            'slug' => $slug,
            'css_vars' => [
                '--color-game-accent' => $accent,
                '--color-game-accent-2' => $accent2,
                '--color-game-accent-3' => $accent3,
                '--color-game-deep' => $primary,
                '--color-game-bg' => "color-mix(in srgb, {$primary} 16%, white)",
                '--color-game-teal' => "color-mix(in srgb, {$accent} 45%, #7EC8C8)",
                '--color-game-hero-start' => "color-mix(in srgb, {$accent} 78%, white)",
                '--color-game-hero-mid' => $accent2,
                '--color-game-hero-end' => "color-mix(in srgb, {$primary} 88%, black)",
            ],
        ];
    }

    /** @param array<string, mixed>|null $metadata */
    private function mapFrame(?string $slug, ?array $metadata): ?array
    {
        if (! $slug) {
            return null;
        }

        $style = isset($metadata['style']) ? (string) $metadata['style'] : null;
        $color = isset($metadata['color']) ? (string) $metadata['color'] : null;
        $resolvedColor = $color ?: match ($slug) {
            'borda-arco-iris' => '#A855F7',
            'borda-fogo' => '#F97316',
            default => '#F59E0B',
        };

        $isAnimated = $style === 'animated';

        return [
            'slug' => $slug,
            'style' => [
                'borderColor' => $resolvedColor,
                'borderWidth' => '3px',
                'borderStyle' => 'solid',
                'boxShadow' => $isAnimated
                    ? "0 0 0 2px #ffffff, 0 0 0 6px {$resolvedColor}AA, 0 0 16px {$resolvedColor}AA, 0 8px 18px {$resolvedColor}66"
                    : "0 0 0 2px #ffffff, 0 0 0 6px {$resolvedColor}88, 0 8px 16px {$resolvedColor}55",
            ],
        ];
    }
}
