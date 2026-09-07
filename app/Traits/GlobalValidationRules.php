<?php

namespace App\Traits;

trait GlobalValidationRules
{
    /**
     * Reusable rule for mobile phone numbers (strictly 10 digits).
     */
    public static function mobileRules(bool $required = true): array
    {
        $rules = ['digits:10', 'regex:/^[6-9][0-9]{9}$/'];
        if ($required) {
            array_unshift($rules, 'required');
        } else {
            array_unshift($rules, 'nullable');
        }
        return $rules;
    }

    /**
     * Reusable rule for usernames (alphanumeric, underscores, 3-25 chars).
     */
    public static function usernameRules(bool $required = true): array
    {
        $rules = ['min:3', 'max:25', 'regex:/^[a-zA-Z0-9_]+$/'];
        if ($required) {
            array_unshift($rules, 'required');
        } else {
            array_unshift($rules, 'nullable');
        }
        return $rules;
    }

    /**
     * Reusable rule for secure passwords.
     */
    public static function passwordRules(bool $confirmed = true): array
    {
        $rules = ['required', 'string', 'min:6'];
        if ($confirmed) {
            $rules[] = 'confirmed';
        }
        return $rules;
    }

    /**
     * Reusable rule for positive monetary / point values.
     */
    public static function pointAmountRules(float $min = 1.0, float $max = 1000000.0): array
    {
        return ['required', 'numeric', "min:$min", "max:$max"];
    }
}
