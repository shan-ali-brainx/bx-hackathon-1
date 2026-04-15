<?php

namespace App\Http\Requests;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ClarifyBriefRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string|ValidationRule|Closure>>
     */
    public function rules(): array
    {
        return [
            'clarifications' => [
                'required',
                'array',
                function (string $attribute, mixed $value, Closure $fail): void {
                    $pending = session('client_brief_clarification');
                    if (! is_array($pending) || empty($pending['pending_ambiguities'])) {
                        $fail('Your clarification session expired. Submit your client brief again.');

                        return;
                    }
                    $expected = count($pending['pending_ambiguities']);
                    $got = is_array($value) ? count($value) : 0;
                    if ($got !== $expected) {
                        $fail("Provide exactly {$expected} answer(s) to match the open points above.");
                    }
                },
            ],
            'clarifications.*' => [
                'required',
                'string',
                'min:1',
                'max:20000',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'clarifications' => 'clarifications',
            'clarifications.*' => 'clarification',
        ];
    }
}
