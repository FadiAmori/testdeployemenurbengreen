<?php

namespace App\DataTransferObjects\Ai;

/**
 * @psalm-type ParsedCartArray=array{
 *     items: array<int, array{
 *         sku_or_name?:string,
 *         sku?:string,
 *         name?:string,
 *         qty?:int,
 *         quantity?:int,
 *         variant?:array<string,string|null>
 *     }>,
 *     shipping_address?:array{city?:?string,details?:?string},
 *     notes?:?string,
 *     confidence?:float,
 *     clarification?:?string
 * }
 */
final class ParsedCartResult
{
    /**
     * @param ParsedCartItem[] $items
     */
    public function __construct(
        public readonly array $items,
        public readonly ?ShippingAddress $shippingAddress,
        public readonly ?string $notes,
        public readonly float $confidence,
        public readonly ?string $clarificationRequest = null
    ) {
    }

    /**
     * @param ParsedCartArray $payload
     */
    public static function fromArray(array $payload): self
    {
        $items = array_map(
            static fn (array $item): ParsedCartItem => ParsedCartItem::fromArray($item),
            (array) ($payload['items'] ?? [])
        );

        $confidence = (float) ($payload['confidence'] ?? 0.0);

        $shippingAddress = null;
        if (! empty($payload['shipping_address']) && is_array($payload['shipping_address'])) {
            $shippingAddress = ShippingAddress::fromArray($payload['shipping_address']);
        }

        $notes = isset($payload['notes']) ? trim((string) $payload['notes']) : null;
        $clarification = isset($payload['clarification'])
            ? trim((string) $payload['clarification'])
            : null;

        return new self(
            items: $items,
            shippingAddress: $shippingAddress,
            notes: $notes !== '' ? $notes : null,
            confidence: $confidence,
            clarificationRequest: $clarification !== '' ? $clarification : null
        );
    }

    /**
     * @return array{
     *     items: array<int, array{sku_or_name:string,qty:int,variant:array<string,string>}>,
     *     shipping_address?:array{city?:string,details?:string},
     *     notes?:string,
     *     confidence:float,
     *     clarification?:string
     * }
     */
    public function toArray(): array
    {
        $items = array_map(
            static fn (ParsedCartItem $item): array => $item->toArray(),
            $this->items
        );

        $payload = [
            'items' => $items,
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
