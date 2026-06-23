<?php

namespace App\DTOs;

class CartItemDTO
{
    public function __construct(
        public ?int $id = null,
        public int $cart_id = 0,
        public int $product_id = 0,
        public int $quantity = 1,
        public ?array $product = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            cart_id: (int) ($data['cart_id'] ?? 0),
            product_id: (int) ($data['product_id'] ?? 0),
            quantity: (int) ($data['quantity'] ?? 1),
            product: $data['product'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'cart_id' => $this->cart_id,
            'product_id' => $this->product_id,
            'quantity' => $this->quantity,
            'product' => $this->product,
        ];
    }
}
