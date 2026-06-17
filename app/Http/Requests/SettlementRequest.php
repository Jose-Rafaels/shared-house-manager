<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SettlementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from_member_id' => ['required', 'integer', 'exists:members,id'],
            'to_member_id' => ['required', 'integer', 'exists:members,id'],
            'amount' => ['required', 'integer', 'min:1'],
            'settlement_date' => ['required', 'date'],
            'note' => ['nullable', 'string'],
        ];
    }
}
