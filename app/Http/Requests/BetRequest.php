<?php

namespace App\Http\Requests;

use App\Traits\GlobalValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class BetRequest extends FormRequest
{
    use GlobalValidationRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'selection' => ['required', 'in:andar,bahar'],
            'amount' => self::pointAmountRules(500, 1000000),
        ];
    }

    public function messages(): array
    {
        return [
            'selection.required' => 'Please select either Andar or Bahar.',
            'selection.in' => 'Selection must be Andar or Bahar.',
            'amount.min' => 'Minimum betting amount is 500 points.',
            'amount.max' => 'Bet exceeds the allowed amount.',
        ];
    }
}
