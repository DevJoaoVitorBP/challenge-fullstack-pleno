<?php

namespace App\Services;

use App\DTOs\ProductDTO;
use App\Events\ProductCreated;
use App\Repositories\ProductRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ProductService
{
    protected ProductRepository $repository;

    protected const CACHE_TTL = 3600; // 1 hora = 3600 segundos

    protected const CACHE_TAG = 'products';

    protected const CATEGORY_CACHE_TAG = 'category_products';

    public function __construct()
    {
        $this->repository = new ProductRepository;
    }

    public function getAllProducts(array $filters = [])
    {
        // Para buscar com filtros, não usa cache
        return $this->repository->getWithFilters($filters);
    }

    public function getProductById(int $id)
    {
        $cacheKey = "product.{$id}";

        return Cache::tags([self::CACHE_TAG])
            ->remember($cacheKey, self::CACHE_TTL, function () use ($id) {
                return $this->repository->find($id);
            });
    }

    public function createProduct(ProductDTO $dto): ?ProductDTO
    {
        $data = $dto->toArray();
        $data['slug'] = Str::slug($data['name']);

        $product = $this->repository->create($data);

        if ($product && $dto->tags) {
            $product->tags()->attach($dto->tags);
        }

        // Invalidar cache de produtos e categorias com tags inteligentes
        Cache::tags([self::CACHE_TAG, self::CATEGORY_CACHE_TAG])->flush();

        // Disparar evento de produto criado
        ProductCreated::dispatch($product);

        return $product ? ProductDTO::fromArray($product->toArray()) : null;
    }

    public function updateProduct(int $id, ProductDTO $dto): ?ProductDTO
    {
        $data = $dto->toArray();
        $data['slug'] = Str::slug($data['name']);

        $product = $this->repository->update($id, $data);

        if ($product && $dto->tags !== null) {
            $product->tags()->sync($dto->tags);
        }

        // Invalidar cache do produto e categorias relacionadas
        Cache::tags([self::CACHE_TAG, self::CATEGORY_CACHE_TAG])->flush();

        return $product ? ProductDTO::fromArray($product->toArray()) : null;
    }

    public function deleteProduct(int $id): bool
    {
        $product = $this->repository->delete($id);

        // Invalidar cache ao deletar
        Cache::tags([self::CACHE_TAG, self::CATEGORY_CACHE_TAG])->flush();

        return $product !== null;
    }

    public function checkStockAvailability(int $productId, int $quantity): bool
    {
        $product = $this->repository->find($productId);

        return $product && $product->quantity >= $quantity;
    }

    public function getLowStockProducts()
    {
        return $this->repository->getLowStock();
    }

    public function searchProducts(string $query)
    {
        return $this->repository->searchByName($query);
    }

    public function getProductsByCategory(int $categoryId)
    {
        $cacheKey = "products.category.{$categoryId}";

        return Cache::tags([self::CATEGORY_CACHE_TAG, "category.{$categoryId}"])
            ->remember($cacheKey, self::CACHE_TTL, function () use ($categoryId) {
                return $this->repository->getByCategory($categoryId);
            });
    }
}
