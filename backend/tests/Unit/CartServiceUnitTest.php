<?php

namespace Tests\Unit;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CartServiceUnitTest extends TestCase
{
    use RefreshDatabase;

    protected CartService $service;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CartService;
        $this->user = User::factory()->create();
    }

    #[Test]
    public function it_gets_or_creates_cart()
    {
        $cart = $this->service->getOrCreateCart($this->user->id);

        $this->assertNotNull($cart);
        $this->assertEquals($this->user->id, $cart->user_id);
    }

    #[Test]
    public function it_adds_item_to_cart()
    {
        $product = Product::factory()->create();
        $cart = $this->service->getOrCreateCart($this->user->id);

        $this->service->addItemToCart($cart->id, $product->id, 2);

        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
    }

    #[Test]
    public function it_calculates_cart_total()
    {
        $product1 = Product::factory()->create(['price' => 50.00]);
        $product2 = Product::factory()->create(['price' => 30.00]);

        $cart = $this->service->getOrCreateCart($this->user->id);

        $this->service->addItemToCart($cart->id, $product1->id, 2); // 100
        $this->service->addItemToCart($cart->id, $product2->id, 1); // 30

        $total = $this->service->getCartTotal($cart->id);

        $this->assertEquals(130.00, $total);
    }

    #[Test]
    public function it_clears_cart()
    {
        $product = Product::factory()->create(['quantity' => 10]);

        $cart = $this->service->getOrCreateCart($this->user->id);
        $this->service->addItemToCart($cart->id, $product->id, 2);

        $items = CartItem::where('cart_id', $cart->id)->get();
        $this->assertCount(1, $items);

        $this->service->clearCart($cart->id);

        $items = CartItem::where('cart_id', $cart->id)->get();
        $this->assertCount(0, $items);
    }

    #[Test]
    public function it_returns_same_cart_on_second_call()
    {
        $cart1 = $this->service->getOrCreateCart($this->user->id);
        $cart2 = $this->service->getOrCreateCart($this->user->id);

        $this->assertEquals($cart1->id, $cart2->id);
    }

    #[Test]
    public function it_accumulates_quantity_when_adding_same_product_twice()
    {
        $product = Product::factory()->create(['quantity' => 20]);

        $this->service->addItemToCart($this->user->id, $product->id, 3);
        $this->service->addItemToCart($this->user->id, $product->id, 2);

        $cart = $this->service->getOrCreateCart($this->user->id);
        $item = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->first();

        $this->assertEquals(5, $item->quantity);
    }

    #[Test]
    public function it_throws_exception_when_adding_unavailable_product()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Produto não disponível ou quantidade insuficiente em estoque');

        $this->service->addItemToCart($this->user->id, 99999, 1);
    }

    #[Test]
    public function it_throws_exception_when_adding_more_than_stock()
    {
        $product = Product::factory()->create(['quantity' => 2]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Produto não disponível ou quantidade insuficiente em estoque');

        $this->service->addItemToCart($this->user->id, $product->id, 10);
    }

    #[Test]
    public function it_updates_cart_item_quantity()
    {
        $product = Product::factory()->create(['quantity' => 20]);
        $this->service->addItemToCart($this->user->id, $product->id, 2);

        $cart = $this->service->getOrCreateCart($this->user->id);
        $item = CartItem::where('cart_id', $cart->id)->first();

        $this->service->updateCartItem($this->user->id, $item->id, 5);

        $this->assertEquals(5, $item->fresh()->quantity);
    }

    #[Test]
    public function it_throws_exception_when_updating_nonexistent_item()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Item não encontrado no carrinho');

        $this->service->updateCartItem($this->user->id, 99999, 1);
    }

    #[Test]
    public function it_throws_exception_when_updating_with_insufficient_stock()
    {
        $product = Product::factory()->create(['quantity' => 3]);
        $this->service->addItemToCart($this->user->id, $product->id, 1);

        $cart = $this->service->getOrCreateCart($this->user->id);
        $item = CartItem::where('cart_id', $cart->id)->first();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Quantidade insuficiente em estoque');

        $this->service->updateCartItem($this->user->id, $item->id, 10);
    }

    #[Test]
    public function it_removes_item_from_cart()
    {
        $product = Product::factory()->create(['quantity' => 10]);
        $this->service->addItemToCart($this->user->id, $product->id, 2);

        $cart = $this->service->getOrCreateCart($this->user->id);
        $item = CartItem::where('cart_id', $cart->id)->first();

        $result = $this->service->removeItemFromCart($this->user->id, $item->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('cart_items', ['id' => $item->id]);
    }

    #[Test]
    public function it_throws_exception_when_removing_nonexistent_item()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Item não encontrado no carrinho');

        $this->service->removeItemFromCart($this->user->id, 99999);
    }

    #[Test]
    public function it_returns_zero_total_for_empty_cart()
    {
        $total = $this->service->getCartTotal($this->user->id);

        $this->assertEquals(0.0, $total);
    }

    #[Test]
    public function it_gets_cart_items_with_products()
    {
        $product = Product::factory()->create(['quantity' => 10]);
        $this->service->addItemToCart($this->user->id, $product->id, 3);

        $cart = $this->service->getCartItems($this->user->id);

        $this->assertNotNull($cart->items);
        $this->assertCount(1, $cart->items);
        $this->assertEquals($product->id, $cart->items->first()->product->id);
    }
}
