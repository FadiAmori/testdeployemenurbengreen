<?php

namespace App\Jobs;

use App\DataTransferObjects\Ai\ProposedCart;
use App\Models\Ai\AiChat;
use App\Models\Ai\AiChatMessage;
use App\Services\Ai\AiOrderNlpService;
use App\Repositories\ProductRepository;
use App\Services\Shop\AiCartBuilder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessAiUserMessageJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly int $chatId,
        public readonly string $message
    ) {
    }

    /**
     * @return array{assistant_message:string,cart:array<string,mixed>}
     */
    public function handle(
        AiOrderNlpService $nlpService,
        ProductRepository $productRepository,
        AiCartBuilder $cartBuilder
    ): array {
        $chat = AiChat::query()->findOrFail($this->chatId);

        $userMessage = $this->sanitizeForStorage($this->message);
        $chat->messages()->create([
            'role' => AiChatMessage::ROLE_USER,
            'content' => $userMessage,
        ]);

        $catalog = ['products' => $productRepository->buildCatalogIndex()];
        $parsed = $nlpService->parseToCart($userMessage, $catalog);
        $proposal = $cartBuilder->build($parsed);

        $assistantMessage = $this->buildAssistantMessage($proposal);

        $chat->messages()->create([
            'role' => AiChatMessage::ROLE_ASSISTANT,
            'content' => $assistantMessage,
            'payload' => [
                'proposal' => $proposal->toArray(),
            ],
        ]);

        return [
            'assistant_message' => $assistantMessage,
            'cart' => $proposal->toArray(),
        ];
    }

    private function sanitizeForStorage(string $message): string
    {
        $sanitized = preg_replace('/\d{4,}/', '[numéro masqué]', $message) ?? $message;

        return trim($sanitized);
    }

    private function buildAssistantMessage(ProposedCart $proposal): string
    {
        if ($proposal->clarificationRequest !== null) {
            return $proposal->clarificationRequest;
        }

        if ($proposal->items === []) {
            return 'Je n’ai pas pu identifier de produits correspondants. Voulez-vous reformuler votre demande ?';
        }

        $lines = array_map(static function (array $item): string {
            $variantText = '';
            if (! empty($item['variant'])) {
                $variantPairs = [];
                foreach ($item['variant'] as $variantKey => $variantValue) {
                    $variantPairs[] = sprintf('%s: %s', $variantKey, $variantValue);
                }
                $variantText = ' (' . implode(', ', $variantPairs) . ')';
            }

            return sprintf('%d × %s%s', $item['quantity'], $item['name'], $variantText);
        }, $proposal->toArray()['items']);

        return 'Je vous propose ce panier : ' . implode('; ', $lines) . sprintf('. Total estimé: %.2f %s.', $proposal->grandTotal, $proposal->currency);
    }
}
