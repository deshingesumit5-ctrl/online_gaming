<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePointRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'points' => ['required', 'integer', 'min:10', 'max:10000000'],
            'remarks' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'points.required' => 'Please enter the number of points you wish to request.',
            'points.integer' => 'Point amount must be a whole number.',
            'points.min' => 'Minimum request amount is 10 points.',
        ];
    }
}
