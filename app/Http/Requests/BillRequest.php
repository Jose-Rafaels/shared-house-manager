<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:50'],
            'amount' => ['required', 'integer', 'min:1'],
            'due_date' => ['nullable', 'date'],
            'billing_month' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'housemate_ids' => ['required', 'array', 'min:1'],
            'housemate_ids.*' => ['integer', 'exists:housemates,id'],
        ];
    }
}
