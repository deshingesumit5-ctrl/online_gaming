<?php

namespace App\Http\Requests;

use App\Traits\GlobalValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    use GlobalValidationRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'username' => array_merge(self::usernameRules(), ['unique:users,username']),
            'email' => ['nullable', 'email', 'max:150', 'unique:users,email'],
            'mobile' => array_merge(self::mobileRules(), ['unique:users,mobile']),
            'password' => self::passwordRules(true),
            'dob' => ['nullable', 'date', 'before_or_equal:today'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'kyc_info' => ['nullable', 'string', 'max:500'],
            'terms' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'terms.accepted' => 'You must agree to the Terms & Conditions and Privacy Policy.',
            'mobile.required' => 'Mobile number is required.',
            'mobile.digits' => 'Mobile number cannot be more or less than 10 digits.',
            'mobile.regex' => 'Please enter a valid 10-digit mobile number starting with 6, 7, 8, or 9.',
            'mobile.unique' => 'The mobile has already been taken.',
            'username.unique' => 'The username has already been taken.',
            'username.regex' => 'Username can only contain alphanumeric characters and underscores.',
            'email.unique' => 'The email has already been taken.',
            'email.email' => 'Please enter a valid email address.',
            'dob.before_or_equal' => 'Date of birth cannot be a future date.',
        ];
    }
}
