<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
            'total' => $this->order->total,
            'status' => $this->order->status,
        ]);

        try {
            DB::beginTransaction();

            // 1. Validar informações de pagamento
            $this->validatePaymentInfo();

            // 2. Mockar integração com gateway de pagamento
            $this->processPaymentWithGateway();

            // 3. Confirmar estoque (já foi feito em UpdateStockAfterOrder, mas validamos aqui)
            $this->confirmStockAvailability();

            // 4. Preparar envio
            $invoiceNumber = $this->generateInvoiceNumber();
            $trackingNumber = $this->generateTrackingNumber();

            // 5. Atualizar status do pedido para processado
            $this->order->update([
                'status' => 'processing',
            ]);

            // Salvar informações adicionais de processamento
            $this->order->update([
                'metadata' => [
                    'invoice_number' => $invoiceNumber,
                    'tracking_number' => $trackingNumber,
                    'processed_at' => Carbon::now()->toIso8601String(),
                    'payment_method' => 'credit_card_mock',
                ],
            ]);

            DB::commit();

            Log::info('Pedido processado com sucesso', [
                'order_id' => $this->order->id,
                'invoice_number' => $invoiceNumber,
                'tracking_number' => $trackingNumber,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erro ao processar pedido', [
                'order_id' => $this->order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Validar informações de pagamento
     */
    private function validatePaymentInfo(): void
    {
        // Validar que o pedido tem usuário
        if (!$this->order->user) {
            throw new \Exception('Pedido sem usuário associado');
        }

        // Validar que o pedido tem itens
        if ($this->order->items()->count() === 0) {
            throw new \Exception('Pedido sem itens');
        }

        // Validar que o total é maior que 0
        if ($this->order->total <= 0) {
            throw new \Exception('Pedido com valor inválido');
        }

        Log::info('Informações de pagamento validadas', [
            'order_id' => $this->order->id,
            'user_id' => $this->order->user_id,
        ]);
    }

    /**
     * Mockar integração com gateway de pagamento
     */
    private function processPaymentWithGateway(): void
    {
        // Simular processamento com gateway de pagamento
        $gatewayTransactionId = 'TXN-' . strtoupper(substr(uniqid(), -12));
        $processingTime = rand(500, 2000); // Simular tempo de processamento

        Log::info('Processando pagamento com gateway simulado', [
            'order_id' => $this->order->id,
            'amount' => $this->order->total,
            'transaction_id' => $gatewayTransactionId,
            'processing_time_ms' => $processingTime,
        ]);

        // Simular sucesso na maioria dos casos (95% de sucesso)
        if (rand(1, 100) > 95) {
            throw new \Exception('Pagamento recusado pelo gateway (simulado)');
        }

        Log::info('Pagamento processado com sucesso', [
            'order_id' => $this->order->id,
            'transaction_id' => $gatewayTransactionId,
            'amount' => $this->order->total,
        ]);
    }

    /**
     * Confirmar disponibilidade de estoque
     */
    private function confirmStockAvailability(): void
    {
        foreach ($this->order->items() as $item) {
            $product = $item->product;

            if (!$product) {
                throw new \Exception("Produto não encontrado para item #{$item->id}");
            }

            // Verificar que o estoque foi decrementado corretamente
            if ($product->quantity < 0) {
                throw new \Exception("Produto {$product->name} com estoque negativo");
            }
        }

        Log::info('Estoque confirmado para o pedido', [
            'order_id' => $this->order->id,
            'items_count' => $this->order->items()->count(),
        ]);
    }

    /**
     * Gerar número de nota fiscal
     */
    private function generateInvoiceNumber(): string
    {
        return 'INV-' . str_pad($this->order->id, 6, '0', STR_PAD_LEFT) . '-' . date('Ym');
    }

    /**
     * Gerar número de rastreamento
     */
    private function generateTrackingNumber(): string
    {
        return 'TRK-' . strtoupper(substr(uniqid(), -8)) . '-' . date('Y');
    }
}
