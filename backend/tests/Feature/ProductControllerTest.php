<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['is_admin' => false]);
        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    #[Test]
    public function test_can_list_products(): void
    {
        Product::factory(10)->create();

        $response = $this->getJson('/api/v1/products?per_page=100');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        // Check that data is an array in the response
        $this->assertIsArray($response->json('data'));
    }

    #[Test]
    public function test_can_get_product_by_id(): void
    {
        $product = Product::factory()->create();

        $response = $this->getJson("/api/v1/products/{$product->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
            ],
        ]);
    }

    #[Test]
    public function it_cannot_create_product_without_auth(): void
    {
        $response = $this->postJson('/api/v1/products', [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 100.00,
            'quantity' => 10,
            'category_id' => 1,
        ]);

        $response->assertStatus(401);
    }

    #[Test]
    public function it_cannot_create_product_as_user(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/products', [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 100.00,
            'quantity' => 10,
            'category_id' => 1,
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function it_admin_can_create_product(): void
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->admin)->postJson('/api/v1/products', [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 100.00,
            'quantity' => 10,
            'category_id' => $category->id,
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'data' => [
                'name' => 'Test Product',
                'price' => 100.00,
            ],
        ]);
    }

    #[Test]
    public function it_requires_valid_fields(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/api/v1/products', [
            'name' => '',
            'price' => -10,
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure([
            'errors',
        ]);
    }

    #[Test]
    public function it_admin_can_delete_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->actingAs($this->admin)->deleteJson("/api/v1/products/{$product->id}");

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    #[Test]
    public function it_admin_can_update_product(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($this->admin)->putJson("/api/v1/products/{$product->id}", [
            'name' => 'Updated Product',
            'description' => 'Updated Description',
            'price' => 200.00,
            'quantity' => 20,
            'category_id' => $category->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    #[Test]
    public function it_returns_404_for_nonexistent_product(): void
    {
        $response = $this->getJson('/api/v1/products/99999');

        $response->assertStatus(404);
        $response->assertJson(['success' => false]);
    }
}
