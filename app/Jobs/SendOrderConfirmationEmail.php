<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendOrderConfirmationEmail implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Order $order
    ) {}

    public function handle(): void
    {
        Log::info('Enviando email de confirmação do pedido', [
            'order_id' => $this->order->id,
            'user_email' => $this->order->user->email,
        ]);

        /* Todo: Implementar a lógica de envio de email
        1. Preparar o conteúdo do email
        2. Enviar o email para o usuário
        */
    }
}
