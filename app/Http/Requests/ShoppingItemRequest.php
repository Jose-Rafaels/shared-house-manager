<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShoppingItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'priority' => ['required', 'in:High,Medium,Low'],
            'notes' => ['nullable', 'string'],
            'added_by_housemate_id' => ['nullable', 'integer', 'exists:housemates,id'],
        ];
    }
}
