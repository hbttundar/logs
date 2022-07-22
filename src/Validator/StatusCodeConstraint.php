<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class StatusCodeConstraint extends Constraint
{
    public $message       = 'The statusCode "{{ string }}" is invalid.';
    public $blank_message = 'The statusCode in query string can not be blank or null';

    public function validatedBy(): string
    {
        return StatusCodeValidator::class;
    }
}
