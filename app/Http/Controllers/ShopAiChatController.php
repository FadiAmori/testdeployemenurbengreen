<?php

namespace App\Http\Controllers;

use App\DataTransferObjects\Ai\ProposedCart;
use App\DataTransferObjects\Ai\ProposedCartItem;
use App\DataTransferObjects\Ai\ShippingAddress;
use App\Jobs\ProcessAiUserMessageJob;
use App\Models\Ai\AiChat;
use App\Models\Ai\AiChatMessage;
use App\Models\Shop\Order;
use App\Models\Shop\OrderItem;
use App\Models\Shop\Product;
use App\Services\Shop\CartProposalSigner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Cookie as SymfonyCookie;

class ShopAiChatController extends Controller
{
    private const COOKIE_NAME = 'ai_chat_session';
    private const COOKIE_TTL_MINUTES = 60 * 24 * 7;

    public function history(Request $request): JsonResponse
    {
        $chat = $this->resolveChat($request, false);

        if ($chat === null) {
            return response()->json([
                'messages' => [],
                'cart' => null,
            ]);
        }

        $lastAssistantMessage = $chat->messages()
            ->where('role', AiChatMessage::ROLE_ASSISTANT)
            ->latest()
            ->first();

        return response()->json([
            'session_uuid' => $chat->session_uuid,
            'messages' => $chat->messages
                ->sortBy('created_at')
                ->map(fn (AiChatMessage $message) => [
                    'role' => $message->role,
                    'content' => $message->content,
                    'created_at' => $message->created_at?->toIso8601String(),
                ])
                ->values(),
            'cart' => $lastAssistantMessage?->payload['proposal'] ?? null,
        ]);
    }

    public function message(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:500'],
        ]);

        $chat = $this->resolveChat($request, true);

        $job = new ProcessAiUserMessageJob($chat->id, $validated['message']);
        /** @var array{assistant_message:string,cart:array<string,mixed>} $result */
        $result = app()->call([$job, 'handle']);

        $signer = CartProposalSigner::fromAppKey();
        $cartToken = null;
        if (! empty($result['cart']['items']) && empty($result['cart']['clarification'])) {
            $cartToken = $signer->encode($this->arrayToProposedCart($result['cart']));
        }

        return response()->json([
            'assistant_message' => $result['assistant_message'],
            'cart' => $result['cart'],
            'cart_token' => $cartToken,
            'session_uuid' => $chat->session_uuid,
        ]);
    }

    public function confirm(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cart_token' => ['required', 'string'],
            'session_uuid' => ['nullable', 'uuid'],
        ]);

        $chat = $this->resolveChat($request, false, $validated['session_uuid'] ?? null);
        if ($chat === null) {
            abort(400, 'Session de chat introuvable.');
        }

        $signer = CartProposalSigner::fromAppKey();

        try {
            $cartPayload = $signer->decode($validated['cart_token']);
        } catch (\Throwable $exception) {
            Log::warning('Tentative de confirmation de panier invalide', ['error' => $exception->getMessage()]);
            abort(422, 'Panier invalide ou expiré.');
        }

        if (empty($cartPayload['items']) || ! is_array($cartPayload['items'])) {
            abort(422, 'Le panier est vide.');
        }

        // Place items into user's cart and redirect to checkout
        if (auth()->check()) {
            /** @var \App\Models\Shop\Cart $cart */
            $cart = \App\Models\Shop\Cart::firstOrCreate(
                ['user_id' => auth()->id()],
                ['total_price' => 0]
            );

            foreach ($cartPayload['items'] as $item) {
                $cartItem = $cart->items()->firstOrCreate(
                    ['product_id' => (int) $item['product_id']],
                    ['quantity' => 0]
                );
                $cartItem->increment('quantity', (int) $item['quantity']);
            }
        }

        $chat->messages()->create([
            'role' => AiChatMessage::ROLE_ASSISTANT,
            'content' => 'Panier mis à jour. Vous pouvez finaliser la commande au checkout.',
            'payload' => ['cart' => $cartPayload],
        ]);

        return response()->json([
            'message' => 'Panier mis à jour. Redirection vers le checkout…',
            'redirect_url' => route('front.checkout'),
        ]);
    }

    private function resolveChat(Request $request, bool $createIfMissing, ?string $explicitSessionUuid = null): ?AiChat
    {
        $sessionUuid = $explicitSessionUuid ?? $request->cookie(self::COOKIE_NAME);

        if ($sessionUuid) {
            $chat = AiChat::query()->where('session_uuid', $sessionUuid)->first();
            if ($chat !== null) {
                if ($request->user() && $chat->user_id === null) {
                    $chat->update(['user_id' => $request->user()->id]);
                }

                return $chat;
            }
        }

        if (! $createIfMissing) {
            return null;
        }

        $sessionUuid = (string) Str::uuid();
        $chat = AiChat::query()->create([
            'session_uuid' => $sessionUuid,
            'user_id' => $request->user()?->id,
        ]);

        Cookie::queue(new SymfonyCookie(
            name: self::COOKIE_NAME,
            value: $sessionUuid,
            expire: now()->addMinutes(self::COOKIE_TTL_MINUTES),
            path: '/',
            secure: config('session.secure', false),
            httpOnly: false,
            raw: false,
            sameSite: config('session.same_site', 'lax')
        ));

        return $chat;
    }

    /**
     * @param array<string,mixed> $cart
     */
    private function arrayToProposedCart(array $cart): ProposedCart
    {
        $items = array_map(
            static fn (array $item) => new ProposedCartItem(
                productId: (int) $item['product_id'],
                sku: $item['sku'],
                name: $item['name'],
                quantity: (int) $item['quantity'],
                unitPrice: (float) $item['unit_price'],
                lineTotal: (float) $item['line_total'],
                variant: $item['variant'] ?? [],
                imageUrl: $item['image_url'] ?? null
            ),
            $cart['items'] ?? []
        );

        $shippingAddress = isset($cart['shipping_address'])
            ? new ShippingAddress(
                city: $cart['shipping_address']['city'] ?? null,
                details: $cart['shipping_address']['details'] ?? null
            )
            : null;

        return new ProposedCart(
            items: $items,
            subtotal: (float) ($cart['totals']['subtotal'] ?? 0.0),
            taxTotal: (float) ($cart['totals']['tax'] ?? 0.0),
            discountTotal: (float) ($cart['totals']['discount'] ?? 0.0),
            grandTotal: (float) ($cart['totals']['total'] ?? 0.0),
            currency: (string) ($cart['totals']['currency'] ?? 'EUR'),
            confidence: (float) ($cart['confidence'] ?? 0.0),
            shippingAddress: $shippingAddress,
            notes: $cart['notes'] ?? null,
            clarificationRequest: $cart['clarification'] ?? null
        );
    }

    /**
     * @param array<string,mixed> $shipping
     */
    private function formatShippingAddress(array $shipping): string
    {
        $parts = [];

        if (! empty($shipping['city'])) {
            $parts[] = 'Ville: ' . $shipping['city'];
        }

        if (! empty($shipping['details'])) {
            $parts[] = 'Détails: ' . $shipping['details'];
        }

        return implode(' | ', $parts);
    }
}
