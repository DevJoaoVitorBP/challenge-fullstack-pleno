<?php

namespace App\Repositories;

use App\Models\Product;

class ProductRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new Product();
    }

    public function getActive()
    {
        return $this->model->active()->paginate(15);
    }

    public function getByCategory(int $categoryId)
    {
        return $this->model->where('category_id', $categoryId)->paginate(15);
    }

    public function searchByName(string $query)
    {
        return $this->model
            ->where('name', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->paginate(15);
    }

    public function getLowStock()
    {
        return $this->model->lowStock()->paginate(15);
    }

    public function getWithFilters(array $filters)
    {
        $query = $this->model->active();

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }

        if (!empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        if (!empty($filters['sort'])) {
            $sortBy = $filters['sort'];
            $direction = $filters['sort_direction'] ?? 'asc';
            $query->orderBy($sortBy, $direction);
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }
}
