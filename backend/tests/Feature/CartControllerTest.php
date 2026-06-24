<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
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

    #[Test]
    public function user_can_get_cart(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/v1/cart');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }

    #[Test]
    public function user_can_add_item_to_cart(): void
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

    #[Test]
    public function user_cannot_add_item_without_stock(): void
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

    #[Test]
    public function user_can_clear_cart(): void
    {
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        CartItem::factory()->create(['cart_id' => $cart->id, 'product_id' => $this->product->id]);

        $response = $this->actingAs($this->user)->deleteJson('/api/v1/cart');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }

    #[Test]
    public function user_can_update_cart_item(): void
    {
        $product = Product::factory()->create(['quantity' => 20]);

        $this->actingAs($this->user)->postJson('/api/v1/cart/items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $cart = Cart::where('user_id', $this->user->id)->first();
        $item = CartItem::where('cart_id', $cart->id)->first();

        $response = $this->actingAs($this->user)->putJson("/api/v1/cart/items/{$item->id}", [
            'quantity' => 5,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'quantity' => 5,
            ],
        ]);
    }

    #[Test]
    public function update_cart_item_fails_with_insufficient_stock(): void
    {
        $product = Product::factory()->create(['quantity' => 3]);

        $this->actingAs($this->user)->postJson('/api/v1/cart/items', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $cart = Cart::where('user_id', $this->user->id)->first();
        $item = CartItem::where('cart_id', $cart->id)->first();

        $response = $this->actingAs($this->user)->putJson("/api/v1/cart/items/{$item->id}", [
            'quantity' => 99,
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
        ]);
    }

    #[Test]
    public function update_cart_item_fails_for_nonexistent_item(): void
    {
        $response = $this->actingAs($this->user)->putJson('/api/v1/cart/items/99999', [
            'quantity' => 1,
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
        ]);
    }

    #[Test]
    public function user_can_remove_cart_item(): void
    {
        $product = Product::factory()->create(['quantity' => 10]);

        $this->actingAs($this->user)->postJson('/api/v1/cart/items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $cart = Cart::where('user_id', $this->user->id)->first();
        $item = CartItem::where('cart_id', $cart->id)->first();

        $response = $this->actingAs($this->user)->deleteJson("/api/v1/cart/items/{$item->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseMissing('cart_items', ['id' => $item->id]);
    }

    #[Test]
    public function remove_cart_item_fails_for_nonexistent_item(): void
    {
        $response = $this->actingAs($this->user)->deleteJson('/api/v1/cart/items/99999');

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
        ]);
    }
}
