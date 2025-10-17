<?php

namespace App\Models\Shop;

use App\Models\Shop\Product;
use App\Models\Shop\SubCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    /**
     * The database connection used by the model.
     *
     * @var string
     */
    protected $connection = 'mysql';

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    protected $appends = [
        'image_url',
    ];

    public function subCategories(): HasMany
    {
        $subCategoryTable = (new SubCategory())->getTable();

        return $this->hasMany(SubCategory::class)
            ->orderBy($subCategoryTable . '.position')
            ->orderBy($subCategoryTable . '.name');
    }

    public function children(): HasMany
    {
        return $this->subCategories();
    }

    public function products(): HasManyThrough
    {
        $productTable = (new Product())->getTable();

        return $this->hasManyThrough(
            Product::class,
            SubCategory::class,
            'category_id',
            'sub_category_id'
        )->orderBy($productTable . '.name');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeRoot(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function isRoot(): bool
    {
        return $this->parent_id === null;
    }

    public function getImageUrlAttribute(): string
    {
        return static::resolveImageUrl($this->image_path, 'urbangreen/img/bg-img/24.jpg');
    }

    public static function resolveImageUrl(?string $path, string $fallback = 'urbangreen/img/bg-img/24.jpg'): string
    {
        if (! $path) {
            return asset($fallback);
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        $raw = ltrim($path, '/');
        $normalized = $raw;
        if (Str::startsWith($normalized, 'storage/')) {
            $normalized = substr($normalized, strlen('storage/')) ?: '';
        }
        if (Str::startsWith($normalized, 'public/')) {
            $normalized = substr($normalized, strlen('public/')) ?: '';
        }

        try {
            if ($normalized !== '' && Storage::disk('public')->exists($normalized)) {
                return Storage::disk('public')->url($normalized);
            }
        } catch (\Throwable $e) {
            // ignore and try next strategies
        }

        if ($normalized !== '' && file_exists(public_path($normalized))) {
            return asset($normalized);
        }
        if (file_exists(public_path($raw))) {
            return asset($raw);
        }

        // Absolute local path: map to /storage if under storage/app/public
        if (Str::startsWith($path, DIRECTORY_SEPARATOR) && file_exists($path)) {
            $publicRoot = storage_path('app/public') . DIRECTORY_SEPARATOR;
            if (Str::startsWith($path, $publicRoot)) {
                $rel = ltrim(Str::after($path, $publicRoot), DIRECTORY_SEPARATOR);
                return asset('storage/' . $rel);
            }
        }

        return asset($fallback);
    }
}
