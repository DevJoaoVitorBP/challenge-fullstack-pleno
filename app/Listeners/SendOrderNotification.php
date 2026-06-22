<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Jobs\SendOrderConfirmationEmail;
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

        // Enviar para fila para enviar email em background
        SendOrderConfirmationEmail::dispatch($event->order);
    }
}
