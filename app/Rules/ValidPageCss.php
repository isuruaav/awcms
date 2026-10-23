<?php

namespace App\Rules;

use App\Services\PageCss;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class ValidPageCss implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $error = app(PageCss::class)->error(is_string($value) ? $value : null);

        if ($error !== null) {
            $fail($error);
        }
    }
}
