<?php

namespace App\DataTransferObjects\Ai;

final class ParsedCartItem
{
    public function __construct(
        public readonly string $skuOrName,
        public readonly int $quantity,
        public readonly array $variant
    ) {
    }

    /**
     * @param array{sku_or_name?:string,sku?:string,name?:string,qty?:int,quantity?:int,variant?:array<string,string|null>} $payload
     */
    public static function fromArray(array $payload): self
    {
        $skuOrName = $payload['sku_or_name']
            ?? $payload['sku']
            ?? $payload['name']
            ?? '';

        return new self(
            skuOrName: $skuOrName,
            quantity: (int) ($payload['qty'] ?? $payload['quantity'] ?? 0),
            variant: array_filter(
                (array) ($payload['variant'] ?? []),
                static fn ($value): bool => $value !== null && $value !== ''
            )
        );
    }

    /**
     * @return array{sku_or_name:string,qty:int,variant:array<string,string>}
     */
    public function toArray(): array
    {
        return [
            'sku_or_name' => $this->skuOrName,
            'qty' => $this->quantity,
            'variant' => $this->variant,
        ];
    }
}
