<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer' => 'string|max:255',
            'warehouse_id' => 'exists:warehouses,id',
            'items' => 'array|min:1',
            'items.*.product_id' => 'exists:products,id',
            'items.*.count' => 'integer|min:1',
        ];
    }
}
