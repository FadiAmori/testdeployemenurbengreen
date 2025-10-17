<?php

namespace App\Services\Ai;

use App\DataTransferObjects\Reports\AiReportResult;
use App\DataTransferObjects\Reports\ReportMetricsDTO;
use App\DataTransferObjects\Reports\ReportParams;
use Illuminate\Support\Facades\Log;

final class AiReportService
{
    public function __construct(private readonly AiClientInterface $client) {}

    public function generate(ReportMetricsDTO $metrics, ReportParams $params): AiReportResult
    {
        $payload = [
            'system_prompt' => $this->systemPrompt($params),
            'metrics' => $metrics->toArray(),
            'period_start' => $params->periodStart->toDateString(),
            'period_end' => $params->periodEnd->toDateString(),
        ];

        Log::debug('AI report prompt payload', ['payload' => ['period'=>$payload['period_start'].'→'.$payload['period_end']]]);

        $markdown = '';
        $raw = [];
        try {
            $response = $this->client->request('/admin/report', $payload, ['timeout' => 45]);
            $raw = $response;
            $markdown = (string) ($response['markdown'] ?? '');
        } catch (\Throwable $e) {
            Log::warning('AI report HTTP error, falling back to local generator', ['error' => $e->getMessage()]);
        }

        if (trim($markdown) === '') {
            $markdown = $this->localMarkdownFromMetrics($metrics, $params);
        }

        return new AiReportResult(markdown: $markdown, rawResponse: $raw);
    }

    private function systemPrompt(ReportParams $params): string
    {
        return <<<PROMPT
Tu es un analyste business pour une boutique e-commerce. Génère un rapport détaillé structuré en Markdown.

Contraintes :
- Respecte strictement la structure suivante :
# Rapport IA – Période {$params->periodStart->toDateString()} → {$params->periodEnd->toDateString()}
## Résumé exécutif (≤120 mots)
## KPI (table N vs N-1) + variation %
## Ce qui fonctionne (bullet points)
## Ce qui ne fonctionne pas (bullet points)
## Analyse & causes probables (référencer KPI/produits)
## Recommandations actionnables (priorisées P1/P2, effort estimé)
## Risques & points de vigilance

- Explique les variations observées en citant uniquement les métriques fournies.
- N’invente AUCUNE donnée.
- Ton style est clair, concis, orienté décisions.
PROMPT;
    }

    private function localMarkdownFromMetrics(ReportMetricsDTO $m, ReportParams $p): string
    {
        $arr = $m->toArray();
        $k = $arr['kpis'] ?? [];
        $fmtMoney = static fn ($v) => number_format((float) ($v ?? 0), 0, ',', ' ') . '€';
        $fmtNum = static fn ($v) => number_format((float) ($v ?? 0), 0, ',', ' ');

        $sales = $k['sales']['current'] ?? 0; $orders = $k['orders']['current'] ?? 0;
        $summary = "Ventes: ".$fmtMoney($sales)." — Commandes: ".$fmtNum($orders).".";

        $lines = [];
        $lines[] = '# Rapport IA – Période '.$p->periodStart->toDateString().' → '.$p->periodEnd->toDateString();
        $lines[] = '## Résumé exécutif (≤120 mots)';
        $lines[] = $summary;
        $lines[] = '';
        $lines[] = '## KPI (table N vs N-1) + variation %';
        $lines[] = '| KPI | N | N-1 | Variation |';
        $lines[] = '|---|---:|---:|---:|';

        $rows = [
            ['Ventes', $k['sales'] ?? null, true],
            ['Commandes', $k['orders'] ?? null, false],
            ['AOV', $k['aov'] ?? null, true],
            ['Conversion', $k['conversion_rate'] ?? null, false],
        ];
        foreach ($rows as [$name, $row, $money]) {
            $cur = $row['current'] ?? null; $prev = $row['previous'] ?? null; $dp = $row['delta_pct'] ?? null;
            $curTxt = $money ? $fmtMoney($cur) : $fmtNum($cur);
            $prevTxt = $money ? $fmtMoney($prev) : $fmtNum($prev);
            $dpTxt = is_null($dp) ? '—' : number_format((float)$dp, 1, ',', ' ').' %';
            $lines[] = "| $name | $curTxt | $prevTxt | $dpTxt |";
        }

        // Positives / negatives from product insights if available
        $pos = [];
        foreach (array_slice(($arr['product_insights']['top_products'] ?? []), 0, 3) as $p1) {
            $pos[] = ($p1['name'] ?? $p1['sku'] ?? '') ?: '';
        }
        $neg = [];
        foreach (array_slice(($arr['product_insights']['decliners'] ?? []), 0, 3) as $n1) {
            $neg[] = ($n1['name'] ?? $n1['sku'] ?? '') ?: '';
        }
        $lines[] = '';
        $lines[] = '## Ce qui fonctionne (bullet points)';
        if ($pos) { foreach ($pos as $p2) { $lines[] = '- '.$p2; } } else { $lines[] = '- —'; }
        $lines[] = '';
        $lines[] = '## Ce qui ne fonctionne pas (bullet points)';
        if ($neg) { foreach ($neg as $n2) { $lines[] = '- '.$n2; } } else { $lines[] = '- —'; }

        $lines[] = '';
        $lines[] = '## Analyse & causes probables (référencer KPI/produits)';
        $lines[] = '- Variations liées aux promotions, disponibilité et trafic. Voir KPI et produits listés.';

        $lines[] = '';
        $lines[] = '## Recommandations actionnables (priorisées P1/P2, effort estimé)';
        if ($neg) {
            foreach ($neg as $n3) {
                $lines[] = '- P1 (M) : Investiguer le déclin de '.$n3.' et adapter la promotion/stock.';
            }
        } else {
            $lines[] = '- P2 (S) : Continuer les actions performantes et tester une nouvelle accroche emailing.';
        }

        $lines[] = '';
        $lines[] = '## Risques & points de vigilance';
        $lines[] = '- Ruptures possibles et saisonnalité. Surveiller les alertes stock.';

        return implode("\n", $lines);
    }
}
