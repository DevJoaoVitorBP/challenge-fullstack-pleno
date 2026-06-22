<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\StockMovement;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateStockAfterOrder implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Order $order
    ) {}

    public function handle(): void
    {
        Log::info('Iniciando atualização de estoque para o pedido', [
            'order_id' => $this->order->id,
            'items_count' => $this->order->orderItems->count(),
        ]);

        try {
            DB::beginTransaction();

            foreach ($this->order->orderItems as $orderItem) {
                $product = $orderItem->product;

                // Validar estoque
                if ($product->quantity < $orderItem->quantity) {
                    throw new \Exception(
                        "Estoque insuficiente para o produto: {$product->name}. "
                        . "Disponível: {$product->quantity}, Solicitado: {$orderItem->quantity}"
                    );
                }

                // Decrementar estoque do produto
                $product->decrement('quantity', $orderItem->quantity);

                // Registrar movimento de estoque
                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => 'saida',
                    'quantity' => $orderItem->quantity,
                    'reason' => 'Venda',
                    'reference_type' => Order::class,
                    'reference_id' => $this->order->id,
                ]);

                // Verificar se o estoque ficou abaixo do mínimo
                if ($product->quantity < $product->min_quantity) {
                    Log::warning('Estoque do produto abaixo do mínimo', [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'current_quantity' => $product->quantity,
                        'min_quantity' => $product->min_quantity,
                    ]);

                    // Disparar evento de estoque baixo
                    event(new \App\Events\StockLow($product));
                }

                Log::info('Estoque atualizado com sucesso', [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity_sold' => $orderItem->quantity,
                    'remaining_quantity' => $product->quantity,
                ]);
            }

            DB::commit();

            Log::info('Estoque atualizado com sucesso para o pedido', [
                'order_id' => $this->order->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erro ao atualizar estoque do pedido', [
                'order_id' => $this->order->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
