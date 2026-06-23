<?php

namespace App\Repositories;

use App\Models\Category;

class CategoryRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new Category;
    }

    public function getActive()
    {
        return $this->model->where('active', true)->get();
    }

    public function getHierarchy()
    {
        return $this->model
            ->where('active', true)
            ->whereNull('parent_id')
            ->with('children')
            ->get();
    }

    public function getByParent(?int $parentId = null)
    {
        if ($parentId === null) {
            return $this->model->whereNull('parent_id')->get();
        }

        return $this->model->where('parent_id', $parentId)->get();
    }

    public function findBySlug(string $slug)
    {
        return $this->model->where('slug', $slug)->first();
    }
}
