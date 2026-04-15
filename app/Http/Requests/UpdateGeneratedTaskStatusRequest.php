<?php

namespace App\Http\Requests;

use App\Enums\GeneratedTaskStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGeneratedTaskStatusRequest extends FormRequest
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
            'status' => ['required', Rule::enum(GeneratedTaskStatus::class)],
        ];
    }
}
