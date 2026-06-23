<?php

namespace App\Listeners;

use App\Events\ProductCreated;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LogProductCreation
{
    public function handle(ProductCreated $event): void
    {
        Log::info('Produto criado', [
            'product_id' => $event->product->id,
            'product_name' => $event->product->name,
            'product_price' => $event->product->price,
        ]);

        // Invalidar cache de produtos e categorias
        Cache::tags(['products', 'category_products'])->flush();
    }
}
