<?php

declare(strict_types=1);

namespace App\Validator;

#[\Attribute]
class StartDateConstraint  extends LogDateConstraint
{
    public $message = 'The StartDate in query string "{{ string }}" is invalid.';
    public $blank_message = 'The StartDate in query string can not be blank or null';

    public function validatedBy(): string
    {
        return StartDateValidator::class;
    }
}
