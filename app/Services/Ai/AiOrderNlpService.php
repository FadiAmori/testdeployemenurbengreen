<?php

namespace App\Services\Ai;

use App\DataTransferObjects\Ai\ParsedCartResult;
use Illuminate\Support\Facades\Log;

final class AiOrderNlpService
{
    private const SCHEMA = [
        'type' => 'object',
        'required' => ['items', 'confidence'],
        'properties' => [
            'items' => [
                'type' => 'array',
                'items' => [
                    'type' => 'object',
                    'required' => ['sku_or_name', 'qty', 'variant'],
                    'properties' => [
                        'sku_or_name' => ['type' => 'string'],
                        'qty' => ['type' => 'integer', 'minimum' => 1],
                        'variant' => [
                            'type' => 'object',
                            'properties' => [
                                'taille' => ['type' => 'string'],
                                'couleur' => ['type' => 'string'],
                            ],
                            'additionalProperties' => ['type' => 'string'],
                        ],
                    ],
                ],
            ],
            'shipping_address' => [
                'type' => 'object',
                'properties' => [
                    'city' => ['type' => 'string'],
                    'details' => ['type' => 'string'],
                ],
            ],
            'notes' => ['type' => 'string'],
            'confidence' => ['type' => 'number', 'minimum' => 0, 'maximum' => 1],
            'clarification' => ['type' => 'string'],
        ],
    ];

    private readonly AiClientInterface $client;

    public function __construct(AiClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @param array<string,mixed> $catalogIndex
     */
    public function parseToCart(string $userUtterance, array $catalogIndex): ParsedCartResult
    {
        $sanitizedUtterance = $this->sanitizeUtterance($userUtterance);
        $payload = [
            'system_prompt' => $this->systemPrompt(),
            'examples' => $this->fewShotExamples(),
            'json_schema' => self::SCHEMA,
            'catalog' => $catalogIndex,
            'user_message' => $sanitizedUtterance,
        ];

        Log::debug('AI order NLP prompt', [
            'payload' => $this->maskForLogging($payload),
        ]);

        $response = $this->client->request('/nl-order', $payload);

        Log::debug('AI order NLP response', [
            'response' => $this->maskForLogging($response),
        ]);

        return ParsedCartResult::fromArray($response);
    }

    private function systemPrompt(): string
    {
        return <<<PROMPT
Tu es un assistant d’achat pour une boutique FR. À partir d’une requête utilisateur, retourne un JSON respectant STRICTEMENT le schéma fourni.

Résous les noms ambigus grâce au catalogue (SKU, variantes).

Si une information manque (taille, couleur, quantité, modèle), pose UNE unique question de clarification courte via le champ "clarification".

Respecte les quantités demandées et propose uniquement des variantes existantes.

Ne crée jamais de SKU absent du catalogue.

Schéma de sortie:
{
  "items":[{"sku_or_name":"string","qty":int,"variant":{"taille?":"string","couleur?":"string"}}],
  "shipping_address":{"city?":"string","details?":"string"},
  "notes?":"string",
  "confidence": float,
  "clarification?":"string"
}
PROMPT;
    }

    /**
     * @return array<int, array<string,mixed>>
     */
    private function fewShotExamples(): array
    {
        return [
            [
                'catalog_slice' => [
                    ['sku' => 'TSHIRT-GREEN-M', 'name' => 'T-shirt Urban vert', 'variants' => ['taille' => ['S', 'M', 'L'], 'couleur' => ['vert']]],
                    ['sku' => 'CAP-BLACK-OS', 'name' => 'Casquette noire', 'variants' => ['taille' => ['Taille unique'], 'couleur' => ['noir']]],
                ],
                'user' => 'Je veux 2 t-shirts verts taille M et une casquette noire, livraison Tunis.',
                'assistant' => [
                    'items' => [
                        [
                            'sku_or_name' => 'TSHIRT-GREEN-M',
                            'qty' => 2,
                            'variant' => ['taille' => 'M', 'couleur' => 'vert'],
                        ],
                        [
                            'sku_or_name' => 'CAP-BLACK-OS',
                            'qty' => 1,
                            'variant' => ['taille' => 'Taille unique', 'couleur' => 'noir'],
                        ],
                    ],
                    'shipping_address' => ['city' => 'Tunis'],
                    'notes' => 'Livraison standard demandée.',
                    'confidence' => 0.94,
                ],
            ],
            [
                'catalog_slice' => [
                    ['sku' => 'TSHIRT-WHITE', 'name' => 'T-shirt Urban blanc', 'variants' => ['taille' => ['S', 'M', 'L'], 'couleur' => ['blanc']]],
                ],
                'user' => 'Un t-shirt blanc mais je ne sais pas quelle taille.',
                'assistant' => [
                    'items' => [
                        [
                            'sku_or_name' => 'TSHIRT-WHITE',
                            'qty' => 1,
                            'variant' => [],
                        ],
                    ],
                    'confidence' => 0.55,
                    'clarification' => 'Pour le t-shirt blanc, souhaitez-vous la taille S, M ou L ?',
                ],
            ],
            [
                'catalog_slice' => [
                    ['sku' => 'PLANT-ALOE', 'name' => 'Aloe Vera', 'variants' => ['taille' => ['S', 'M']]],
                ],
                'user' => 'Ajoute deux Aloe Vera, taille moyenne.',
                'assistant' => [
                    'items' => [
                        [
                            'sku_or_name' => 'PLANT-ALOE',
                            'qty' => 2,
                            'variant' => ['taille' => 'M'],
                        ],
                    ],
                    'confidence' => 0.88,
                ],
            ],
        ];
    }

    private function sanitizeUtterance(string $utterance): string
    {
        $maskEmails = preg_replace('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i', '[email masqué]', $utterance);
        $maskDigits = preg_replace('/\d{4,}/', '[numéro masqué]', $maskEmails ?? $utterance);

        return trim((string) $maskDigits);
    }

    /**
     * @param array<string,mixed> $data
     *
     * @return array<string,mixed>
     */
    private function maskForLogging(array $data): array
    {
        $clone = $data;
        if (isset($clone['user_message'])) {
            $clone['user_message'] = $this->sanitizeUtterance((string) $clone['user_message']);
        }

        if (isset($clone['shipping_address']) && is_array($clone['shipping_address'])) {
            $clone['shipping_address'] = array_filter(
                $clone['shipping_address'],
                static fn ($key): bool => $key === 'city',
                ARRAY_FILTER_USE_KEY
            );
        }

        return $clone;
    }
}
