<?php

namespace App\Http\Requests;

use App\Services\DebtLedgerService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SettlementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from_member_id' => [
                'required',
                'integer',
                Rule::exists('members', 'id')->whereNull('deleted_at'),
                'different:to_member_id',
            ],
            'to_member_id' => [
                'required',
                'integer',
                Rule::exists('members', 'id')->whereNull('deleted_at'),
            ],
            'amount' => ['required', 'integer', 'min:1'],
            'settlement_date' => ['required', 'date', 'before_or_equal:today'],
            'note' => ['nullable', 'string'],
        ];
    }

    /**
     * Reject settlements that exceed the current outstanding balance
     * (debtor → creditor). For updates, exclude the current settlement
     * so editing doesn't double-count it.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            if ($v->errors()->isNotEmpty()) {
                return;
            }

            $debtorId = (int) $this->input('from_member_id');
            $creditorId = (int) $this->input('to_member_id');
            $amount = (int) $this->input('amount');

            $ignoreId = $this->route('settlement')?->getKey();
            $balance = app(DebtLedgerService::class)
                ->balanceBetween($debtorId, $creditorId, $ignoreId);

            if ($amount > $balance) {
                $v->errors()->add(
                    'amount',
                    "Settlement amount ({$amount}) exceeds outstanding balance ({$balance})."
                );
            }
        });
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw (new ValidationException($validator))
            ->errorBag($this->errorBag)
            ->redirectTo($this->getRedirectUrl());
    }
}
