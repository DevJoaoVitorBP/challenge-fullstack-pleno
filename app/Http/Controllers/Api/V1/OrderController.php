<?php
namespace App\Http\Controllers\Api\V1;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Services\OrderService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
class OrderController extends Controller {
    use ApiResponses;
    protected OrderService $service;
    public function __construct() { $this->service = new OrderService(); }
    public function index(): JsonResponse {
        $userId = auth()->id();
        $orders = $this->service->getUserOrders($userId);
        return $this->paginatedResponse(OrderResource::collection($orders), 'Pedidos listados com sucesso');
    }
    public function show(int $id): JsonResponse {
        $order = $this->service->getOrderById($id);
        if (!$order) { return $this->notFoundResponse('Pedido n�o encontrado'); }
        if ($order->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            return $this->forbiddenResponse('Voc� n�o tem permiss�o para acessar este pedido');
        }
        return $this->successResponse(new OrderResource($order), 'Pedido obtido com sucesso');
    }
    public function store(CreateOrderRequest $request): JsonResponse {
        $userId = auth()->id();
        try {
            $order = $this->service->createOrderFromCart($userId, $request->shipping_address, $request->billing_address, $request->notes);
            if (!$order) { return $this->errorResponse('Erro ao criar pedido', null, 400); }
            return $this->createdResponse(new OrderResource($this->service->getOrderById($order->id)), 'Pedido criado com sucesso');
        } catch (\Exception $e) { return $this->errorResponse($e->getMessage(), null, 400); }
    }
    public function updateStatus(int $id): JsonResponse {
        $order = $this->service->getOrderById($id);
        if (!$order) { return $this->notFoundResponse('Pedido n�o encontrado'); }
        if (!auth()->user()->isAdmin()) { return $this->forbiddenResponse('Voc� n�o tem permiss�o para atualizar este pedido'); }
        $status = request()->get('status');
        $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        if (!in_array($status, $validStatuses)) { return $this->errorResponse('Status inv�lido', null, 400); }
        $updated = $this->service->updateOrderStatus($id, $status);
        if (!$updated) { return $this->errorResponse('Erro ao atualizar pedido', null, 400); }
        return $this->successResponse(new OrderResource($this->service->getOrderById($id)), 'Status do pedido atualizado com sucesso');
    }
}
