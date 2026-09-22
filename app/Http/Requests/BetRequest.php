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
            'selection' => ['required', 'in:andar,bahar,both'],
            'amount' => ['required_if:selection,andar,bahar', 'nullable', 'numeric', 'min:500', 'max:1000000'],
            'andar_amount' => ['required_if:selection,both', 'nullable', 'numeric', 'min:500', 'max:1000000'],
            'bahar_amount' => ['required_if:selection,both', 'nullable', 'numeric', 'min:500', 'max:1000000'],
        ];
    }

    public function messages(): array
    {
        return [
            'selection.required' => 'Please select Andar, Bahar, or Both.',
            'selection.in' => 'Selection must be Andar, Bahar, or Both.',
            'amount.min' => 'Minimum betting amount is 500 points.',
            'amount.max' => 'Bet exceeds the allowed amount.',
            'andar_amount.required_if' => 'Please specify an amount for Andar.',
            'andar_amount.min' => 'Minimum betting amount on Andar is 500 points.',
            'bahar_amount.required_if' => 'Please specify an amount for Bahar.',
            'bahar_amount.min' => 'Minimum betting amount on Bahar is 500 points.',
        ];
    }
}
