<?php

namespace App\DTOs;

class CategoryDTO
{
    public function __construct(
        public ?int $id = null,
        public string $name = '',
        public string $slug = '',
        public string $description = '',
        public ?int $parent_id = null,
        public bool $active = true,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'] ?? '',
            slug: $data['slug'] ?? '',
            description: $data['description'] ?? '',
            parent_id: $data['parent_id'] ?? null,
            active: (bool) ($data['active'] ?? true),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'parent_id' => $this->parent_id,
            'active' => $this->active,
        ];
    }
}
