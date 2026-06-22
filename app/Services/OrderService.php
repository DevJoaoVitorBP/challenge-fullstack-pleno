<?php

namespace App\Services;

use App\DTOs\OrderDTO;
use App\Events\OrderCreated;
use App\Events\StockLow;
use App\Jobs\ProcessOrder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\StockMovement;
use App\Repositories\CartRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use Illuminate\Support\Facades\DB;

class OrderService
{
    protected OrderRepository $orderRepository;
    protected ProductRepository $productRepository;
    protected CartRepository $cartRepository;
    protected CartService $cartService;

    public function __construct()
    {
        $this->orderRepository = new OrderRepository();
        $this->productRepository = new ProductRepository();
        $this->cartRepository = new CartRepository();
        $this->cartService = new CartService();
    }

    public function getUserOrders(int $userId)
    {
        return $this->orderRepository->getByUser($userId);
    }

    public function getOrderById(int $id)
    {
        return $this->orderRepository->findWithItems($id);
    }

    public function createOrderFromCart(int $userId, array $shippingAddress, array $billingAddress, ?string $notes = null): ?OrderDTO
    {
        return DB::transaction(function () use ($userId, $shippingAddress, $billingAddress, $notes) {
            $cart = $this->cartService->getCartItems($userId);

            if ($cart->items->isEmpty()) {
                throw new \Exception('Carrinho vazio');
            }

            // Calcular totais
            $subtotal = 0;
            $items = [];

            foreach ($cart->items as $cartItem) {
                $product = $cartItem->product;
                $itemTotal = $product->price * $cartItem->quantity;
                $subtotal += $itemTotal;

                $items[] = [
                    'product_id' => $product->id,
                    'quantity' => $cartItem->quantity,
                    'unit_price' => $product->price,
                    'total_price' => $itemTotal,
                    'product' => $product,
                ];

                // Verificar estoque
                if (!$this->productRepository->find($product->id) || 
                    $product->quantity < $cartItem->quantity) {
                    throw new \Exception("Produto {$product->name} fora de estoque");
                }
            }

            $tax = $subtotal * 0.1; // 10% de imposto
            $shippingCost = 25.00;
            $total = $subtotal + $tax + $shippingCost;

            // Criar pedido
            $order = $this->orderRepository->create([
                'user_id' => $userId,
                'status' => 'pending',
                'subtotal' => $subtotal,
                'tax' => $tax,
                'shipping_cost' => $shippingCost,
                'total' => $total,
                'shipping_address' => $shippingAddress,
                'billing_address' => $billingAddress,
                'notes' => $notes,
            ]);

            // Criar items do pedido e atualizar estoque
            foreach ($items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['total_price'],
                ]);

                // Atualizar quantidade em estoque
                $product = $item['product'];
                $product->decrement('quantity', $item['quantity']);

                // Registrar movimento de estoque
                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => 'venda',
                    'quantity' => -$item['quantity'],
                    'reason' => 'Venda',
                    'reference_type' => 'order',
                    'reference_id' => $order->id,
                ]);

                // Verificar se estoque ficou baixo
                if ($product->quantity <= $product->min_quantity) {
                    StockLow::dispatch($product);
                }
            }

            // Limpar carrinho
            $this->cartService->clearCart($userId);

            // Disparar evento de pedido criado
            OrderCreated::dispatch($order);

            // Enviar para fila para processar pedido em background
            ProcessOrder::dispatch($order);

            return OrderDTO::fromArray($order->toArray());
        });
    }

    public function updateOrderStatus(int $id, string $status): ?OrderDTO
    {
        $order = $this->orderRepository->updateStatus($id, $status);
        return $order ? OrderDTO::fromArray($order->toArray()) : null;
    }

    public function getPendingOrders()
    {
        return $this->orderRepository->getPending();
    }

    public function getOrdersByStatus(string $status)
    {
        return $this->orderRepository->getByStatus($status);
    }
}
