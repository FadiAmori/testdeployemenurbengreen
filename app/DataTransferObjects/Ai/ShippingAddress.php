<?php

namespace App\DataTransferObjects\Ai;

final class ShippingAddress
{
    public function __construct(
        public readonly ?string $city,
        public readonly ?string $details
    ) {
    }

    /**
     * @param array{city?:?string,details?:?string} $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            city: isset($payload['city']) ? self::sanitizeField((string) $payload['city']) : null,
            details: isset($payload['details']) ? self::sanitizeField((string) $payload['details']) : null,
        );
    }

    private static function sanitizeField(string $value): ?string
    {
        $normalized = trim($value);

        return $normalized !== '' ? $normalized : null;
    }

    /**
     * @return array{city?:string,details?:string}
     */
    public function toArray(): array
    {
        $data = [];

        if ($this->city !== null) {
            $data['city'] = $this->city;
        }

        if ($this->details !== null) {
            $data['details'] = $this->details;
        }

        return $data;
    }
}
