<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GameActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'in:start_round,open_betting,close_betting,declare_result'],
            'first_card' => ['nullable', 'string'],
            'winning_side' => ['nullable', 'in:andar,bahar'],
        ];
    }
}
