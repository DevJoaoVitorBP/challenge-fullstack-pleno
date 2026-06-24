<?php

namespace Tests\Feature;

use App\Events\StockLow;
use App\Jobs\UpdateStockAfterOrder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateStockAfterOrderJobTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Product $product;

    protected Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->product = Product::factory()->create([
            'quantity' => 100,
            'min_quantity' => 10,
            'active' => true,
        ]);

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
            'quantity' => 10,
            'unit_price' => 49.99,
            'total_price' => 499.90,
        ]);
    }

    #[Test]
    public function it_updates_stock_successfully()
    {
        $initialQuantity = $this->product->quantity;

        $job = new UpdateStockAfterOrder($this->order);
        $job->handle();

        $this->product->refresh();
        $this->assertEquals($initialQuantity - 10, $this->product->quantity);
    }

    #[Test]
    public function it_creates_stock_movement()
    {
        $job = new UpdateStockAfterOrder($this->order);
        $job->handle();

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'type' => 'saida',
            'quantity' => 10,
            'reason' => 'Venda',
            'reference_id' => $this->order->id,
        ]);
    }

    #[Test]
    public function it_fails_with_insufficient_stock()
    {
        $this->product->update(['quantity' => 5]);

        $this->expectException(\Exception::class);

        $job = new UpdateStockAfterOrder($this->order);
        $job->handle();
    }

    #[Test]
    public function it_handles_multiple_items()
    {
        $product2 = Product::factory()->create(['quantity' => 50, 'active' => true]);

        OrderItem::create([
            'order_id' => $this->order->id,
            'product_id' => $product2->id,
            'quantity' => 5,
            'unit_price' => 29.99,
            'total_price' => 149.95,
        ]);

        $job = new UpdateStockAfterOrder($this->order);
        $job->handle();

        $this->product->refresh();
        $product2->refresh();

        $this->assertEquals(90, $this->product->quantity);
        $this->assertEquals(45, $product2->quantity);
        $this->assertCount(2, StockMovement::all());
    }

    #[Test]
    public function it_dispatches_stock_low_event()
    {
        Event::fake();

        $this->product->update(['quantity' => 15, 'min_quantity' => 10]);

        OrderItem::where('order_id', $this->order->id)->delete();

        OrderItem::create([
            'order_id' => $this->order->id,
            'product_id' => $this->product->id,
            'quantity' => 10,
            'unit_price' => 49.99,
            'total_price' => 499.90,
        ]);

        $job = new UpdateStockAfterOrder($this->order);
        $job->handle();

        Event::assertDispatched(StockLow::class);
    }
}
