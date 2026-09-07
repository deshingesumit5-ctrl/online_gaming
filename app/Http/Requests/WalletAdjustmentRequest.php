<?php

namespace App\Http\Requests;

use App\Traits\GlobalValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class WalletAdjustmentRequest extends FormRequest
{
    use GlobalValidationRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'action_type' => ['required', 'in:credit,debit'],
            'amount' => self::pointAmountRules(1, 1000000),
            'remarks' => ['required', 'string', 'min:3', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'remarks.required' => 'A specific reason/remark is mandatory for wallet balance adjustments.',
        ];
    }
}
