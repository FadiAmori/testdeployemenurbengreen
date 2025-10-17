<?php

namespace App\Services\Reports;

use App\DataTransferObjects\Reports\ReportMetricsDTO;
use App\DataTransferObjects\Reports\ReportParams;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class ReportMetricsService
{
    public function collect(ReportParams $params): ReportMetricsDTO
    {
        $current = $this->aggregateOrders($params->periodStart, $params->periodEnd);
        $previous = $this->aggregateOrders($params->comparisonStart, $params->comparisonEnd);

        $kpis = $this->buildKpis($current, $previous);

        $productInsights = [
            'top_products' => $this->topProducts($params->periodStart, $params->periodEnd),
            'decliners' => $this->decliningProducts($params->periodStart, $params->periodEnd, $params->comparisonStart, $params->comparisonEnd),
            'stockouts' => $this->stockouts(),
            'returns' => $this->returnsSummary($params->periodStart, $params->periodEnd),
        ];

        $timeSeries = $this->timeSeries($params->periodStart, $params->periodEnd);

        return new ReportMetricsDTO(
            periodStart: $params->periodStart,
            periodEnd: $params->periodEnd,
            comparisonStart: $params->comparisonStart ?? $params->periodStart->subDays(7),
            comparisonEnd: $params->comparisonEnd ?? $params->periodStart->subDay(),
            kpis: $kpis,
            productInsights: $productInsights,
            timeSeries: $timeSeries,
            meta: ['granularity' => $params->granularity]
        );
    }

    /**
     * @return array<string,float|int|null>
     */
    private function aggregateOrders(?CarbonImmutable $start, ?CarbonImmutable $end): array
    {
        if ($start === null || $end === null) {
            return ['sales' => 0.0, 'orders' => 0, 'items' => 0, 'sessions' => null];
        }

        $orders = (array) DB::table('orders')
            ->selectRaw('COALESCE(SUM(total_price),0) as sales')
            ->selectRaw('COUNT(*) as orders')
            ->whereBetween('order_date', [$start->toDateString(), $end->toDateString()])
            ->first();

        $items = DB::table('order_items')
            ->whereIn('order_id', function ($query) use ($start, $end) {
                $query->select('id')->from('orders')->whereBetween('order_date', [$start->toDateString(), $end->toDateString()]);
            })
            ->selectRaw('COALESCE(SUM(quantity),0) as qty')
            ->first();

        $sessions = $this->countSessions($start, $end);

        return [
            'sales' => (float) ($orders['sales'] ?? 0.0),
            'orders' => (int) ($orders['orders'] ?? 0),
            'items' => (int) ($items?->qty ?? 0),
            'sessions' => $sessions,
        ];
    }

    private function diffMetric(float|int|null $current, float|int|null $previous): array
    {
        $c = $current !== null ? (float) $current : null;
        $p = $previous !== null ? (float) $previous : null;
        if ($p === null || abs($p) < 1e-6) {
            return ['current' => $c, 'previous' => $p, 'delta' => null, 'delta_pct' => null];
        }
        $delta = ($c ?? 0.0) - $p;
        $deltaPct = ($delta / $p) * 100;
        return ['current' => $c, 'previous' => $p, 'delta' => $delta, 'delta_pct' => $deltaPct];
    }

    private function buildKpis(array $current, array $previous): array
    {
        return [
            'sales' => $this->diffMetric($current['sales'], $previous['sales']),
            'orders' => $this->diffMetric($current['orders'], $previous['orders']),
            'aov' => $this->diffMetric($this->safeDivide($current['sales'], $current['orders']), $this->safeDivide($previous['sales'], $previous['orders'])),
            'conversion_rate' => $this->diffMetric($this->safeDivide($current['orders'], $current['sessions']), $this->safeDivide($previous['orders'], $previous['sessions'])),
            'avg_items_per_order' => $this->diffMetric($this->safeDivide($current['items'], $current['orders']), $this->safeDivide($previous['items'], $previous['orders'])),
        ];
    }

    private function safeDivide(float|int $numerator, float|int|null $denominator): ?float
    {
        if ($denominator === null || (float) $denominator === 0.0) return null;
        return (float) $numerator / (float) $denominator;
    }

    private function topProducts(CarbonImmutable $start, CarbonImmutable $end): array
    {
        return DB::table('order_items AS oi')
            ->selectRaw('oi.product_id, p.sku, p.name, SUM(oi.price_at_purchase * oi.quantity) AS revenue, SUM(oi.quantity) AS units')
            ->join('orders AS o', 'o.id', '=', 'oi.order_id')
            ->join('products AS p', 'p.id', '=', 'oi.product_id')
            ->whereBetween('o.order_date', [$start->toDateString(), $end->toDateString()])
            ->groupBy('oi.product_id', 'p.sku', 'p.name')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get()->map(fn ($r) => [
                'product_id' => (int) $r->product_id,
                'sku' => (string) $r->sku,
                'name' => (string) $r->name,
                'revenue' => (float) $r->revenue,
                'units' => (int) $r->units,
                'variation_pct' => null,
            ])->all();
    }

    private function decliningProducts(CarbonImmutable $start, CarbonImmutable $end, ?CarbonImmutable $prevStart, ?CarbonImmutable $prevEnd): array
    {
        if ($prevStart === null || $prevEnd === null) return [];

        $current = $this->productRevenueForPeriod($start, $end);
        $previous = $this->productRevenueForPeriod($prevStart, $prevEnd);
        $decliners = [];
        foreach ($current as $sku => $row) {
            $prev = $previous[$sku] ?? null;
            if (!$prev || $prev['revenue'] <= 0) continue;
            $var = (($row['revenue'] - $prev['revenue']) / $prev['revenue']) * 100;
            if ($var <= -30) {
                $decliners[] = [
                    'product_id' => $row['product_id'], 'sku' => $row['sku'], 'name' => $row['name'],
                    'revenue' => $row['revenue'], 'units' => $row['units'], 'variation_pct' => $var,
                ];
            }
        }
        usort($decliners, fn ($a,$b) => $a['variation_pct'] <=> $b['variation_pct']);
        return array_slice($decliners, 0, 10);
    }

    private function productRevenueForPeriod(CarbonImmutable $start, CarbonImmutable $end): array
    {
        $rows = DB::table('order_items AS oi')
            ->selectRaw('p.sku, p.id as product_id, p.name, SUM(oi.price_at_purchase * oi.quantity) AS revenue, SUM(oi.quantity) AS units')
            ->join('orders AS o', 'o.id', '=', 'oi.order_id')
            ->join('products AS p', 'p.id', '=', 'oi.product_id')
            ->whereBetween('o.order_date', [$start->toDateString(), $end->toDateString()])
            ->groupBy('p.sku', 'p.id', 'p.name')
            ->get();

        $result = [];
        foreach ($rows as $r) {
            $result[$r->sku] = [
                'product_id' => (int) $r->product_id,
                'sku' => (string) $r->sku,
                'name' => (string) $r->name,
                'revenue' => (float) $r->revenue,
                'units' => (int) $r->units,
            ];
        }
        return $result;
    }

    private function stockouts(): array
    {
        $rows = DB::table('products')->select('id','sku','name','stock','updated_at')->where('stock','<=',0)->limit(10)->get();
        return $rows->map(function ($r) {
            $updatedAt = $r->updated_at ? CarbonImmutable::parse($r->updated_at) : null;
            $days = $updatedAt ? $updatedAt->diffInDays(CarbonImmutable::now()) : 0;
            return ['product_id'=>(int)$r->id,'sku'=>(string)$r->sku,'name'=>(string)$r->name,'days_out'=>$days];
        })->all();
    }

    private function returnsSummary(CarbonImmutable $start, CarbonImmutable $end): array
    {
        if (!Schema::hasTable('returns')) return [];
        return DB::table('returns as r')
            ->selectRaw('r.product_id, p.sku, p.name, COUNT(*) as returns, COALESCE(SUM(r.refund_amount),0) as refund_total')
            ->join('products as p', 'p.id', '=', 'r.product_id')
            ->whereBetween('r.created_at', [$start->startOfDay(), $end->endOfDay()])
            ->groupBy('r.product_id','p.sku','p.name')
            ->orderByDesc('returns')->limit(10)
            ->get()->map(fn($r)=>[
                'product_id'=>(int)$r->product_id,'sku'=>(string)$r->sku,'name'=>(string)$r->name,
                'returns'=>(int)$r->returns,'refund_total'=>(float)$r->refund_total,
            ])->all();
    }

    private function timeSeries(CarbonImmutable $start, CarbonImmutable $end): array
    {
        return DB::table('orders')
            ->selectRaw('order_date as date, COALESCE(SUM(total_price),0) as revenue, COUNT(*) as orders')
            ->whereBetween('order_date', [$start->toDateString(), $end->toDateString()])
            ->groupBy('order_date')->orderBy('order_date')
            ->get()->map(fn($r)=>[
                'date'=>(string)$r->date,'revenue'=>(float)$r->revenue,'orders'=>(int)$r->orders,
            ])->all();
    }

    private function countSessions(CarbonImmutable $start, CarbonImmutable $end): ?int
    {
        if (!Schema::hasTable('sessions')) return null;
        return DB::table('sessions')->whereBetween('last_activity', [$start->timestamp, $end->endOfDay()->timestamp])->count();
    }
}

