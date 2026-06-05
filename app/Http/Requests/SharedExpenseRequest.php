<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SharedExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'payer_housemate_id' => ['required', 'integer', 'exists:housemates,id'],
            'amount' => ['required', 'integer', 'min:1'],
            'expense_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'housemate_ids' => ['required', 'array', 'min:1'],
            'housemate_ids.*' => ['integer', 'exists:housemates,id'],
        ];
    }
}
