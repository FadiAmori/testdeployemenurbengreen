<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The database connection used by the model.
     *
     * @var string
     */
    protected $connection = 'mysql';

    protected $guarded = [];

    protected $casts = [
        'price' => 'float',
        'sale_price' => 'float',
        'weight' => 'float',
        'dimensions' => 'array',
        'attributes' => 'array',
        'seo' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
        'type' => 'string',
    ];

    protected $appends = [
        'primary_image_url',
    ];

    protected static function booted(): void
    {
        static::saving(function (Product $product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
            if (empty($product->sku)) {
                $product->sku = strtoupper(Str::random(8));
            }
            if ($product->isDirty('stock') && $product->stock > 0 && $product->status === 'draft') {
                $product->status = 'published';
                $product->published_at = $product->published_at ?? Carbon::now();
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class, 'sub_category_id');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function variants()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('position');
    }

    public function inventoryMovements()
    {
        return $this->hasMany(ProductInventoryMovement::class)->latest();
    }

    public function getPrimaryImageUrlAttribute(): string
    {
        return static::resolveImageUrl($this->primaryImage?->path);
    }

    public static function resolveImageUrl(?string $path, string $fallback = 'urbangreen/img/bg-img/9.jpg'): string
    {
        if (! $path) {
            return asset($fallback);
        }

        // Absolute URLs or absolute paths
        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        // Normalize common stored values like "public/..." or "storage/..."
        $raw = ltrim($path, '/');
        $normalized = $raw;
        if (Str::startsWith($normalized, 'storage/')) {
            $normalized = substr($normalized, strlen('storage/')) ?: '';
        }
        if (Str::startsWith($normalized, 'public/')) {
            $normalized = substr($normalized, strlen('public/')) ?: '';
        }

        // 1) Check public disk (storage/app/public)
        try {
            if ($normalized !== '' && Storage::disk('public')->exists($normalized)) {
                return Storage::disk('public')->url($normalized);
            }
        } catch (\Throwable $e) {
            // continue to next strategy
        }

        // 2) If file is already published in public/ folder
        if (file_exists(public_path($raw))) {
            return asset($raw);
        }
        if ($normalized !== '' && file_exists(public_path($normalized))) {
            return asset($normalized);
        }

        // 3) If the saved path already targets the symlinked /storage path
        if (Str::startsWith($raw, 'storage/')) {
            return asset($raw);
        }

        // 4) Absolute local path -> map to /storage if inside storage/app/public
        if (Str::startsWith($path, DIRECTORY_SEPARATOR) && file_exists($path)) {
            $publicRoot = storage_path('app/public') . DIRECTORY_SEPARATOR;
            if (Str::startsWith($path, $publicRoot)) {
                $rel = ltrim(Str::after($path, $publicRoot), DIRECTORY_SEPARATOR);
                return asset('storage/' . $rel);
            }
        }

        // Fallbacks
        return asset($fallback);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('availability', '!=', 'out_of_stock')->where('is_active', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function markAsOutOfStock(?string $reason = null): void
    {
        $this->availability = 'out_of_stock';
        $this->is_active = false;
        $this->status = 'archived';
        if ($reason) {
            $this->seo = array_merge($this->seo ?? [], ['stock_message' => $reason]);
        }
        $this->save();
    }

    public function adjustStock(int $adjustment, array $meta = []): ProductInventoryMovement
    {
        $movement = $this->inventoryMovements()->create([
            'adjustment' => $adjustment,
            'reason' => $meta['reason'] ?? 'manual',
            'reference' => $meta['reference'] ?? null,
            'notes' => $meta['notes'] ?? null,
            'user_id' => $meta['user_id'] ?? null,
            'metadata' => $meta['metadata'] ?? null,
        ]);

        $this->stock += $adjustment;
        if ($this->stock < 0) {
            $this->stock = 0;
        }

        if ($this->stock === 0) {
            $this->availability = 'out_of_stock';
        } elseif ($this->stock <= $this->stock_threshold) {
            $this->availability = 'limited';
        } else {
            $this->availability = 'in_stock';
        }

        $this->save();

        return $movement;
    }
        public function maintenance()
    {
        return $this->hasOne(\App\Models\Shop\Maintenance::class);
    }
    public function notifications()
    {
        $relation = $this->belongsToMany(Notification::class);

        try {
            if (Schema::hasTable('notification_product') && Schema::hasColumn('notification_product', 'days')) {
                $relation = $relation->withPivot(['days', 'time'])->withTimestamps();
            }
        } catch (\Exception $e) {
            // In case the database is not available during migration, fall back to plain relation
        }

        return $relation;
    }

    public function favoritedByUsers()
    {
        return $this->belongsToMany(\App\Models\User::class, 'user_product_favorites')
                    ->withTimestamps();
    }
}
