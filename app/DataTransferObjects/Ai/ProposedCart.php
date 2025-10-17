<?php

namespace App\DataTransferObjects\Ai;

final class ProposedCart
{
    /**
     * @param ProposedCartItem[] $items
     */
    public function __construct(
        public readonly array $items,
        public readonly float $subtotal,
        public readonly float $taxTotal,
        public readonly float $discountTotal,
        public readonly float $grandTotal,
        public readonly string $currency,
        public readonly float $confidence,
        public readonly ?ShippingAddress $shippingAddress,
        public readonly ?string $notes,
        public readonly ?string $clarificationRequest = null
    ) {
    }

    /**
     * @return array{
     *     items: array<int, array{
     *         product_id:int,
     *         sku:string,
     *         name:string,
     *         quantity:int,
     *         unit_price:float,
     *         line_total:float,
     *         variant:array<string,string>,
     *         image_url?:string
     *     }>,
     *     totals: array{
     *         subtotal:float,
     *         tax:float,
     *         discount:float,
     *         total:float,
     *         currency:string
     *     },
     *     shipping_address?:array{city?:string,details?:string},
     *     notes?:string,
     *     confidence:float,
     *     clarification?:string
     * }
     */
    public function toArray(): array
    {
        $items = array_map(
            static fn (ProposedCartItem $item): array => $item->toArray(),
            $this->items
        );

        $payload = [
            'items' => $items,
            'totals' => [
                'subtotal' => $this->subtotal,
                'tax' => $this->taxTotal,
                'discount' => $this->discountTotal,
                'total' => $this->grandTotal,
                'currency' => $this->currency,
            ],
            'confidence' => $this->confidence,
        ];

        if ($this->shippingAddress !== null) {
            $payload['shipping_address'] = $this->shippingAddress->toArray();
        }

        if ($this->notes !== null) {
            $payload['notes'] = $this->notes;
        }

        if ($this->clarificationRequest !== null) {
            $payload['clarification'] = $this->clarificationRequest;
        }

        return $payload;
    }
}
