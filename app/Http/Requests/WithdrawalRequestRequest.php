<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WithdrawalRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'integer', 'min:100', 'max:100000000'],
            'settlement_details' => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => 'Please enter the withdrawal amount in points.',
            'amount.integer' => 'Withdrawal points must be a whole number.',
            'amount.min' => 'Minimum withdrawal amount is 100 points.',
            'settlement_details.required' => 'Please provide settlement details (Bank account details or UPI ID).',
        ];
    }
}
