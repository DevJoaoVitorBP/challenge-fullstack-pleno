<?php

namespace App\Listeners;

use App\Events\StockLow;
use Illuminate\Support\Facades\Log;

class NotifyAdminLowStock
{
    public function handle(StockLow $event): void
    {
        Log::warning('Estoque baixo', [
            'product_id' => $event->product->id,
            'product_name' => $event->product->name,
            'current_quantity' => $event->product->quantity,
            'min_quantity' => $event->product->min_quantity,
        ]);

        // Aqui vou enviar uma notificação para o admin via email, SMS, etc.
        /* Todo: Implementar a lógica de notificação para o admin
        1. Preparar o conteúdo da notificação
        2. Enviar a notificação para o admin
        */
    }
}
