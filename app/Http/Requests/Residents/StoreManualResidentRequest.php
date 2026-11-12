<?php

namespace App\Http\Requests\Residents;

use App\Services\Residents\ResidentAccess;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreManualResidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null
            && $this->user()->status === 'active'
            && $this->user()->login_enabled
            && ResidentAccess::hasRole($this->user(), 'superadmin', 'operator-bank-sampah');
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'min:2', 'max:150'],
            'phone' => ['nullable', 'string', 'max:25'],
            'rt' => ['required', Rule::in(array_map(fn ($n) => str_pad((string) $n, 2, '0', STR_PAD_LEFT), range(1, 9)))],
            'house_number' => ['required', 'string', 'max:30'],
            'household_name' => ['nullable', 'string', 'max:150'],
            'display_name' => ['nullable', 'string', 'max:100'],
            'leaderboard_visible' => ['required', 'boolean'],
            'checked_duplicate' => ['accepted'],
        ];
    }
}
