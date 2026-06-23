<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'O ID do produto é obrigatório',
            'product_id.exists' => 'Produto não encontrado',
            'quantity.required' => 'A quantidade é obrigatória',
            'quantity.min' => 'A quantidade deve ser pelo menos 1',
        ];
    }
}
