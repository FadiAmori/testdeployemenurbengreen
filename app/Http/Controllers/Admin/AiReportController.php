<?php

namespace App\Http\Controllers\Admin;

use App\DataTransferObjects\Reports\ReportParams;
use App\Http\Controllers\Controller;
use App\Models\Ai\AiReport;
use App\Services\Ai\AiReportService;
use App\Services\Reports\ReportMetricsService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class AiReportController extends Controller
{
    public function index(Request $request): View
    {
        $reports = AiReport::query()->latest('created_at')->limit(24)->get();
        $selectedId = (int) $request->query('id');
        $selected = $selectedId ? $reports->firstWhere('id', $selectedId) : null;
        if (!$selected) {
            $selected = $reports->first();
        }

        $uiReport = null;
        if ($selected) {
            $m = (array) ($selected->metrics ?? []);
            $kpis = (array) ($m['kpis'] ?? []);
            $ts = (array) ($m['time_series'] ?? []);
            $insights = (array) ($m['product_insights'] ?? []);

            // Build KPI rows with sparklines (derive where possible)
            $sparkRevenue = array_map(fn ($row) => (float) ($row['revenue'] ?? $row['revenue'] ?? 0), $ts);
            $sparkOrders = array_map(fn ($row) => (int) ($row['orders'] ?? 0), $ts);
            $sparkAov = [];
            foreach ($ts as $row) {
                $o = max(1, (int) ($row['orders'] ?? 0));
                $sparkAov[] = $o ? ((float) ($row['revenue'] ?? 0)) / $o : 0;
            }

            $mapName = [
                'sales' => 'Ventes',
                'orders' => 'Commandes',
                'aov' => 'AOV',
                'conversion_rate' => 'Conversion',
                'avg_items_per_order' => 'Articles/commande',
            ];

            $rows = [];
            foreach ($mapName as $key => $label) {
                $row = (array) ($kpis[$key] ?? []);
                $spark = [];
                if ($key === 'sales') $spark = $sparkRevenue;
                elseif ($key === 'orders') $spark = $sparkOrders;
                elseif ($key === 'aov') $spark = $sparkAov;
                $rows[] = [
                    'name' => $label,
                    'current' => $row['current'] ?? null,
                    'previous' => $row['previous'] ?? null,
                    'delta_percent' => $row['delta_pct'] ?? null,
                    'sparkline' => $spark,
                ];
            }

            $positives = array_map(fn ($p) => (string) ($p['name'] ?? $p['sku'] ?? ''), array_slice((array) ($insights['top_products'] ?? []), 0, 3));
            $negatives = array_map(fn ($p) => (string) ($p['name'] ?? $p['sku'] ?? ''), array_slice((array) ($insights['decliners'] ?? []), 0, 3));

            $analysis = [];
            if (!empty($positives)) {
                $analysis[] = ['text' => 'Les ventes ont été tirées par: '.implode(', ', $positives).'.', 'tags' => ['#Ventes', '#TopProduits']];
            }
            if (!empty($negatives)) {
                $analysis[] = ['text' => 'Produits en baisse à investiguer: '.implode(', ', $negatives).'.', 'tags' => ['#Déclin']];
            }

            $stocks = array_slice((array) ($insights['stockouts'] ?? []), 0, 3);
            $recs = [];
            foreach ($stocks as $s) {
                $recs[] = [
                    'text' => 'Réapprovisionner '.$s['name'].' (SKU '.$s['sku'].')',
                    'priority' => 'P1', 'effort' => 'M', 'impact' => 'Élevé',
                    'owner' => ['name' => 'Ops', 'initials' => 'OP'],
                    'due_date' => $selected->period_end?->copy()->addDays(7)->toDateString(),
                    'status' => 'Open',
                ];
            }

            $uiReport = [
                'period' => ['start' => $selected->period_start->toDateString(), 'end' => $selected->period_end->toDateString()],
                'summary' => (string) (str($selected->markdown)->before('## KPI')->trim() ?: 'Synthèse non disponible.'),
                'kpis' => $rows,
                'positives' => $positives,
                'negatives' => $negatives,
                'analysis' => $analysis,
                'recommendations' => $recs,
                'meta' => ['generated_at' => $selected->created_at?->toDateTimeString(), 'author' => 'IA'],
                '__pdf_url' => route('admin.ai-report.pdf', $selected->id),
            ];
        }

        return view('admin.ai-report', [
            'latestReport' => $selected,
            'reports' => $reports,
            'aiBaseUrl' => (string) config('ai.base_url'),
            'uiReport' => $uiReport,
        ]);
    }

    public function generate(Request $request, ReportMetricsService $metricsService, AiReportService $aiService): JsonResponse
    {
        $validated = $request->validate([
            'start' => ['nullable','date'],
            'end' => ['nullable','date','after_or_equal:start'],
            'granularity' => ['nullable','in:day,week,month'],
        ]);

        $start = isset($validated['start']) ? Carbon\CarbonImmutable::parse($validated['start']) : CarbonImmutable::now()->subDays(6)->startOfDay();
        $end = isset($validated['end']) ? CarbonImmutable::parse($validated['end']) : CarbonImmutable::now()->endOfDay();
        $granularity = $validated['granularity'] ?? 'week';

        $periodDays = $start->diffInDays($end) + 1;
        $cmpEnd = $start->subDay();
        $cmpStart = $cmpEnd->subDays($periodDays - 1);

        $params = new ReportParams(
            periodStart: $start,
            periodEnd: $end,
            granularity: $granularity,
            comparisonStart: $cmpStart,
            comparisonEnd: $cmpEnd,
        );

        $metrics = $metricsService->collect($params);
        $ai = $aiService->generate($metrics, $params);

        $report = AiReport::create([
            'period_start' => $start->toDateString(),
            'period_end' => $end->toDateString(),
            'granularity' => $granularity,
            'markdown' => $ai->markdown,
            'metrics' => $metrics->toArray(),
            'created_by' => $request->user()?->id,
        ]);

        return response()->json(['status' => 'ok', 'id' => $report->id]);
    }

    public function latest(): JsonResponse
    {
        $report = AiReport::query()->latest('created_at')->first();
        if (!$report) return response()->json(['message' => 'Aucun rapport disponible.'], 404);
        return response()->json([
            'id' => $report->id,
            'period_start' => $report->period_start->toDateString(),
            'period_end' => $report->period_end->toDateString(),
            'markdown' => $report->markdown,
            'metrics' => $report->metrics,
        ]);
    }

    public function list(): JsonResponse
    {
        $reports = AiReport::query()->latest('created_at')->limit(50)->get()->map(function (AiReport $r) {
            return [
                'id' => $r->id,
                'period' => $r->period_start->toDateString() . ' → ' . $r->period_end->toDateString(),
                'created_at' => $r->created_at?->toDateTimeString(),
                'excerpt' => str($r->markdown)->limit(160)->toString(),
            ];
        });
        return response()->json(['items' => $reports]);
    }

    public function downloadPdf(int $reportId): SymfonyResponse
    {
        $report = AiReport::query()->findOrFail($reportId);
        // Convert markdown to HTML (or fallback) then render a dedicated Blade view for PDF
        $markdownHtml = null;
        try {
            $markdownHtml = method_exists(Str::class, 'markdown') ? (string) Str::markdown($report->markdown) : null;
        } catch (\Throwable $e) {
            $markdownHtml = null;
        }
        if ($markdownHtml === null || trim($markdownHtml) === '') {
            $markdownHtml = '<pre style="white-space:pre-wrap;word-break:break-word;font-family:inherit;">'.e($report->markdown).'</pre>';
        }

        $metrics = (array) ($report->metrics ?? []);
        $html = view('admin.ai-report-pdf', [
            'report' => $report,
            'markdownHtml' => $markdownHtml,
            'metrics' => $metrics,
        ])->render();
        $filename = sprintf('rapport-ia-%s-%s.pdf', $report->period_start->toDateString(), $report->period_end->toDateString());

        // 1) Prefer facade if available
        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)->setPaper('a4');
            return $pdf->download($filename);
        }

        // 2) Try container wrapper binding
        if (app()->bound('dompdf.wrapper')) {
            $pdf = app('dompdf.wrapper');
            $pdf->loadHTML($html)->setPaper('a4');
            return $pdf->download($filename);
        }

        // 3) Fallback to dompdf directly
        if (class_exists(\Dompdf\Dompdf::class)) {
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4');
            $dompdf->render();
            return response($dompdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            ]);
        }

        // 4) No PDF engine installed — return HTML as a download hint
        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'.html"',
        ]);
    }

    public function destroy(AiReport $report): JsonResponse
    {
        $report->delete();
        return response()->json(['status' => 'ok']);
    }
}
