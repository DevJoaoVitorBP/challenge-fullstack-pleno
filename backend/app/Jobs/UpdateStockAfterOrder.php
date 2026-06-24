<?php

namespace App\Jobs;

use App\Events\StockLow;
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
        $this->order->load('items.product');

        Log::info('Iniciando atualização de estoque para o pedido', [
            'order_id' => $this->order->id,
            'items_count' => $this->order->items->count(),
        ]);

        try {
            DB::beginTransaction();

            foreach ($this->order->items as $item) {
                $product = $item->product;

                if ($product->quantity < $item->quantity) {
                    throw new \Exception(
                        "Estoque insuficiente para o produto: {$product->name}. "
                        ."Disponível: {$product->quantity}, Solicitado: {$item->quantity}"
                    );
                }

                $product->decrement('quantity', $item->quantity);

                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => 'saida',
                    'quantity' => $item->quantity,
                    'reason' => 'Venda',
                    'reference_type' => Order::class,
                    'reference_id' => $this->order->id,
                ]);

                $product->refresh(); // garante o valor atualizado antes de checar min_quantity

                if ($product->quantity < $product->min_quantity) {
                    Log::warning('Estoque do produto abaixo do mínimo', [
                        'product_id' => $product->id,
                        'current_quantity' => $product->quantity,
                        'min_quantity' => $product->min_quantity,
                    ]);

                    event(new StockLow($product));
                }

                Log::info('Estoque atualizado com sucesso', [
                    'product_id' => $product->id,
                    'quantity_sold' => $item->quantity,
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
