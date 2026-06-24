<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Repositories\CartRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RepositoryTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function cart_repository_creates_cart()
    {
        $user = User::factory()->create();
        $repository = new CartRepository;

        $cart = $repository->create(['user_id' => $user->id]);

        $this->assertDatabaseHas('carts', [
            'user_id' => $user->id,
            'id' => $cart->id,
        ]);
    }

    #[Test]
    public function cart_repository_finds_cart_by_user()
    {
        $user = User::factory()->create();
        Cart::create(['user_id' => $user->id]);

        $repository = new CartRepository;
        $cart = $repository->getByUser($user->id);

        $this->assertNotNull($cart);
        $this->assertEquals($user->id, $cart->user_id);
    }

    #[Test]
    public function cart_repository_gets_or_creates_cart()
    {
        $user = User::factory()->create();

        $repository = new CartRepository;
        $cart = $repository->getOrCreateByUser($user->id);

        $this->assertNotNull($cart);
        $this->assertEquals($user->id, $cart->user_id);
        $this->assertDatabaseHas('carts', ['user_id' => $user->id]);
    }

    #[Test]
    public function cart_repository_finds_cart_with_items()
    {
        $user = User::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $user->id]);
        $product = Product::factory()->create();

        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $repository = new CartRepository;
        $found = $repository->findWithItems($cart->id);

        $this->assertNotNull($found);
        $this->assertCount(1, $found->items);
    }

    #[Test]
    public function cart_repository_clears_cart()
    {
        $user = User::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $user->id]);
        $product = Product::factory()->create();

        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $repository = new CartRepository;
        $repository->clearCart($cart->id);

        $this->assertCount(0, CartItem::where('cart_id', '=', $cart->id, 'and')->get());
    }

    #[Test]
    public function category_repository_gets_all_categories()
    {
        Category::factory()->count(3)->create();

        $repository = new CategoryRepository;
        $categories = $repository->all();

        $this->assertCount(3, $categories);
    }

    #[Test]
    public function category_repository_creates_category()
    {
        $data = [
            'name' => 'Electronics',
            'slug' => 'electronics',
            'description' => 'Electronic products',
        ];

        $repository = new CategoryRepository;
        $category = $repository->create($data);

        $this->assertDatabaseHas('categories', [
            'name' => 'Electronics',
            'slug' => 'electronics',
        ]);
    }

    #[Test]
    public function category_repository_finds_by_id()
    {
        $category = Category::factory()->create();

        $repository = new CategoryRepository;
        $found = $repository->find($category->id);

        $this->assertEquals($category->id, $found->id);
    }

    #[Test]
    public function product_repository_gets_active_products()
    {
        Product::factory()->create(['active' => true]);
        Product::factory()->create(['active' => true]);
        Product::factory()->create(['active' => false]);

        $repository = new ProductRepository;
        $products = $repository->getActive();

        $this->assertGreaterThan(0, $products->count());
        $products->each(fn ($p) => $this->assertTrue((bool) $p->active));
    }

    #[Test]
    public function product_repository_gets_by_category()
    {
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();

        Product::factory()->create(['category_id' => $category1->id]);
        Product::factory()->create(['category_id' => $category1->id]);
        Product::factory()->create(['category_id' => $category2->id]);

        $repository = new ProductRepository;
        $products = $repository->getByCategory($category1->id);

        $this->assertGreaterThan(0, $products->count());
    }

    #[Test]
    public function product_repository_gets_low_stock()
    {
        Product::factory()->create(['quantity' => 5, 'min_quantity' => 10]);
        Product::factory()->create(['quantity' => 50, 'min_quantity' => 10]);

        $repository = new ProductRepository;
        $lowStock = $repository->getLowStock();

        $this->assertGreaterThan(0, $lowStock->count());
    }

    #[Test]
    public function product_repository_filters_with_filter_array()
    {
        $category = Category::factory()->create();

        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Test Product',
            'price' => 100.00,
            'active' => true,
        ]);

        $repository = new ProductRepository;
        $filtered = $repository->getWithFilters([
            'category_id' => $category->id,
            'search' => 'Test',
            'min_price' => 50,
            'max_price' => 150,
        ]);

        $this->assertGreaterThan(0, $filtered->count());
    }

    #[Test]
    public function order_repository_gets_by_user()
    {
        $user = User::factory()->create();
        Order::factory()->create(['user_id' => $user->id]);
        Order::factory()->create();

        $repository = new OrderRepository;
        $orders = $repository->getByUser($user->id);

        $this->assertGreaterThan(0, $orders->count());
        $orders->each(fn ($o) => $this->assertEquals($user->id, $o->user_id));
    }

    #[Test]
    public function order_repository_gets_by_status()
    {
        Order::factory()->create(['status' => 'pending']);
        Order::factory()->create(['status' => 'processing']);
        Order::factory()->create(['status' => 'pending']);

        $repository = new OrderRepository;
        $orders = $repository->getByStatus('pending');

        $this->assertGreaterThan(0, $orders->count());
        $orders->each(fn ($o) => $this->assertEquals('pending', $o->status));
    }

    #[Test]
    public function order_repository_finds_with_items()
    {
        $order = Order::factory()->create();
        $product = Product::factory()->create();

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => $product->price,
            'total_price' => $product->price,
        ]);

        $repository = new OrderRepository;
        $found = $repository->findWithItems($order->id);

        $this->assertNotNull($found);
        $this->assertEquals($order->id, $found->id);
        $this->assertCount(1, $found->items);
    }

    #[Test]
    public function order_repository_updates_status()
    {
        $order = Order::factory()->create(['status' => 'pending']);

        $repository = new OrderRepository;
        $repository->updateStatus($order->id, 'processing');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'processing',
        ]);
    }

    #[Test]
    public function order_repository_gets_pending_orders()
    {
        Order::factory()->create(['status' => 'pending']);
        Order::factory()->create(['status' => 'pending']);
        Order::factory()->create(['status' => 'processing']);

        $repository = new OrderRepository;
        $pending = $repository->getPending();

        $this->assertGreaterThan(0, $pending->count());
        $pending->each(fn ($o) => $this->assertEquals('pending', $o->status));
    }
}
