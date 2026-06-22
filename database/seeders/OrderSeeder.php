<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = \App\Models\User::all();
        
        foreach ($users as $user) {
            // Create 3-5 orders per user
            $orders = \App\Models\Order::factory(rand(3, 5))
                ->create(['user_id' => $user->id]);
            
            foreach ($orders as $order) {
                // Add 1-5 items to each order
                $products = \App\Models\Product::all()->random(rand(1, 5));
                foreach ($products as $product) {
                    $quantity = rand(1, 5);
                    \App\Models\OrderItem::create([
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
