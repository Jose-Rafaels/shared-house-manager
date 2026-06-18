<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'description' => ['required', 'string', 'max:255'],
            'payer_id' => ['required', 'integer', Rule::exists('members', 'id')->whereNull('deleted_at')],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'amount' => ['required', 'integer', 'min:1'],
            'expense_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'member_ids' => ['required', 'array', 'min:1'],
            'member_ids.*' => ['integer', Rule::exists('members', 'id')->whereNull('deleted_at')],
        ];
    }
}
