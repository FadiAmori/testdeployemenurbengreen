<?php

namespace App\Repositories;

use App\DataTransferObjects\Ai\ParsedCartItem;
use App\Models\Shop\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

final class ProductRepository
{
    public function buildCatalogIndex(): array
    {
        return Product::query()
            ->select(['id', 'sku', 'name', 'price', 'stock', 'attributes'])
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get()
            ->map(static function (Product $product): array {
                return [
                    'sku' => $product->sku,
                    'name' => $product->name,
                    'price' => (float) $product->price,
                    'stock' => (int) $product->stock,
                    'variants' => $product->attributes ?? [],
                ];
            })
            ->all();
    }

    public function findForCartItem(ParsedCartItem $item): ?Product
    {
        $query = Product::query()->where('is_active', true);
        $skippedSku = false;
        if ($this->looksLikeSku($item->skuOrName)) {
            $query->where('sku', $item->skuOrName);
            $skippedSku = true;
        } else {
            $query->where(function ($inner) use ($item): void {
                $inner->where('sku', $item->skuOrName)
                    ->orWhere('name', 'like', '%' . $item->skuOrName . '%');
            });
        }

        $product = $query->first();
        if ($product) {
            return $product;
        }

        if (! $skippedSku) {
            return Product::query()
                ->where('is_active', true)
                ->where('sku', $item->skuOrName)
                ->first();
        }

        return null;
    }

    private function looksLikeSku(string $value): bool
    {
        return (bool) preg_match('/^[A-Z0-9\-]{3,}$/', $value);
    }
}
