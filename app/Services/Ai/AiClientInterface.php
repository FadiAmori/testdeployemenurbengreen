<?php

namespace App\Services\Ai;

interface AiClientInterface
{
    /**
     * @param array<string,mixed> $payload
     * @param array{timeout?:float} $options
     *
     * @return array<string,mixed>
     */
    public function request(string $endpoint, array $payload, array $options = []): array;
}
