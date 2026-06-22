<?php

namespace Tests\Unit;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CartService $service;
    protected User $user;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new CartService();
        $this->user = User::factory()->create();
        $this->product = Product::factory()->create(['quantity' => 100]);
    }

    public function test_can_get_or_create_cart(): void
    {
        $cart = $this->service->getOrCreateCart($this->user->id);

        $this->assertNotNull($cart);
        $this->assertEquals($this->user->id, $cart->user_id);
    }

    public function test_can_add_item_to_cart(): void
    {
        $cartItem = $this->service->addItemToCart($this->user->id, $this->product->id, 2);

        $this->assertNotNull($cartItem);
        $this->assertEquals($this->product->id, $cartItem->product_id);
        $this->assertEquals(2, $cartItem->quantity);
    }

    public function test_can_get_cart_items(): void
    {
        $this->service->addItemToCart($this->user->id, $this->product->id, 2);

        $cart = $this->service->getCartItems($this->user->id);

        $this->assertGreaterThan(0, $cart->items->count());
    }

    public function test_can_clear_cart(): void
    {
        $this->service->addItemToCart($this->user->id, $this->product->id, 2);
        $this->service->clearCart($this->user->id);

        $cart = $this->service->getCartItems($this->user->id);

        $this->assertEquals(0, $cart->items->count());
    }

    public function test_cart_total_calculation(): void
    {
        $this->service->addItemToCart($this->user->id, $this->product->id, 2);

        $total = $this->service->getCartTotal($this->user->id);

        $expected = $this->product->price * 2;
        $this->assertEquals($expected, $total);
    }
}
