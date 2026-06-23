<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            // Create 3-5 orders per user
            $orders = Order::factory(rand(3, 5))
                ->create(['user_id' => $user->id]);

            foreach ($orders as $order) {
                // Add 1-5 items to each order
                $products = Product::all()->random(rand(1, 5));
                foreach ($products as $product) {
                    $quantity = rand(1, 5);
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'unit_price' => $product->price,
                        'total_price' => $product->price * $quantity,
                    ]);
                }
            }
        }
    }
}
