<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Eletrônicos', 'description' => 'Produtos eletrônicos em geral'],
            ['name' => 'Computadores', 'description' => 'Computadores e periféricos'],
            ['name' => 'Móveis', 'description' => 'Móveis para casa e escritório'],
            ['name' => 'Roupas', 'description' => 'Vestuário em geral'],
            ['name' => 'Livros', 'description' => 'Livros e publicações'],
        ];

        foreach ($categories as $category) {
            $slug = Str::slug($category['name']);
            Category::create([
                'name' => $category['name'],
                'slug' => $slug,
                'description' => $category['description'],
                'parent_id' => null,
                'active' => true,
            ]);
        }
    }
}
