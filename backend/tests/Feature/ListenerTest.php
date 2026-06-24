<?php

namespace Tests\Feature;

use App\Events\OrderCreated;
use App\Events\ProductCreated;
use App\Events\StockLow;
use App\Jobs\ProcessOrder;
use App\Jobs\SendOrderConfirmationEmail;
use App\Jobs\UpdateStockAfterOrder;
use App\Listeners\LogProductCreation;
use App\Listeners\NotifyAdminLowStock;
use App\Listeners\SendOrderNotification;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ListenerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function send_order_notification_listener_dispatches_update_stock_job()
    {
        Queue::fake();

        $user = User::factory()->create();
        $order = Order::create([
            'user_id' => $user->id,
            'subtotal' => 99.99,
            'tax' => 0,
            'shipping_cost' => 0,
            'total' => 99.99,
            'status' => 'pending',
            'shipping_address' => '123 Main St',
            'billing_address' => '123 Main St',
        ]);

        $listener = new SendOrderNotification;
        $listener->handle(new OrderCreated($order));

        Queue::assertPushed(UpdateStockAfterOrder::class);
    }

    #[Test]
    public function send_order_notification_dispatches_process_order_job()
    {
        Queue::fake();

        $user = User::factory()->create();
        $order = Order::create([
            'user_id' => $user->id,
            'subtotal' => 99.99,
            'tax' => 0,
            'shipping_cost' => 0,
            'total' => 99.99,
            'status' => 'pending',
            'shipping_address' => '123 Main St',
            'billing_address' => '123 Main St',
        ]);

        $listener = new SendOrderNotification;
        $listener->handle(new OrderCreated($order));

        Queue::assertPushed(ProcessOrder::class);
    }

    #[Test]
    public function send_order_notification_dispatches_email_job()
    {
        Queue::fake();

        $user = User::factory()->create();
        $order = Order::create([
            'user_id' => $user->id,
            'subtotal' => 99.99,
            'tax' => 0,
            'shipping_cost' => 0,
            'total' => 99.99,
            'status' => 'pending',
            'shipping_address' => '123 Main St',
            'billing_address' => '123 Main St',
        ]);

        $listener = new SendOrderNotification;
        $listener->handle(new OrderCreated($order));

        Queue::assertPushed(SendOrderConfirmationEmail::class);
    }

    #[Test]
    public function log_product_creation_listener_handles_event()
    {
        $product = Product::factory()->create();

        $listener = new LogProductCreation;
        $listener->handle(new ProductCreated($product));

        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    #[Test]
    public function notify_admin_low_stock_listener_handles_event()
    {
        $product = Product::factory()->create([
            'quantity' => 5,
            'min_quantity' => 10,
        ]);

        $listener = new NotifyAdminLowStock;
        $listener->handle(new StockLow($product));

        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }
}
