<?php

namespace Djurovicigoor\PostmarkBouncedEmailBlocker\Validation;

use Illuminate\Validation\Validator;
use Djurovicigoor\PostmarkBouncedEmailBlocker\Facades\PostmarkBouncedEmailBlockerFacade;

class BouncedEmailInPostmark
{
    
    /**
     * Default error message.
     *
     * @var string
     */
    public static string $errorMessage = 'It\'s not possible to send email to this address because the recipient has flagged your previous email as spam.';
    
    /**
     * Validates whether an email address does not list in Postmark bounced emails list.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  array  $parameters
     * @param  Validator  $validator
     *
     * @return bool
     */
    public function validate($attribute, $value, $parameters, $validator): bool
    {
        $hasValidateDomain = in_array('validate_domain', $parameters, true);
        if ($hasValidateDomain) {
            return PostmarkBouncedEmailBlockerFacade::enableDomainBlocking()->isNotBlocked($value);
        }
        return PostmarkBouncedEmailBlockerFacade::isNotBlocked($value);
    }
}
