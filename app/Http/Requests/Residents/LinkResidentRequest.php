<?php

namespace App\Http\Requests\Residents;

use App\Services\Residents\ResidentAccess;
use Illuminate\Foundation\Http\FormRequest;

class LinkResidentRequest extends FormRequest
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
            'target_profile_id' => ['required', 'integer', 'exists:resident_profiles,id'],
            'reason' => ['required', 'string', 'min:8', 'max:2000'],
        ];
    }
}
