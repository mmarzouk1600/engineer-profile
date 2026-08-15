<?php

namespace App\Http\Requests;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:190'],
            'email' => ['required', 'email', 'max:190', Rule::unique(Admin::class)->ignore($this->user()->id)],
            'employee_no' => ['sometimes', 'nullable', 'string', 'max:190'],
            'phone' => ['required', 'string', 'max:190'],
            'office_phone' => ['sometimes', 'nullable', 'string', 'max:190'],
            'building_no' => ['sometimes', 'nullable', 'string', 'max:190'],
            'office_no' => ['sometimes', 'nullable', 'string', 'max:190'],
        ];
    }
}
