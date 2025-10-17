<?php

namespace App\Services\Shop;

use App\DataTransferObjects\Ai\ProposedCart;
use RuntimeException;

final class CartProposalSigner
{
    private const ALGO = 'sha256';

    public function __construct(
        private readonly string $secret
    ) {
    }

    public static function fromAppKey(): self
    {
        $secret = (string) config('app.key');
        if ($secret === '') {
            throw new RuntimeException('APP_KEY manquant pour signer le panier.');
        }

        return new self($secret);
    }

    public function encode(ProposedCart $cart): string
    {
        $payload = json_encode($cart->toArray(), JSON_THROW_ON_ERROR);
        $signature = $this->sign($payload);

        return base64_encode($payload) . '.' . $signature;
    }

    /**
     * @return array<string,mixed>
     */
    public function decode(string $token): array
    {
        [$encodedPayload, $signature] = $this->splitToken($token);
        $payload = base64_decode($encodedPayload, true);

        if ($payload === false || ! $this->verify($payload, $signature)) {
            throw new RuntimeException('Signature du panier invalide.');
        }

        /** @var array<string,mixed> $decoded */
        $decoded = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);

        return $decoded;
    }

    private function sign(string $payload): string
    {
        return hash_hmac(self::ALGO, $payload, $this->secret);
    }

    private function verify(string $payload, string $signature): bool
    {
        $expected = $this->sign($payload);

        return hash_equals($expected, $signature);
    }

    /**
     * @return array{0:string,1:string}
     */
    private function splitToken(string $token): array
    {
        $parts = explode('.', $token, 2);
        if (count($parts) !== 2) {
            throw new RuntimeException('Format de jeton invalide.');
        }

        return [$parts[0], $parts[1]];
    }
}
