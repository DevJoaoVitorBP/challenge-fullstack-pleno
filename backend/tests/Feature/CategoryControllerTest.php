<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\CategoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    #[Test]
    public function can_list_categories(): void
    {
        Category::factory(5)->create();

        $response = $this->getJson('/api/v1/categories');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $this->assertIsArray($response->json('data'));
    }

    #[Test]
    public function can_get_category_by_id(): void
    {
        $category = Category::factory()->create();

        $response = $this->getJson("/api/v1/categories/{$category->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'id' => $category->id,
                'name' => $category->name,
            ],
        ]);
    }

    #[Test]
    public function can_list_products_by_category(): void
    {
        $category = Category::factory()->create();
        Product::factory(5)->create(['category_id' => $category->id]);

        $response = $this->getJson("/api/v1/categories/{$category->id}/products?per_page=100");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }

    #[Test]
    public function admin_can_create_category(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/api/v1/categories', [
            'name' => 'New Category',
            'description' => 'A new category',
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'data' => [
                'name' => 'New Category',
            ],
        ]);
    }

    #[Test]
    public function admin_can_update_category(): void
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->admin)->putJson("/api/v1/categories/{$category->id}", [
            'name' => 'Updated Category',
            'description' => 'Updated description',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }

    #[Test]
    public function admin_can_delete_category(): void
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->admin)->deleteJson("/api/v1/categories/{$category->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }

    #[Test]
    public function get_all_categories_returns_active_categories(): void
    {
        Category::factory(3)->create(['active' => true]);

        $service = new CategoryService;
        $result = $service->getAllCategories();

        $this->assertCount(3, $result);
    }

    #[Test]
    public function get_category_by_slug(): void
    {
        $category = Category::factory()->create(['name' => 'My Category']);

        $service = new CategoryService;
        $result = $service->getCategoryBySlug($category->slug);

        $this->assertNotNull($result);
        $this->assertEquals($category->slug, $result->slug);
    }

    #[Test]
    public function get_child_categories(): void
    {
        $parent = Category::factory()->create();
        Category::factory(3)->create(['parent_id' => $parent->id]);

        $service = new CategoryService;
        $result = $service->getChildCategories($parent->id);

        $this->assertCount(3, $result);
    }
}
