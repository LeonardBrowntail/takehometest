<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class IndonesianPhoneNumber implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail("The {$attribute} must be a valid phone number string");

            return;
        }

        // Clean common delimiter characters
        $clean = preg_replace('/[\s\-\.\(\)]/', '', trim($value));

        // Matches: +628..., 628..., or 08... followed by valid carrier prefix and 7 to 10 digits
        $pattern = '/^(\+62|62|0)8[1-9][0-9]{7,10}$/';

        if (! preg_match($pattern, $clean)) {
            $fail("The {$attribute} is not a valid Indonesian mobile phone number.");
        }
    }
}
