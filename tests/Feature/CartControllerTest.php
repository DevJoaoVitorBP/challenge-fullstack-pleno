<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->product = Product::factory()->create();
    }

    public function test_user_can_get_cart(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/v1/cart');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }

    public function test_user_can_add_item_to_cart(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/cart/items', [
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'data' => [
                'product_id' => $this->product->id,
                'quantity' => 2,
            ],
        ]);
    }

    public function test_user_cannot_add_item_without_stock(): void
    {
        $product = Product::factory()->create(['quantity' => 0]);

        $response = $this->actingAs($this->user)->postJson('/api/v1/cart/items', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
        ]);
    }

    public function test_user_can_clear_cart(): void
    {
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        CartItem::factory()->create(['cart_id' => $cart->id, 'product_id' => $this->product->id]);

        $response = $this->actingAs($this->user)->deleteJson('/api/v1/cart');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }
}
