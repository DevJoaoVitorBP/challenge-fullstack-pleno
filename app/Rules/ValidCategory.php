<?php

namespace App\Rules;

use App\Models\Category;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidCategory implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! Category::where('id', $value)->exists()) {
            $fail('A categoria selecionada não existe.');
        }
    }
}
