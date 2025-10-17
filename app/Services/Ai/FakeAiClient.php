<?php

namespace App\Services\Ai;

use Illuminate\Support\Str;

final class FakeAiClient implements AiClientInterface
{
    /**
     * @param array<string,mixed> $payload
     * @param array{timeout?:float} $options
     *
     * @return array<string,mixed>
     */
    public function request(string $endpoint, array $payload, array $options = []): array
    {
        if (Str::contains($endpoint, 'nl-order')) {
            return $this->fakeOrderResponse($payload);
        }

        if (Str::contains($endpoint, 'admin/report')) {
            return $this->fakeReportResponse($payload);
        }

        return [];
    }

    /**
     * @param array<string,mixed> $payload
     *
     * @return array<string,mixed>
     */
    private function fakeOrderResponse(array $payload): array
    {
        // Naive local parser to make the feature usable without a real model.
        // Maps "<qty> <mot>" to the closest catalog product whose name contains <mot>.
        $utterance = trim((string) ($payload['user_message'] ?? ''));
        /** @var array{products?:array<int,array{sku:string,name:string}>} $catalog */
        $catalog = (array) ($payload['catalog'] ?? []);
        $products = (array) ($catalog['products'] ?? []);

        $items = [];
        $lc = Str::lower($utterance);

        // Extract pairs like "2 dza", "1 test", "3x cactus"
        preg_match_all('/(\d{1,3})\s*(?:x|×)?\s*([\p{L}\-]{2,})/ui', $utterance, $matches, PREG_SET_ORDER);

        foreach ($matches as $m) {
            $qty = max(1, (int) $m[1]);
            $token = Str::lower(trim($m[2]));

            $best = $this->bestCatalogMatch($token, $products);
            if ($best !== null) {
                $items[] = [
                    'sku_or_name' => $best['sku'],
                    'qty' => $qty,
                    'variant' => new \stdClass(),
                ];
            }
        }

        // If nothing matched, try any product name that appears in the sentence without qty → default 1
        if ($items === []) {
            foreach ($products as $p) {
                $name = Str::lower($p['name'] ?? '');
                if ($name !== '' && Str::contains($lc, $name)) {
                    $items[] = [
                        'sku_or_name' => $p['sku'],
                        'qty' => 1,
                        'variant' => new \stdClass(),
                    ];
                }
            }
        }

        // Shipping city quick guess (ex: "à Tunis" or "livraison Tunis")
        $city = null;
        if (preg_match('/(?:\bà\s+|\blivraison\s+)([\p{L}\- ]{2,20})/ui', $utterance, $m)) {
            $city = trim($m[1]);
        } elseif (Str::contains($lc, 'tunis')) {
            $city = 'Tunis';
        }

        if ($items !== []) {
            return [
                'items' => $items,
                'shipping_address' => $city ? ['city' => $city] : new \stdClass(),
                'confidence' => 0.85,
            ];
        }

        return [
            'items' => [],
            'confidence' => 0.3,
            'clarification' => 'Pouvez-vous préciser les produits voulus ?',
        ];
    }

    /**
     * @param array<int,array{sku:string,name:string}> $products
     * @return array{sku:string,name:string}|null
     */
    private function bestCatalogMatch(string $token, array $products): ?array
    {
        $token = Str::lower($token);
        $tokenNorm = preg_replace('/[^a-z0-9]+/i', '', Str::ascii($token));
        $best = null;
        $bestScore = 0;

        foreach ($products as $p) {
            $name = (string) ($p['name'] ?? '');
            $sku = (string) ($p['sku'] ?? '');
            $nameLc = Str::lower($name);
            $nameNorm = preg_replace('/[^a-z0-9]+/i', '', Str::ascii($nameLc));
            $skuNorm = preg_replace('/[^a-z0-9]+/i', '', Str::lower($sku));

            $score = 0;
            if ($skuNorm !== '' && $tokenNorm === $skuNorm) {
                $score = 100;
            } elseif ($nameNorm !== '' && Str::contains($nameNorm, $tokenNorm)) {
                $score = strlen($tokenNorm);
            } elseif ($nameLc !== '' && Str::contains($nameLc, $token)) {
                $score = strlen($token);
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = ['sku' => $sku, 'name' => $name];
            }
        }

        return $bestScore > 0 ? $best : null;
    }

    /**
     * @param array<string,mixed> $payload
     *
     * @return array<string,mixed>
     */
    private function fakeReportResponse(array $payload): array
    {
        return [
            'markdown' => "# Rapport IA – Période {$payload['period_start']} → {$payload['period_end']}\n"
                . "## Résumé exécutif (≤120 mots)\n"
                . "Les ventes progressent grâce à la campagne email.\n"
                . "## KPI (table N vs N-1) + variation %\n"
                . "| KPI | N | N-1 | Variation |\n| Ventes | 1500€ | 1300€ | +15% |\n"
                . "## Ce qui fonctionne (bullet points)\n"
                . "- Promotions sur les t-shirts\n"
                . "## Ce qui ne fonctionne pas (bullet points)\n"
                . "- Stock limité casquette noire\n"
                . "## Analyse & causes probables (référencer KPI/produits)\n"
                . "- +15% ventes grâce à emailing ciblé.\n"
                . "## Recommandations actionnables (priorisées P1/P2, effort estimé)\n"
                . "- P1 (faible effort) : réapprovisionner casquette noire.\n"
                . "## Risques & points de vigilance\n"
                . "- Risque de rupture prolongée.",
        ];
    }
}
