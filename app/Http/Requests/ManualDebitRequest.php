<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ManualDebitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'amount' => ['required', 'integer', 'min:1', 'max:100000000'],
            'remarks' => ['required', 'string', 'max:255'],
        ];
    }
}
