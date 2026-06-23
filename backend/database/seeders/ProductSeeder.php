<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 50 products
        Product::factory(50)
            ->create()
            ->each(function (Product $product) {
                // Attach 1-5 random tags to each product
                $tagCount = rand(1, 5);
                $tags = Tag::inRandomOrder()->limit($tagCount)->pluck('id');
                $product->tags()->attach($tags);
            });
    }
}
