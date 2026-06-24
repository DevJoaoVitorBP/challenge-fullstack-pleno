<?php

namespace Tests\Feature;

use App\Jobs\ProcessOrder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProcessOrderJobTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Product $product;

    protected Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->product = Product::factory()->create(['quantity' => 100]);

        $this->order = Order::create([
            'user_id' => $this->user->id,
            'subtotal' => 99.99,
            'tax' => 0,
            'shipping_cost' => 0,
            'total' => 99.99,
            'status' => 'pending',
            'shipping_address' => '123 Main St',
            'billing_address' => '123 Main St',
        ]);

        OrderItem::create([
            'order_id' => $this->order->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
            'unit_price' => 49.99,
            'total_price' => 99.98,
        ]);
    }

    #[Test]
    public function it_processes_order_successfully()
    {
        $job = new ProcessOrder($this->order);
        $job->handle();

        $this->order->refresh();
        $this->assertEquals('processing', $this->order->status);
        $this->assertNotNull($this->order->metadata);
        $this->assertArrayHasKey('invoice_number', $this->order->metadata);
        $this->assertArrayHasKey('tracking_number', $this->order->metadata);
        $this->assertArrayHasKey('payment_method', $this->order->metadata);
        $this->assertEquals('credit_card_mock', $this->order->metadata['payment_method']);
    }

    #[Test]
    public function it_fails_when_order_has_no_user(): void
    {
        $order = Order::create([
            'user_id' => $this->user->id,
            'subtotal' => 99.99,
            'tax' => 0,
            'shipping_cost' => 0,
            'total' => 99.99,
            'status' => 'pending',
            'shipping_address' => '123 Main St',
            'billing_address' => '123 Main St',
        ]);

        $mockOrder = $this->partialMock(Order::class, function ($mock) {
            $mock->shouldReceive('getAttribute')
                ->with('user')
                ->andReturn(null);
        });

        $mockOrder->id = $order->id;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Pedido sem usuário associado');

        $job = new ProcessOrder($mockOrder);
        $job->handle();
    }

    #[Test]
    public function it_fails_when_order_has_no_items()
    {
        $order = Order::create([
            'user_id' => $this->user->id,
            'subtotal' => 99.99,
            'tax' => 0,
            'shipping_cost' => 0,
            'total' => 99.99,
            'status' => 'pending',
            'shipping_address' => '123 Main St',
            'billing_address' => '123 Main St',
        ]);

        $this->expectException(\Exception::class);

        $job = new ProcessOrder($order);
        $job->handle();
    }

    #[Test]
    public function it_fails_when_order_total_is_invalid()
    {
        $order = Order::create([
            'user_id' => $this->user->id,
            'subtotal' => 0,
            'tax' => 0,
            'shipping_cost' => 0,
            'total' => 0,
            'status' => 'pending',
            'shipping_address' => '123 Main St',
            'billing_address' => '123 Main St',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
            'unit_price' => 49.99,
            'total_price' => 99.98,
        ]);

        $this->expectException(\Exception::class);

        $job = new ProcessOrder($order);
        $job->handle();
    }

    #[Test]
    public function it_fails_when_payment_is_declined(): void
    {

        $attempts = 0;
        $maxAttempts = 200;
        $declined = false;

        while ($attempts < $maxAttempts) {
            try {
                $order = Order::create([
                    'user_id' => $this->user->id,
                    'subtotal' => 99.99,
                    'tax' => 0,
                    'shipping_cost' => 0,
                    'total' => 99.99,
                    'status' => 'pending',
                    'shipping_address' => '123 Main St',
                    'billing_address' => '123 Main St',
                ]);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $this->product->id,
                    'quantity' => 1,
                    'unit_price' => 99.99,
                    'total_price' => 99.99,
                ]);

                $job = new ProcessOrder($order);
                $job->handle();

                $attempts++;
                $order->forceDelete();
            } catch (\Exception $e) {
                if (str_contains($e->getMessage(), 'Pagamento recusado pelo gateway')) {
                    $declined = true;
                    break;
                }
                throw $e;
            }
        }

        $this->assertTrue($declined, 'Pagamento recusado não foi simulado em '.$maxAttempts.' tentativas');
    }

    #[Test]
    public function it_fails_when_product_not_found_in_stock_confirmation(): void
    {
        $deletedProduct = Product::factory()->create(['quantity' => 100]);

        OrderItem::create([
            'order_id' => $this->order->id,
            'product_id' => $deletedProduct->id,
            'quantity' => 1,
            'unit_price' => 49.99,
            'total_price' => 49.99,
        ]);

        // Deletar o produto para simular produto não encontrado
        $deletedProduct->delete();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Produto não encontrado para item');

        $job = new ProcessOrder($this->order);
        $job->handle();
    }

    #[Test]
    public function it_fails_when_product_has_negative_stock(): void
    {
        $product = Product::factory()->create(['quantity' => -1]);

        OrderItem::create([
            'order_id' => $this->order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 49.99,
            'total_price' => 49.99,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('com estoque negativo');

        $job = new ProcessOrder($this->order);
        $job->handle();
    }
}
