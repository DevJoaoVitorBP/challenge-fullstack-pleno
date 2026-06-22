<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Jobs\ProcessOrder;
use App\Jobs\SendOrderConfirmationEmail;
use App\Jobs\UpdateStockAfterOrder;
use Illuminate\Support\Facades\Log;

class SendOrderNotification
{
    public function handle(OrderCreated $event): void
    {
        Log::info('Pedido criado', [
            'order_id' => $event->order->id,
            'user_id' => $event->order->user_id,
            'total' => $event->order->total,
        ]);

        // Atualizar estoque do pedido em background
        UpdateStockAfterOrder::dispatch($event->order);

        // Processar pedido em background
        ProcessOrder::dispatch($event->order);

        // Enviar email de confirmação em background
        SendOrderConfirmationEmail::dispatch($event->order);
    }
}
