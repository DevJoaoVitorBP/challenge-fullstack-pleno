<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shipping_address' => 'required|array',
            'shipping_address.street' => 'required|string|max:255',
            'shipping_address.city' => 'required|string|max:100',
            'shipping_address.state' => 'required|string|max:2',
            'shipping_address.zip' => 'required|string|max:20',
            'billing_address' => 'required|array',
            'billing_address.street' => 'required|string|max:255',
            'billing_address.city' => 'required|string|max:100',
            'billing_address.state' => 'required|string|max:2',
            'billing_address.zip' => 'required|string|max:20',
            'notes' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'shipping_address.required' => 'O endereço de entrega é obrigatório',
            'billing_address.required' => 'O endereço de faturamento é obrigatório',
            'shipping_address.street.required' => 'A rua é obrigatória',
            'shipping_address.city.required' => 'A cidade é obrigatória',
        ];
    }
}
