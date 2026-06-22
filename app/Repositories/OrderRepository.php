<?php

namespace App\Repositories;

use App\Models\Order;

class OrderRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new Order();
    }

    public function getByUser(int $userId)
    {
        return $this->model->where('user_id', $userId)->latest()->paginate(15);
    }

    public function getByStatus(string $status)
    {
        return $this->model->where('status', $status)->latest()->paginate(15);
    }

    public function getPending()
    {
        return $this->model->where('status', 'pending')->latest()->paginate(15);
    }

    public function findWithItems(int $id)
    {
        return $this->model->with('items.product')->find($id);
    }

    public function updateStatus(int $id, string $status)
    {
        return $this->update($id, ['status' => $status]);
    }
}
