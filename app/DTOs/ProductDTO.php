<?php

namespace App\DTOs;

class ProductDTO
{
    public function __construct(
        public ?int $id = null,
        public string $name = '',
        public string $slug = '',
        public string $description = '',
        public float $price = 0,
        public ?float $cost_price = null,
        public int $quantity = 0,
        public int $min_quantity = 0,
        public bool $active = true,
        public int $category_id = 0,
        public ?array $tags = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'] ?? '',
            slug: $data['slug'] ?? '',
            description: $data['description'] ?? '',
            price: (float) ($data['price'] ?? 0),
            cost_price: isset($data['cost_price']) ? (float) $data['cost_price'] : null,
            quantity: (int) ($data['quantity'] ?? 0),
            min_quantity: (int) ($data['min_quantity'] ?? 0),
            active: (bool) ($data['active'] ?? true),
            category_id: (int) ($data['category_id'] ?? 0),
            tags: $data['tags'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => $this->price,
            'cost_price' => $this->cost_price,
            'quantity' => $this->quantity,
            'min_quantity' => $this->min_quantity,
            'active' => $this->active,
            'category_id' => $this->category_id,
            'tags' => $this->tags,
        ];
    }
}
