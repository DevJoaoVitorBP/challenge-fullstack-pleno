<?php

namespace App\Services;

use App\DTOs\CategoryDTO;
use App\Repositories\CategoryRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class CategoryService
{
    protected CategoryRepository $repository;

    protected const CACHE_TTL = 86400; // 24 horas = 86400 segundos

    protected const CACHE_TAG = 'categories';

    protected const PRODUCT_CACHE_TAG = 'category_products';

    public function __construct()
    {
        $this->repository = new CategoryRepository;
    }

    public function getAllCategories()
    {
        $cacheKey = 'categories.all';

        return Cache::tags([self::CACHE_TAG])
            ->remember($cacheKey, self::CACHE_TTL, function () {
                return $this->repository->getActive();
            });
    }

    public function getCategoryHierarchy()
    {
        $cacheKey = 'categories.hierarchy';

        return Cache::tags([self::CACHE_TAG])
            ->remember($cacheKey, self::CACHE_TTL, function () {
                return $this->repository->getHierarchy();
            });
    }

    public function getCategoryById(int $id)
    {
        $cacheKey = "category.{$id}";

        return Cache::tags([self::CACHE_TAG])
            ->remember($cacheKey, self::CACHE_TTL, function () use ($id) {
                return $this->repository->find($id);
            });
    }

    public function getCategoryBySlug(string $slug)
    {
        $cacheKey = "category.slug.{$slug}";

        return Cache::tags([self::CACHE_TAG])
            ->remember($cacheKey, self::CACHE_TTL, function () use ($slug) {
                return $this->repository->findBySlug($slug);
            });
    }

    public function createCategory(CategoryDTO $dto): ?CategoryDTO
    {
        $data = $dto->toArray();
        $data['slug'] = Str::slug($data['name']);

        $category = $this->repository->create($data);

        // Invalidar cache de categorias (listing, hierarchy) com tags inteligentes
        Cache::tags([self::CACHE_TAG, self::PRODUCT_CACHE_TAG])->flush();

        return $category ? CategoryDTO::fromArray($category->toArray()) : null;
    }

    public function updateCategory(int $id, CategoryDTO $dto): ?CategoryDTO
    {
        $data = $dto->toArray();
        $data['slug'] = Str::slug($data['name']);

        $category = $this->repository->update($id, $data);

        // Invalidar cache ao atualizar
        Cache::tags([self::CACHE_TAG, self::PRODUCT_CACHE_TAG])->flush();

        return $category ? CategoryDTO::fromArray($category->toArray()) : null;
    }

    public function deleteCategory(int $id): bool
    {
        $category = $this->repository->delete($id);

        // Invalidar cache ao deletar
        Cache::tags([self::CACHE_TAG, self::PRODUCT_CACHE_TAG])->flush();

        return $category !== null;
    }

    public function getChildCategories(int $parentId)
    {
        $cacheKey = "categories.parent.{$parentId}";

        return Cache::tags([self::CACHE_TAG])
            ->remember($cacheKey, self::CACHE_TTL, function () use ($parentId) {
                return $this->repository->getByParent($parentId);
            });
    }
}
