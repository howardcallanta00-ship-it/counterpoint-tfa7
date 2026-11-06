<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'max:50', Rule::unique('users', 'username')->ignore($this->route('user')?->id)],
            'full_name' => ['required', 'string', 'max:100'],
            'avatar' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'mimetypes:image/jpeg,image/png', 'max:2048', 'dimensions:max_width=4096,max_height=4096'],
        ];
    }

    public function messages(): array
    {
        return [
            'username.unique' => 'Username is already taken.',
            'avatar.mimes' => 'Choose a JPG or PNG image.',
            'avatar.mimetypes' => 'Choose a real JPG or PNG image.',
            'avatar.max' => 'Your photo must be 2 MB or smaller.',
            'avatar.dimensions' => 'Your photo must be no larger than 4096 × 4096 pixels.',
        ];
    }

    public function attributes(): array
    {
        return ['full_name' => 'full name', 'avatar' => 'profile photo'];
    }
}
