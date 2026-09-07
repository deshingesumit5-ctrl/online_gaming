<?php

namespace App\Http\Requests;

use App\Traits\GlobalValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class WithdrawalRequest extends FormRequest
{
    use GlobalValidationRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => self::pointAmountRules(100, 500000),
            'settlement_details' => ['required', 'string', 'min:10', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.min' => 'Minimum withdrawal amount is 100 points.',
            'amount.max' => 'Maximum withdrawal amount is 500,000 points.',
            'settlement_details.required' => 'Please provide your Bank Account (Account #, IFSC, Name) or UPI ID details.',
        ];
    }
}
