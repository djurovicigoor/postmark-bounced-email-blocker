<?php

namespace Djurovicigoor\PostmarkBouncedEmailBlocker\Validation;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Djurovicigoor\PostmarkBouncedEmailBlocker\Facades\PostmarkBouncedEmailBlockerFacade;

class BouncedEmailInPostmark implements ValidationRule
{
    /**
     * Default error message.
     */
    public static string $errorMessage = 'It\'s not possible to send email to this address because the recipient has flagged your previous email as spam.';

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (PostmarkBouncedEmailBlockerFacade::isBlocked($value)) {
            $fail(static::$errorMessage);
        }
    }
}
