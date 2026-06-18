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
            'rotation_start_date' => ['required', 'date'],
            'member_ids' => ['required', 'array', 'min:1'],
            'member_ids.*' => ['integer', Rule::exists('members', 'id')->whereNull('deleted_at')],
        ];
    }
}
