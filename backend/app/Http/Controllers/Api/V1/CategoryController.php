<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\CategoryDTO;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductResource;
use App\Services\CategoryService;
use App\Services\ProductService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class CategoryController extends Controller
{
    use ApiResponses;

    protected CategoryService $service;

    protected ProductService $productService;

    public function __construct()
    {
        $this->service = new CategoryService;
        $this->productService = new ProductService;
    }

    public function index(): JsonResponse
    {
        $categories = $this->service->getCategoryHierarchy();

        return $this->successResponse(
            CategoryResource::collection($categories),
            'Categorias listadas com sucesso'
        );
    }

    public function show(int $id): JsonResponse
    {
        $category = $this->service->getCategoryById($id);

        if (! $category) {
            return $this->notFoundResponse('Categoria n�o encontrada');
        }

        return $this->successResponse(
            new CategoryResource($category),
            'Categoria obtida com sucesso'
        );
    }

    public function products(int $id): JsonResponse
    {
        $category = $this->service->getCategoryById($id);

        if (! $category) {
            return $this->notFoundResponse('Categoria n�o encontrada');
        }

        $filters = request()->only('search', 'min_price', 'max_price', 'sort', 'sort_direction', 'per_page', 'page');
        $products = $this->productService->getProductsByCategory($id, $filters);

        return $this->paginatedResponse(
            ProductResource::collection($products),
            'Produtos da categoria listados com sucesso'
        );
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $dto = CategoryDTO::fromArray($request->validated());
        $category = $this->service->createCategory($dto);

        if (! $category) {
            return $this->errorResponse('Erro ao criar categoria', null, 400);
        }

        return $this->createdResponse(
            new CategoryResource($this->service->getCategoryById($category->id)),
            'Categoria criada com sucesso'
        );
    }

    public function update(StoreCategoryRequest $request, int $id): JsonResponse
    {
        $category = $this->service->getCategoryById($id);

        if (! $category) {
            return $this->notFoundResponse('Categoria n�o encontrada');
        }

        $dto = CategoryDTO::fromArray($request->validated());
        $updated = $this->service->updateCategory($id, $dto);

        if (! $updated) {
            return $this->errorResponse('Erro ao atualizar categoria', null, 400);
        }

        return $this->successResponse(
            new CategoryResource($this->service->getCategoryById($id)),
            'Categoria atualizada com sucesso'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $category = $this->service->getCategoryById($id);

        if (! $category) {
            return $this->notFoundResponse('Categoria n�o encontrada');
        }

        $deleted = $this->service->deleteCategory($id);

        if (! $deleted) {
            return $this->errorResponse('Erro ao deletar categoria', null, 400);
        }

        return $this->successResponse(null, 'Categoria deletada com sucesso');
    }
}
