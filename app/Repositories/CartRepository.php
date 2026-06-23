<?php

namespace App\Repositories;

use App\Models\Cart;

class CartRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new Cart;
    }

    public function getByUser(int $userId)
    {
        return $this->model->where('user_id', $userId)->first();
    }

    public function getOrCreateByUser(int $userId)
    {
        return $this->model->firstOrCreate(['user_id' => $userId]);
    }

    public function findWithItems(int $id)
    {
        return $this->model->with('items.product')->find($id);
    }

    public function clearCart(int $id)
    {
        $cart = $this->find($id);
        if ($cart) {
            $cart->items()->delete();
        }

        return $cart;
    }
}
