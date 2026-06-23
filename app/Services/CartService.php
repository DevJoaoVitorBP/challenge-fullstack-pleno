<?php

namespace App\Services;

use App\Models\CartItem;
use App\Repositories\CartRepository;
use App\Repositories\ProductRepository;

class CartService
{
    protected CartRepository $cartRepository;

    protected ProductRepository $productRepository;

    public function __construct()
    {
        $this->cartRepository = new CartRepository;
        $this->productRepository = new ProductRepository;
    }

    public function getOrCreateCart(int $userId)
    {
        return $this->cartRepository->getOrCreateByUser($userId);
    }

    public function getCartItems(int $userId)
    {
        $cart = $this->getOrCreateCart($userId);

        return $this->cartRepository->findWithItems($cart->id);
    }

    public function addItemToCart(int $userId, int $productId, int $quantity): ?CartItem
    {
        $cart = $this->getOrCreateCart($userId);
        $product = $this->productRepository->find($productId);

        if (! $product || $product->quantity < $quantity) {
            throw new \Exception('Produto não disponível ou quantidade insuficiente em estoque');
        }

        $cartItem = $cart->items()->firstOrCreate(
            ['product_id' => $productId],
            ['quantity' => 0]
        );

        $cartItem->quantity += $quantity;
        $cartItem->save();

        return $cartItem->load('product');
    }

    public function updateCartItem(int $userId, int $itemId, int $quantity): ?CartItem
    {
        $cart = $this->getOrCreateCart($userId);
        $cartItem = $cart->items()->find($itemId);

        if (! $cartItem) {
            throw new \Exception('Item não encontrado no carrinho');
        }

        $product = $this->productRepository->find($cartItem->product_id);
        if (! $product || $product->quantity < $quantity) {
            throw new \Exception('Quantidade insuficiente em estoque');
        }

        $cartItem->update(['quantity' => $quantity]);

        return $cartItem->load('product');
    }

    public function removeItemFromCart(int $userId, int $itemId): bool
    {
        $cart = $this->getOrCreateCart($userId);
        $cartItem = $cart->items()->find($itemId);

        if (! $cartItem) {
            throw new \Exception('Item não encontrado no carrinho');
        }

        return $cartItem->delete();
    }

    public function clearCart(int $userId): bool
    {
        $cart = $this->getOrCreateCart($userId);

        return $this->cartRepository->clearCart($cart->id) !== null;
    }

    public function getCartTotal(int $userId): float
    {
        $cart = $this->getCartItems($userId);
        $total = 0;

        foreach ($cart->items as $item) {
            $total += $item->product->price * $item->quantity;
        }

        return $total;
    }
}
