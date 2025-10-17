<?php

namespace Tests\Feature;

use App\Models\Ai\AiChat;
use App\Models\Shop\Order;
use App\Models\Shop\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopAiChatTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['ai.driver' => 'fake']);
        $this->withoutExceptionHandling();
    }

    public function test_user_can_receive_cart_proposal_and_confirm_order(): void
    {
        $productA = Product::factory()->create([
            'sku' => 'TSHIRT-GREEN-M',
            'name' => 'T-shirt Urban vert',
            'price' => 29.90,
            'stock' => 10,
            'attributes' => ['taille' => ['S', 'M', 'L'], 'couleur' => ['vert']],
        ]);

        $productB = Product::factory()->create([
            'sku' => 'CAP-BLACK-OS',
            'name' => 'Casquette noire',
            'price' => 19.90,
            'stock' => 5,
            'attributes' => ['taille' => ['Taille unique'], 'couleur' => ['noir']],
        ]);

        $messageResponse = $this->postJson('/shop/ai-chat/message', [
            'message' => 'Je veux 2 t-shirts verts taille M et une casquette noire, livraison Tunis',
        ]);

        $messageResponse->assertOk();

        $payload = $messageResponse->json();

        $this->assertNotNull($payload['cart_token']);
        $this->assertCount(2, $payload['cart']['items']);

        $sessionUuid = $payload['session_uuid'] ?? AiChat::firstOrFail()->session_uuid;

        $confirmResponse = $this->withCookie('ai_chat_session', $sessionUuid)
            ->postJson('/shop/ai-chat/confirm', [
                'cart_token' => $payload['cart_token'],
                'session_uuid' => $sessionUuid,
            ]);

        $confirmResponse->assertOk();

        $order = Order::with('items')->first();
        $this->assertNotNull($order);
        $this->assertSame('pending_payment', $order->status);
        $this->assertCount(2, $order->items);
        $this->assertEquals(8, $productA->fresh()->stock);
        $this->assertEquals(4, $productB->fresh()->stock);
    }
}
