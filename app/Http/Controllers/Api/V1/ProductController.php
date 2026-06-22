<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\ProductDTO;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Services\ProductService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class ProductController extends Controller
{
    use ApiResponses;

    protected ProductService $service;

    public function __construct()
    {
        $this->service = new ProductService();
    }

    public function index(): JsonResponse
    {
        $filters = request()->only('category_id', 'search', 'min_price', 'max_price', 'sort', 'sort_direction', 'per_page');
        $products = $this->service->getAllProducts($filters);
        
        return $this->paginatedResponse(
            ProductResource::collection($products),
            'Produtos listados com sucesso'
        );
    }

    public function show(int $id): JsonResponse
    {
        $product = $this->service->getProductById($id);

        if (!$product) {
            return $this->notFoundResponse('Produto n�o encontrado');
        }

        return $this->successResponse(new ProductResource($product), 'Produto obtido com sucesso');
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $dto = ProductDTO::fromArray($request->validated());
        $product = $this->service->createProduct($dto);

        if (!$product) {
            return $this->errorResponse('Erro ao criar produto', null, 400);
        }

        return $this->createdResponse(
            new ProductResource($this->service->getProductById($product->id)),
            'Produto criado com sucesso'
        );
    }

    public function update(UpdateProductRequest $request, int $id): JsonResponse
    {
        $product = $this->service->getProductById($id);

        if (!$product) {
            return $this->notFoundResponse('Produto n�o encontrado');
        }

        $dto = ProductDTO::fromArray($request->validated());
        $updated = $this->service->updateProduct($id, $dto);

        if (!$updated) {
            return $this->errorResponse('Erro ao atualizar produto', null, 400);
        }

        return $this->successResponse(
            new ProductResource($this->service->getProductById($id)),
            'Produto atualizado com sucesso'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $product = $this->service->getProductById($id);

        if (!$product) {
            return $this->notFoundResponse('Produto n�o encontrado');
        }

        $deleted = $this->service->deleteProduct($id);

        if (!$deleted) {
            return $this->errorResponse('Erro ao deletar produto', null, 400);
        }

        return $this->successResponse(null, 'Produto deletado com sucesso');
    }
}
