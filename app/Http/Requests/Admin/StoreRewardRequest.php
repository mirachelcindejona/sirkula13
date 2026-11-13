<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Support\Uploads;
use Illuminate\Validation\Rule;

class StoreRewardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('superadmin') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => strtolower(trim((string) $this->input('code'))),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50', 'regex:/\A[a-z0-9_-]+\z/', Rule::unique('rewards', 'code')],
            'name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
            'points_required' => ['required', 'integer', 'min:2', 'multiple_of:2'],
            'stock' => ['required', 'integer', 'min:0'],
            'photo' => Uploads::rules(),
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return Uploads::messages('photo');
    }
}
