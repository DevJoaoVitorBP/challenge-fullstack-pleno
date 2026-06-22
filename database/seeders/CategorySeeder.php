<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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
            $slug = \Illuminate\Support\Str::slug($category['name']);
            \App\Models\Category::create([
                'name' => $category['name'],
                'slug' => $slug,
                'description' => $category['description'],
                'parent_id' => null,
                'active' => true,
            ]);
        }
    }
}
