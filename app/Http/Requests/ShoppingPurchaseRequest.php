<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShoppingPurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'purchased_by_housemate_id' => ['nullable', 'integer', 'exists:housemates,id'],
            'amount' => ['nullable', 'integer', 'min:1'],
            'purchased_on' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
