<?php

namespace App\Rules;

use App\Models\Product;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SufficientStock implements ValidationRule
{
    private string $productId;
    private int $quantity;

    public function __construct(string $productId, int $quantity)
    {
        $this->productId = $productId;
        $this->quantity = $quantity;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $product = Product::find($this->productId);
    
        if (!$product || $product->quantity < $this->quantity) {
            $available = $product?->quantity ?? 0;
    
            $fail("O produto não tem estoque suficiente. Disponível: {$available}");
        }
    }
}
