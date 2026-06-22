<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 50 products
        \App\Models\Product::factory(50)
            ->create()
            ->each(function (\App\Models\Product $product) {
                // Attach 1-5 random tags to each product
                $tagCount = rand(1, 5);
                $tags = \App\Models\Tag::inRandomOrder()->limit($tagCount)->pluck('id');
                $product->tags()->attach($tags);
            });
    }
}
