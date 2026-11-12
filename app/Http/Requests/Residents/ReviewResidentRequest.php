<?php

namespace App\Http\Requests\Residents;

use App\Services\Residents\ResidentAccess;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewResidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null
            && $this->user()->status === 'active'
            && $this->user()->login_enabled
            && ResidentAccess::hasRole($this->user(), 'superadmin');
    }

    public function rules(): array
    {
        return [
            'decision' => ['required', Rule::in(['approved', 'needs_info', 'rejected'])],
            'reason' => ['required_if:decision,needs_info,rejected', 'nullable', 'string', 'max:2000'],
        ];
    }
}
