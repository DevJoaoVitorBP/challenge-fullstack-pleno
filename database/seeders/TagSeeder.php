<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = ['gaming', 'console', 'laptop', 'desktop', 'ofertas', 'novo', 'em-estoque', 'promocao'];

        foreach ($tags as $tag) {
            \App\Models\Tag::create([
                'name' => $tag,
                'slug' => \Illuminate\Support\Str::slug($tag),
            ]);
        }
    }
}
