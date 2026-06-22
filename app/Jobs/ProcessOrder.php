<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessOrder implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Order $order
    ) {}

    public function handle(): void
    {
        Log::info('Processando pedido em background', [
            'order_id' => $this->order->id,
            'user_id' => $this->order->user_id,
            'status' => $this->order->status,
        ]);

        try {

            /* Todo: Implementar a lógica de processamento do pedido 
            1. Validar informações de pagamento
            2. Mockar integração com gateway de pagamento
            3. Confirmar estoque
            4. Preparar envio
            5. Gerar nota fiscal
            */

            Log::info('Pedido processado com sucesso', [
                'order_id' => $this->order->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao processar pedido', [
                'order_id' => $this->order->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
