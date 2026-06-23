<?php

namespace App\Jobs;

use App\Mail\OrderConfirmation;
use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOrderConfirmationEmail implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Order $order
    ) {}

    public function handle(): void
    {
        try {
            if (! $this->order->user || ! $this->order->user->email) {
                throw new \Exception('Usuário ou email não encontrado para o pedido');
            }
   
            Log::info('Iniciando envio de email de confirmação do pedido', [
                'order_id' => $this->order->id,
                'user_id' => $this->order->user_id,
                'user_email' => $this->order->user->email,
            ]);

            Mail::to($this->order->user->email)->send(new OrderConfirmation($this->order));

            Log::info('Email de confirmação enviado com sucesso', [
                'order_id' => $this->order->id,
                'recipient' => $this->order->user->email,
                'sent_at' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao enviar email de confirmação do pedido', [
                'order_id' => $this->order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
}
