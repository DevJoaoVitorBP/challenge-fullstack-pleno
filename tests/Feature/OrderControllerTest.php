<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $admin;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['is_admin' => false]);
        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->product = Product::factory()->create(['quantity' => 100]);
    }

    public function test_user_can_get_orders(): void
    {
        Order::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/orders');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }

    public function test_user_can_view_order(): void
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->getJson("/api/v1/orders/{$order->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'id' => $order->id,
            ],
        ]);
    }

    public function test_user_cannot_view_other_user_order(): void
    {
        $other_user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $other_user->id]);

        $response = $this->actingAs($this->user)->getJson("/api/v1/orders/{$order->id}");

        $response->assertStatus(403);
    }

    public function test_user_can_create_order_from_cart(): void
    {
        // Add item to cart
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        $cart->items()->create([
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/v1/orders', [
            'shipping_address' => [
                'street' => '123 Main St',
                'city' => 'New York',
                'state' => 'NY',
                'zip' => '10001',
            ],
            'billing_address' => [
                'street' => '123 Main St',
                'city' => 'New York',
                'state' => 'NY',
                'zip' => '10001',
            ],
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
        ]);
    }

    public function test_order_requires_valid_address(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/orders', [
            'shipping_address' => [],
            'billing_address' => [],
        ]);

        $response->assertStatus(422);
    }

    public function test_admin_can_update_order_status(): void
    {
        $order = Order::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($this->admin)->putJson("/api/v1/orders/{$order->id}/status", [
            'status' => 'processing',
        ]);

        $response->assertStatus(200);
    }

    public function test_user_cannot_update_order_status(): void
    {
        $order = Order::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($this->user)->putJson("/api/v1/orders/{$order->id}/status", [
            'status' => 'processing',
        ]);

        $response->assertStatus(403);
    }
}
