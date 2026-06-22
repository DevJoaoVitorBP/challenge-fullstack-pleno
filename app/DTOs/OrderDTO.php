<?php

namespace App\DTOs;

class OrderDTO
{
    public function __construct(
        public ?int $id = null,
        public int $user_id = 0,
        public string $status = 'pending',
        public float $total = 0,
        public float $subtotal = 0,
        public float $tax = 0,
        public float $shipping_cost = 0,
        public array $shipping_address = [],
        public array $billing_address = [],
        public ?string $notes = null,
        public ?array $items = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            user_id: (int) ($data['user_id'] ?? 0),
            status: $data['status'] ?? 'pending',
            total: (float) ($data['total'] ?? 0),
            subtotal: (float) ($data['subtotal'] ?? 0),
            tax: (float) ($data['tax'] ?? 0),
            shipping_cost: (float) ($data['shipping_cost'] ?? 0),
            shipping_address: $data['shipping_address'] ?? [],
            billing_address: $data['billing_address'] ?? [],
            notes: $data['notes'] ?? null,
            items: $data['items'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'status' => $this->status,
            'total' => $this->total,
            'subtotal' => $this->subtotal,
            'tax' => $this->tax,
            'shipping_cost' => $this->shipping_cost,
            'shipping_address' => $this->shipping_address,
            'billing_address' => $this->billing_address,
            'notes' => $this->notes,
            'items' => $this->items,
        ];
    }
}
