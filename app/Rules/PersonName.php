<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * A person's first or last name: letters (including ñ and accented letters), spaces,
 * hyphens, apostrophes and periods only. Allows "Ma. Cristiña", "dela Cruz" and
 * "O'Neil"; rejects numbers and other symbols. Used for staff and customer names.
 */
class PersonName implements ValidationRule
{
    public const PATTERN = "/^[\\pL\\s'.\\-]+$/u";

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! preg_match(self::PATTERN, $value)) {
            $fail('The :attribute may only contain letters, spaces, hyphens, apostrophes, and periods (no numbers).');
        }
    }
}
