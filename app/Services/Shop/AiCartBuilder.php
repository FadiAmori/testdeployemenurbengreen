<?php

namespace App\Services\Shop;

use App\DataTransferObjects\Ai\ParsedCartItem;
use App\DataTransferObjects\Ai\ParsedCartResult;
use App\DataTransferObjects\Ai\ProposedCart;
use App\DataTransferObjects\Ai\ProposedCartItem;
use App\DataTransferObjects\Ai\ShippingAddress;
use App\Models\Shop\Product;
use App\Repositories\ProductRepository;
use Illuminate\Support\Str;

final class AiCartBuilder
{
    private const DEFAULT_CURRENCY = 'EUR';

    public function __construct(
        private readonly ProductRepository $products
    ) {
    }

    public function build(ParsedCartResult $parsed): ProposedCart
    {
        $items = [];
        $clarifications = [];
        $subtotal = 0.0;

        foreach ($parsed->items as $item) {
            $product = $this->products->findForCartItem($item);
            if ($product === null) {
                $clarifications[] = sprintf('Impossible de trouver le produit « %s ». Pouvez-vous préciser le nom ou SKU ?', $item->skuOrName);
                continue;
            }

            $variantCheck = $this->validateVariant($product, $item);
            if ($variantCheck !== null) {
                $clarifications[] = $variantCheck;
                continue;
            }

            if ($item->quantity <= 0) {
                $clarifications[] = sprintf('Quantité invalide pour %s. Merci d’indiquer une quantité positive.', $product->name);
                continue;
            }

            if ($product->stock !== null && $product->stock < $item->quantity) {
                $clarifications[] = sprintf(
                    'Stock insuffisant pour %s. Stock disponible: %d.',
                    $product->name,
                    (int) $product->stock
                );
                continue;
            }

            $unitPrice = (float) ($product->price ?? 0);
            $lineTotal = $unitPrice * $item->quantity;
            $subtotal += $lineTotal;

            $items[] = new ProposedCartItem(
                productId: (int) $product->id,
                sku: $product->sku,
                name: $product->name,
                quantity: $item->quantity,
                unitPrice: $unitPrice,
                lineTotal: $lineTotal,
                variant: $item->variant,
                imageUrl: $product->primary_image_url ?? null
            );
        }

        $taxTotal = $this->calculateTax($subtotal);
        $discountTotal = 0.0;
        $grandTotal = $subtotal - $discountTotal + $taxTotal;

        $clarificationMessage = $parsed->clarificationRequest;
        if (! empty($clarifications)) {
            $clarificationMessage = implode(' ', array_unique($clarifications));
        }

        return new ProposedCart(
            items: $items,
            subtotal: round($subtotal, 2),
            taxTotal: round($taxTotal, 2),
            discountTotal: round($discountTotal, 2),
            grandTotal: round($grandTotal, 2),
            currency: self::DEFAULT_CURRENCY,
            confidence: $parsed->confidence,
            shippingAddress: $parsed->shippingAddress,
            notes: $parsed->notes,
            clarificationRequest: $clarificationMessage
        );
    }

    private function calculateTax(float $amount): float
    {
        $taxRate = (float) config('shop.tax_rate', 0.0);

        return $amount * $taxRate;
    }

    private function validateVariant(Product $product, ParsedCartItem $item): ?string
    {
        $attributes = $product->attributes ?? [];
        if (! is_array($attributes) || $attributes === []) {
            return null;
        }

        $missing = [];
        foreach ($attributes as $attributeKey => $allowedValues) {
            if (! is_array($allowedValues) || $allowedValues === []) {
                continue;
            }

            $normalizedKey = Str::lower($attributeKey);
            $value = null;
            foreach ($item->variant as $variantKey => $variantValue) {
                if (Str::lower($variantKey) === $normalizedKey) {
                    $value = $variantValue;
                    break;
                }
            }

            if ($value === null) {
                $missing[] = $attributeKey;
                continue;
            }

            $allowedNormalized = array_map(static fn ($val): string => Str::lower((string) $val), $allowedValues);
            if (! in_array(Str::lower($value), $allowedNormalized, true)) {
                return sprintf(
                    'La variante %s pour %s n’est pas disponible. Variantes possibles: %s.',
                    $attributeKey,
                    $product->name,
                    implode(', ', $allowedValues)
                );
            }
        }

        if ($missing !== []) {
            return sprintf(
                'Pour %s, merci de préciser: %s.',
                $product->name,
                implode(', ', $missing)
            );
        }

        return null;
    }
}
