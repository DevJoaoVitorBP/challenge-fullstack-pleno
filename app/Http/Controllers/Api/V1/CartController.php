<?php
namespace App\Http\Controllers\Api\V1;
use App\Http\Requests\AddToCartRequest;
use App\Http\Resources\CartResource;
use App\Http\Resources\CartItemResource;
use App\Services\CartService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
class CartController extends Controller {
    use ApiResponses;
    protected CartService $service;
    public function __construct() { $this->service = new CartService(); }
    public function index(): JsonResponse {
        $userId = auth()->id();
        $cart = $this->service->getCartItems($userId);
        return $this->successResponse(new CartResource($cart), 'Carrinho obtido com sucesso');
    }
    public function addItem(AddToCartRequest $request): JsonResponse {
        $userId = auth()->id();
        try {
            $cartItem = $this->service->addItemToCart($userId, $request->product_id, $request->quantity);
            return $this->createdResponse(new CartItemResource($cartItem), 'Item adicionado ao carrinho com sucesso');
        } catch (\Exception $e) { return $this->errorResponse($e->getMessage(), null, 400); }
    }
    public function updateItem(int $itemId): JsonResponse {
        $userId = auth()->id();
        $quantity = request()->get('quantity', 1);
        try {
            $cartItem = $this->service->updateCartItem($userId, $itemId, $quantity);
            return $this->successResponse(new CartItemResource($cartItem), 'Item do carrinho atualizado com sucesso');
        } catch (\Exception $e) { return $this->errorResponse($e->getMessage(), null, 400); }
    }
    public function removeItem(int $itemId): JsonResponse {
        $userId = auth()->id();
        try {
            $this->service->removeItemFromCart($userId, $itemId);
            return $this->successResponse(null, 'Item removido do carrinho com sucesso');
        } catch (\Exception $e) { return $this->errorResponse($e->getMessage(), null, 400); }
    }
    public function clear(): JsonResponse {
        $userId = auth()->id();
        $this->service->clearCart($userId);
        return $this->successResponse(null, 'Carrinho esvaziado com sucesso');
    }
}
