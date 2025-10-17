<?php

namespace App\DataTransferObjects\Ai;

final class ProposedCartItem
{
    public function __construct(
        public readonly int $productId,
        public readonly string $sku,
        public readonly string $name,
        public readonly int $quantity,
        public readonly float $unitPrice,
        public readonly float $lineTotal,
        public readonly array $variant,
        public readonly ?string $imageUrl = null
    ) {
    }

    /**
     * @return array{
     *     product_id:int,
     *     sku:string,
     *     name:string,
     *     quantity:int,
     *     unit_price:float,
     *     line_total:float,
     *     variant:array<string,string>,
     *     image_url?:string
     * }
     */
    public function toArray(): array
    {
        $payload = [
            'product_id' => $this->productId,
            'sku' => $this->sku,
            'name' => $this->name,
            'quantity' => $this->quantity,
            'unit_price' => $this->unitPrice,
            'line_total' => $this->lineTotal,
            'variant' => $this->variant,
        ];

        if ($this->imageUrl !== null) {
            $payload['image_url'] = $this->imageUrl;
        }

        return $payload;
    }
}
