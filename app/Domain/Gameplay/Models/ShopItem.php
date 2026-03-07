<?php

namespace App\Domain\Gameplay\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShopItem extends Model
{
    use HasFactory;

    public const TYPE_AVATAR = 'avatar';

    public const TYPE_FRAME = 'frame';

    public const TYPE_THEME = 'theme';

    public const TYPE_POWER_UP = 'power_up';

    /** @var array<string> */
    public const TYPES = [
        self::TYPE_AVATAR,
        self::TYPE_FRAME,
        self::TYPE_THEME,
        self::TYPE_POWER_UP,
    ];

    /** @var array<string, string> */
    public const TYPE_ALIASES = [
        'border' => self::TYPE_FRAME,
        'powerup' => self::TYPE_POWER_UP,
    ];

    protected $fillable = [
        'name',
        'type',
        'slug',
        'description',
        'image_url',
        'gem_price',
        'active',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'gem_price' => 'integer',
            'metadata' => 'array',
        ];
    }

    protected function type(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): ?string => $value === null ? null : self::normalizeType($value),
            set: fn (?string $value): ?string => $value === null ? null : self::normalizeType($value),
        );
    }

    public static function normalizeType(string $type): string
    {
        $normalized = strtolower(trim($type));

        return self::TYPE_ALIASES[$normalized] ?? $normalized;
    }

    /** @return array<string> */
    public static function rawTypeCandidates(string $type): array
    {
        $normalized = self::normalizeType($type);
        $aliases = array_keys(array_filter(
            self::TYPE_ALIASES,
            fn (string $canonicalType): bool => $canonicalType === $normalized
        ));

        return array_values(array_unique([$normalized, ...$aliases]));
    }

    public static function normalizeAvatarImageUrl(?string $imageUrl): ?string
    {
        if (! $imageUrl) {
            return null;
        }

        if (str_starts_with($imageUrl, '/storage/shop-avatars/')) {
            return '/media/shop-avatars/'.basename($imageUrl);
        }

        if (str_starts_with($imageUrl, 'shop-avatars/')) {
            return '/media/'.$imageUrl;
        }

        return $imageUrl;
    }

    public static function extractAvatarStoragePath(?string $imageUrl): ?string
    {
        if (! $imageUrl) {
            return null;
        }

        if (str_starts_with($imageUrl, '/storage/shop-avatars/')) {
            return str_replace('/storage/', '', $imageUrl);
        }

        if (str_starts_with($imageUrl, '/media/shop-avatars/')) {
            return str_replace('/media/', '', $imageUrl);
        }

        if (str_starts_with($imageUrl, 'shop-avatars/')) {
            return $imageUrl;
        }

        return null;
    }

    public function studentItems(): HasMany
    {
        return $this->hasMany(StudentItem::class, 'item_id');
    }
}
