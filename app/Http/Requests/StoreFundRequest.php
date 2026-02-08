<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFundRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'start_year' => ['required', 'integer', 'min:1900', 'max:' . (date('Y') + 10)],
            'fund_manager_id' => ['required', 'exists:fund_managers,id'],
            'aliases' => ['sometimes', 'array'],
            'aliases.*' => [
                'required',
                'string',
                'max:255',
                'distinct:ignore_case',
                Rule::unique('fund_aliases', 'name'),
                Rule::unique('funds', 'name'),
            ],
            'company_ids' => ['sometimes', 'array'],
            'company_ids.*' => ['required', 'exists:companies,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'aliases.*.unique' => 'The alias ":input" is already in use.',
            'aliases.*.distinct' => 'Duplicate alias values are not allowed.',
        ];
    }
}
