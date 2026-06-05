<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CashFundEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'in:contribution,expense'],
            'housemate_id' => ['nullable', 'integer', 'exists:housemates,id'],
            'title' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'integer', 'min:1'],
            'entry_date' => ['required', 'date'],
            'receipt' => ['nullable', 'file', 'max:2048'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
