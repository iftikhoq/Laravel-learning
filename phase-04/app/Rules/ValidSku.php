<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class ValidSku implements ValidationRule
{
    public function __construct(
        protected ?int $ignoreId = null
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail("The {$attribute} must be a valid string.");
            return;
        }

        // Must match uppercase alphanumeric hyphenated segments (e.g. PROD-CAT-01, AUD-WNC-01)
        if (! preg_match('/^[A-Z0-9]{2,8}(-[A-Z0-9]{2,8})+$/', $value)) {
            $fail("The {$attribute} must follow the standardized inventory format (e.g., PROD-ITEM-01) using uppercase alphanumeric segments separated by hyphens.");
            return;
        }

        // Uniqueness verification (respecting ignoreId if updating)
        $query = DB::table('products')->where('sku', $value);
        if ($this->ignoreId !== null) {
            $query->where('id', '!=', $this->ignoreId);
        }

        if ($query->exists()) {
            $fail("The {$attribute} '{$value}' is already assigned to another inventory product.");
        }
    }
}
