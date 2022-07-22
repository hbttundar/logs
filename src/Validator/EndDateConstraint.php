<?php

declare(strict_types=1);

namespace App\Validator;

#[\Attribute]
class EndDateConstraint extends LogDateConstraint
{
    public $message = 'The EndDate in query string "{{ string }}" is invalid.';
    public $blank_message = 'The EndDate in query string can not be blank or null';

    public function validatedBy(): string
    {
        return EndDateValidator::class;
    }
}
