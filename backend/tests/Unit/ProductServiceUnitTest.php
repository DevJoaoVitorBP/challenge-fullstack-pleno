<?php

namespace Tests\Unit;

use App\DTOs\ProductDTO;
use App\Models\Category;
use App\Models\Product;
use App\Models\Tag;
use App\Services\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductServiceUnitTest extends TestCase
{
    use RefreshDatabase;

    protected ProductService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ProductService;
    }

    #[Test]
    public function it_creates_product()
    {
        $category = Category::factory()->create();

        $data = new ProductDTO(
            name: 'Test Product',
            slug: 'test-product',
            description: 'Test Description',
            price: 99.99,
            cost_price: 50.00,
            quantity: 10,
            min_quantity: 2,
            category_id: $category->id,
        );

        $product = $this->service->createProduct($data);

        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'slug' => 'test-product',
            'price' => 99.99,
        ]);
    }

    #[Test]
    public function it_gets_in_stock_products()
    {
        Product::factory()->create(['quantity' => 10, 'active' => true]);
        Product::factory()->create(['quantity' => 0, 'active' => true]);

        $products = $this->service->getAllProducts();

        $inStock = $products->filter(fn ($p) => $p->quantity > 0);
        $this->assertGreaterThan(0, $inStock->count());
    }

    #[Test]
    public function it_gets_low_stock_products()
    {
        Product::factory()->create(['quantity' => 5, 'min_quantity' => 10, 'active' => true]);
        Product::factory()->create(['quantity' => 50, 'min_quantity' => 10, 'active' => true]);

        $products = $this->service->getLowStockProducts();

        $lowStock = $products->filter(fn ($p) => $p->quantity < $p->min_quantity);
        $this->assertGreaterThan(0, $lowStock->count());
    }

    #[Test]
    public function it_updates_product()
    {
        $product = Product::factory()->create();

        $data = new ProductDTO(
            name: $product->name,
            slug: $product->slug,
            description: $product->description,
            price: 150.00,
            cost_price: $product->cost_price,
            quantity: 20,
            min_quantity: $product->min_quantity,
            category_id: $product->category_id,
        );

        $this->service->updateProduct($product->id, $data);

        $product->refresh();
        $this->assertEquals(150.00, $product->price);
        $this->assertEquals(20, $product->quantity);
    }

    #[Test]
    public function it_deletes_product()
    {
        $product = Product::factory()->create();

        $result = $this->service->deleteProduct($product->id);

        $this->assertTrue($result);
        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    #[Test]
    public function it_checks_stock_availability_when_sufficient()
    {
        $product = Product::factory()->create(['quantity' => 10]);

        $result = $this->service->checkStockAvailability($product->id, 5);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_checks_stock_availability_when_insufficient()
    {
        $product = Product::factory()->create(['quantity' => 2]);

        $result = $this->service->checkStockAvailability($product->id, 10);

        $this->assertFalse($result);
    }

    #[Test]
    public function it_returns_false_for_nonexistent_product_stock_check()
    {
        $result = $this->service->checkStockAvailability(99999, 1);

        $this->assertFalse($result);
    }

    #[Test]
    public function it_searches_products()
    {
        Product::factory()->create(['name' => 'Unique Searchable Product', 'active' => true]);
        Product::factory()->create(['name' => 'Another Product', 'active' => true]);

        $results = $this->service->searchProducts('Unique Searchable');

        $this->assertGreaterThan(0, $results->count());
    }

    #[Test]
    public function it_creates_product_with_tags()
    {
        $category = Category::factory()->create();
        $tags = Tag::factory(3)->create();

        $data = new ProductDTO(
            name: 'Tagged Product',
            slug: 'tagged-product',
            description: 'Test',
            price: 99.99,
            cost_price: 50.00,
            quantity: 10,
            min_quantity: 2,
            category_id: $category->id,
            tags: $tags->pluck('id')->toArray(),
        );

        $product = $this->service->createProduct($data);

        $this->assertNotNull($product);
    }

    #[Test]
    public function it_updates_product_with_tags()
    {
        $product = Product::factory()->create();
        $tags = Tag::factory(2)->create();

        $data = new ProductDTO(
            name: $product->name,
            slug: $product->slug,
            description: $product->description,
            price: 150.00,
            cost_price: $product->cost_price,
            quantity: 20,
            min_quantity: $product->min_quantity,
            category_id: $product->category_id,
            tags: $tags->pluck('id')->toArray(),
        );

        $result = $this->service->updateProduct($product->id, $data);

        $this->assertNotNull($result);
    }
}
