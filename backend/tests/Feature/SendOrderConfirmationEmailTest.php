<?php

namespace Tests\Feature;

use App\Jobs\SendOrderConfirmationEmail;
use App\Mail\OrderConfirmation;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SendOrderConfirmationEmailTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'customer@example.com',
            'name' => 'João Silva',
        ]);

        $this->product = Product::factory()->create(['quantity' => 100]);
    }

    #[Test]
    public function job_sends_confirmation_email_successfully(): void
    {
        Mail::fake();

        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'total' => 299.99,
            'status' => 'pending',
        ]);

        $order->items()->create([
            'product_id' => $this->product->id,
            'quantity' => 2,
            'unit_price' => 149.99,
            'total_price' => 299.98,
        ]);

        (new SendOrderConfirmationEmail($order))->handle();

        Mail::assertSent(OrderConfirmation::class);
    }

    #[Test]
    public function email_contains_order_details(): void
    {
        Mail::fake();

        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'total' => 199.99,
        ]);

        $order->items()->create([
            'product_id' => $this->product->id,
            'quantity' => 1,
            'unit_price' => 199.99,
            'total_price' => 199.99,
        ]);

        (new SendOrderConfirmationEmail($order))->handle();

        Mail::assertSent(OrderConfirmation::class);
    }

    #[Test]
    public function job_processes_successfully(): void
    {
        Mail::fake();

        $order = Order::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $order->items()->create([
            'product_id' => $this->product->id,
            'quantity' => 1,
            'unit_price' => 99.99,
            'total_price' => 99.99,
        ]);

        (new SendOrderConfirmationEmail($order))->handle();

        Mail::assertSent(OrderConfirmation::class);
    }

    #[Test]
    public function multiple_emails_sent_for_multiple_orders(): void
    {
        Mail::fake();

        $user2 = User::factory()->create(['email' => 'customer2@example.com']);

        $order1 = Order::factory()->create(['user_id' => $this->user->id]);
        $order1->items()->create([
            'product_id' => $this->product->id,
            'quantity' => 1,
            'unit_price' => 99.99,
            'total_price' => 99.99,
        ]);

        $order2 = Order::factory()->create(['user_id' => $user2->id]);
        $order2->items()->create([
            'product_id' => $this->product->id,
            'quantity' => 1,
            'unit_price' => 149.99,
            'total_price' => 149.99,
        ]);

        (new SendOrderConfirmationEmail($order1))->handle();
        (new SendOrderConfirmationEmail($order2))->handle();

        Mail::assertSentCount(2);
    }

    #[Test]
    public function email_sent_to_correct_address(): void
    {
        Mail::fake();

        $customUser = User::factory()->create(['email' => 'custom@example.com']);
        $order = Order::factory()->create(['user_id' => $customUser->id]);

        $order->items()->create([
            'product_id' => $this->product->id,
            'quantity' => 1,
            'unit_price' => 99.99,
            'total_price' => 99.99,
        ]);

        (new SendOrderConfirmationEmail($order))->handle();

        Mail::assertSent(OrderConfirmation::class, function ($mail) use ($customUser) {
            return $mail->hasTo($customUser->email);
        });
    }

    #[Test]
    public function job_throws_exception_when_order_has_no_user(): void
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
        ]);

        // Forçar user como null no objeto
        $order->setRelation('user', null);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Usuário ou email não encontrado para o pedido');

        (new SendOrderConfirmationEmail($order))->handle();
    }

    #[Test]
    public function job_throws_exception_when_user_has_no_email(): void
    {
        $userWithoutEmail = User::factory()->make(['email' => null]);
        $userWithoutEmail->id = $this->user->id;

        $order = Order::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $order->setRelation('user', $userWithoutEmail);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Usuário ou email não encontrado para o pedido');

        (new SendOrderConfirmationEmail($order))->handle();
    }

    #[Test]
    public function job_logs_error_and_rethrows_on_mail_failure(): void
    {
        Mail::shouldReceive('to->send')->andThrow(new \Exception('Mail server error'));

        $order = Order::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Mail server error');

        (new SendOrderConfirmationEmail($order))->handle();
    }
}
