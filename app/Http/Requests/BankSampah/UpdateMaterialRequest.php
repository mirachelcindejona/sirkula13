<?php

namespace App\Http\Requests\BankSampah;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => strtolower(trim((string) $this->input('code'))),
        ]);
    }

    public function rules(): array
    {
        $material = $this->route('material');

        return [
            'code' => [
                'required',
                'string',
                'max:50',
                'regex:/\A[a-z0-9_-]+\z/',
                Rule::unique('materials', 'code')->ignore($material),
            ],

            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'short_name' => [
                'nullable',
                'string',
                'max:100',
            ],

            'acceptance_standard' => [
                'nullable',
                'string',
            ],

            'icon' => [
                'nullable',
                'string',
                'max:20',
            ],

            'is_active' => [
                'required',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Kode material wajib diisi.',
            'code.unique' => 'Kode material sudah digunakan.',
            'code.regex' => 'Format kode material tidak valid.',
            'name.required' => 'Nama material wajib diisi.',
            'is_active.required' => 'Status material wajib dipilih.',
        ];
    }
}
