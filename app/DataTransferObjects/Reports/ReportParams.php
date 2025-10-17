<?php

namespace App\DataTransferObjects\Reports;

use Carbon\CarbonImmutable;

final class ReportParams
{
    public function __construct(
        public readonly CarbonImmutable $periodStart,
        public readonly CarbonImmutable $periodEnd,
        public readonly string $granularity = 'week',
        public readonly ?CarbonImmutable $comparisonStart = null,
        public readonly ?CarbonImmutable $comparisonEnd = null
    ) {
    }

    public function toArray(): array
    {
        return [
            'period_start' => $this->periodStart->toDateString(),
            'period_end' => $this->periodEnd->toDateString(),
            'comparison_start' => $this->comparisonStart?->toDateString(),
            'comparison_end' => $this->comparisonEnd?->toDateString(),
            'granularity' => $this->granularity,
        ];
    }
}

