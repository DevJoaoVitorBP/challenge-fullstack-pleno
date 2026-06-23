<?php

namespace Tests\Unit;

use App\DTOs\ProductDTO;
use App\Models\Category;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ProductService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ProductService;
    }

    public function test_can_create_product(): void
    {
        $category = Category::factory()->create();

        $dto = new ProductDTO(
            name: 'Test Product',
            slug: 'test-product',
            description: 'Test Description',
            price: 100.00,
            cost_price: 50.00,
            quantity: 10,
            min_quantity: 5,
            active: true,
            category_id: $category->id,
        );

        $product = $this->service->createProduct($dto);

        $this->assertNotNull($product);
        $this->assertEquals('Test Product', $product->name);
    }

    public function test_can_check_stock_availability(): void
    {
        $product = Product::factory()->create(['quantity' => 10]);

        $available = $this->service->checkStockAvailability($product->id, 5);
        $this->assertTrue($available);

        $available = $this->service->checkStockAvailability($product->id, 15);
        $this->assertFalse($available);
    }

    public function test_can_get_low_stock_products(): void
    {
        Product::factory()->create(['quantity' => 1, 'min_quantity' => 10]);
        Product::factory()->create(['quantity' => 15, 'min_quantity' => 10]);

        $lowStock = $this->service->getLowStockProducts();

        $this->assertGreaterThanOrEqual(1, count($lowStock->items()));
    }
}
