<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'housemate_ids' => ['required', 'array', 'min:1'],
            'housemate_ids.*' => ['integer', 'exists:housemates,id'],
        ];
    }
}
