<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class GenerateTasksRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string|ValidationRule>>
     */
    public function rules(): array
    {
        return [
            'brief' => [
                'required',
                'string',
                'min:20',
                'max:100000',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'brief.required' => 'A client brief is required.',
            'brief.string' => 'The client brief must be text.',
            'brief.min' => 'The client brief must be at least :min characters so there is enough context to extract tasks.',
            'brief.max' => 'The client brief may not be greater than :max characters.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'brief' => 'client brief',
        ];
    }
}
