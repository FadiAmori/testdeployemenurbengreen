<?php

namespace App\DataTransferObjects\Reports;

final class AiReportResult
{
    /**
     * @param array<string,mixed> $rawResponse
     */
    public function __construct(
        public readonly string $markdown,
        public readonly array $rawResponse = []
    ) {
    }
}

