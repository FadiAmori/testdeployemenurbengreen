<?php

namespace App\DataTransferObjects\Reports;

use Carbon\CarbonImmutable;

final class ReportMetricsDTO
{
    /**
     * @param array<string, array{current:float|int|null,previous:float|int|null,delta:float|null,delta_pct:float|null}> $kpis
     * @param array{top_products:array<int,array{product_id:int,sku:string,name:string,revenue:float,units:int,variation_pct:float|null}>,decliners:array<int,array{product_id:int,sku:string,name:string,revenue:float,units:int,variation_pct:float|null}>,stockouts:array<int,array{product_id:int,sku:string,name:string,days_out:int}>,returns:array<int,array{product_id:int,sku:string,name:string,returns:int,refund_total:float}>} $productInsights
     * @param array<int,array{date:string,revenue:float,orders:int}> $timeSeries
     * @param array<string,mixed> $meta
     */
    public function __construct(
        public readonly CarbonImmutable $periodStart,
        public readonly CarbonImmutable $periodEnd,
        public readonly CarbonImmutable $comparisonStart,
        public readonly CarbonImmutable $comparisonEnd,
        public readonly array $kpis,
        public readonly array $productInsights,
        public readonly array $timeSeries,
        public readonly array $meta = []
    ) {
    }

    public function toArray(): array
    {
        return [
            'period_start' => $this->periodStart->toDateString(),
            'period_end' => $this->periodEnd->toDateString(),
            'comparison_start' => $this->comparisonStart->toDateString(),
            'comparison_end' => $this->comparisonEnd->toDateString(),
            'kpis' => $this->kpis,
            'product_insights' => $this->productInsights,
            'time_series' => $this->timeSeries,
            'meta' => $this->meta,
        ];
    }
}

