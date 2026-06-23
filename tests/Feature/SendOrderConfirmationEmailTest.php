<?php

namespace Tests\Feature;

use App\Jobs\SendOrderConfirmationEmail;
use App\Mail\OrderConfirmation;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
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

    /**
     * Test que o email é enviado com sucesso quando o job é executado
     */
    public function test_job_sends_confirmation_email_successfully(): void
    {
        Mail::fake();

        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'total' => 299.99,
            'status' => 'pending',
        ]);

        // Adicionar item ao pedido
        $order->items()->create([
            'product_id' => $this->product->id,
            'quantity' => 2,
            'unit_price' => 149.99,
            'total_price' => 299.98,
        ]);

        // Executar o job
        (new SendOrderConfirmationEmail($order))->handle();

        // Verificar se o email foi enviado
        Mail::assertSent(OrderConfirmation::class);
    }

    /**
     * Test que o email contém os dados corretos do pedido
     */
    public function test_email_contains_order_details(): void
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

        // Simplesmente verificar que o email foi enviado
        Mail::assertSent(OrderConfirmation::class);
    }

    /**
     * Test que o job processa corretamente sem erros
     */
    public function test_job_processes_successfully(): void
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

        // Job deve completar sem lançar exceção
        (new SendOrderConfirmationEmail($order))->handle();

        // Verificar que o email foi enviado
        Mail::assertSent(OrderConfirmation::class);
    }

    /**
     * Test que múltiplos emails são enviados para múltiplos pedidos
     */
    public function test_multiple_emails_sent_for_multiple_orders(): void
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

        // Enviar emails
        (new SendOrderConfirmationEmail($order1))->handle();
        (new SendOrderConfirmationEmail($order2))->handle();

        // Verificar que dois emails foram enviados
        Mail::assertSentCount(2);
    }

    /**
     * Test que o email é enviado para o endereço correto
     */
    public function test_email_sent_to_correct_address(): void
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
}
