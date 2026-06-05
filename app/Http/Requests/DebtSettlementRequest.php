<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DebtSettlementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shared_expense_id' => ['nullable', 'integer', 'exists:shared_expenses,id'],
            'debtor_housemate_id' => ['required', 'integer', 'exists:housemates,id'],
            'creditor_housemate_id' => ['required', 'integer', 'exists:housemates,id'],
            'amount' => ['required', 'integer', 'min:1'],
            'settled_on' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
