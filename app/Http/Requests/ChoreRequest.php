<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'assigned_to_member_id' => ['nullable', 'integer', Rule::exists('members', 'id')->whereNull('deleted_at')],
            'assigned_for_date' => ['nullable', 'date'],
        ];
    }
}
